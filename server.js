import express from 'express';
import cors from 'cors';
import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import { GoogleGenerativeAI } from '@google/generative-ai';

dotenv.config();

const app = express();
app.use(cors());
app.use(express.json());

// 1. Inisialisasi Google Gemini API
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
if (!GEMINI_API_KEY) {
    console.error("PERINGATAN: GEMINI_API_KEY tidak ditemukan di environment (.env).");
}
const genAI = new GoogleGenerativeAI(GEMINI_API_KEY || "");

// 2. Koneksi Pool Database MySQL
const dbPool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT) || 3306,
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'perpustakaan_sdn_pengampon3',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// 3. Fungsi-Fungsi Query Database (Tools)
async function getSiswaBelumAbsen() {
    try {
        const today = new Date().toISOString().slice(0, 10);
        const [rows] = await dbPool.query(`
            SELECT u.id, u.name, u.class, u.nisn 
            FROM users u 
            WHERE u.role = 'siswa' 
              AND u.status = 'aktif'
              AND u.id NOT IN (
                  SELECT user_id FROM kunjungans WHERE tanggal = ?
              )
            ORDER BY u.class, u.name
        `, [today]);
        
        return {
            tanggal: today,
            jumlah_siswa_belum_absen: rows.length,
            daftar_siswa: rows
        };
    } catch (err) {
        return { error: err.message };
    }
}

async function getTotalBuku() {
    try {
        const [books] = await dbPool.query(`
            SELECT 
                COUNT(*) as total_judul,
                IFNULL(SUM(stock_total), 0) as total_eksemplar,
                IFNULL(SUM(stock_available), 0) as total_tersedia
            FROM books
        `);
        const [loans] = await dbPool.query(`
            SELECT 
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu_persetujuan,
                SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_kembali
            FROM loans
        `);
        return {
            statistik_buku: books[0],
            statistik_transaksi_sirkulasi: loans[0]
        };
    } catch (err) {
        return { error: err.message };
    }
}

async function getWaliKelas({ kelas }) {
    try {
        const searchKelas = String(kelas || '').trim();
        const [rows] = await dbPool.query(`
            SELECT name, nip, phone, role, class 
            FROM users 
            WHERE role = 'guru' AND class LIKE ?
            LIMIT 1
        `, [`%${searchKelas}%`]);
        
        if (rows.length === 0) {
            const [allGuru] = await dbPool.query(`SELECT name, class, nip FROM users WHERE role = 'guru'`);
            return {
                status: 'tidak_ditemukan',
                pesan: `Wali kelas ${searchKelas} tidak ditemukan secara spesifik.`,
                daftar_seluruh_guru: allGuru
            };
        }
        return {
            status: 'ditemukan',
            wali_kelas: rows[0]
        };
    } catch (err) {
        return { error: err.message };
    }
}

// 4. Definisi Function Declarations (Tools) untuk Gemini
const tools = [
    {
        functionDeclarations: [
            {
                name: "getSiswaBelumAbsen",
                description: "Mendapatkan daftar siswa yang belum hadir / belum absen / belum berkunjung ke perpustakaan hari ini.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
            {
                name: "getTotalBuku",
                description: "Mendapatkan statistik lengkap buku (total judul, eksemplar, stok tersedia) dan total transaksi sirkulasi (menunggu, dipinjam, dikembalikan) di perpustakaan secara real-time.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
            {
                name: "getWaliKelas",
                description: "Mencari data guru atau wali kelas berdasarkan nama kelas (misal: '2A', 'Kelas 1', '6').",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        kelas: {
                            type: "STRING",
                            description: "Identitas atau tingkatan kelas, misalnya '2A', '1', '6'"
                        }
                    },
                    required: ["kelas"]
                }
            }
        ]
    }
];

// 5. Endpoint POST /api/chat
app.post('/api/chat', async (req, res) => {
    try {
        const { pesan, userName, userRole } = req.body;
        if (!pesan) {
            return res.status(400).json({ error: "Pesan wajib diisi." });
        }

        const systemInstruction = `
            Anda adalah PustakaAI, asisten digital cerdas dan ramah di Perpustakaan Digital SDN Pengampon 3 Cirebon.
            Pengguna yang sedang aktif login di website saat ini:
            - Nama: ${userName || 'Faidil Aqiel'}
            - Peran: ${userRole || 'Petugas Perpustakaan'}
            Jika ditanya 'siapa saya', sebutkan nama dan peran pengguna di atas dengan sopan dan ramah.
            Jika ditanya mengenai:
            1. Siswa yang belum absen -> Panggil fungsi getSiswaBelumAbsen
            2. Jumlah buku / stok / total transaksi peminjaman -> Panggil fungsi getTotalBuku
            3. Wali kelas atau guru kelas -> Panggil fungsi getWaliKelas
            Gunakan selalu tools yang tersedia agar data yang Anda sampaikan akurat 100% dan real-time dari database MySQL sekolah.
            Gunakan bahasa Indonesia yang santun, ramah anak, dan informatif.
        `;

        const model = genAI.getGenerativeModel({
            model: "gemini-3-flash-preview",
            systemInstruction: systemInstruction,
            tools: tools
        });

        const chat = model.startChat();
        let result = await chat.sendMessage(pesan);
        let response = await result.response;

        // Tangani Function Calls jika model meminta data database
        const functionCalls = response.functionCalls();
        if (functionCalls && functionCalls.length > 0) {
            const call = functionCalls[0];
            let functionResult = null;

            if (call.name === "getSiswaBelumAbsen") {
                functionResult = await getSiswaBelumAbsen();
            } else if (call.name === "getTotalBuku") {
                functionResult = await getTotalBuku();
            } else if (call.name === "getWaliKelas") {
                functionResult = await getWaliKelas(call.args || {});
            }

            // Kembalikan hasil query database ke Gemini untuk dirangkai menjadi jawaban
            result = await chat.sendMessage([
                {
                    functionResponse: {
                        name: call.name,
                        response: { output: functionResult }
                    }
                }
            ]);
            response = await result.response;
        }

        const jawaban = response.text();
        return res.json({ jawaban });

    } catch (error) {
        console.error("Error pada POST /api/chat:", error);
        return res.status(500).json({ 
            jawaban: "Mohon maaf, terjadi kendala saat memproses jawaban AI. Pastikan database dan koneksi aktif." 
        });
    }
});

const PORT = process.env.CHAT_PORT || 3000;
app.listen(PORT, () => {
    console.log(`🤖 PustakaAI Gemini Server berjalan di http://localhost:${PORT}`);
});
