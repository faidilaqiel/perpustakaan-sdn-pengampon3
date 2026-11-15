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

async function getTotalAnggota() {
    try {
        const [counts] = await dbPool.query(`
            SELECT 
                COUNT(*) as total_anggota,
                SUM(CASE WHEN role = 'siswa' THEN 1 ELSE 0 END) as total_siswa,
                SUM(CASE WHEN role = 'guru' THEN 1 ELSE 0 END) as total_guru,
                SUM(CASE WHEN role = 'petugas' THEN 1 ELSE 0 END) as total_petugas
            FROM users 
            WHERE status = 'aktif' OR status IS NULL
        `);
        return {
            status: 'success',
            data_anggota: counts[0]
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
                name: "getTotalAnggota",
                description: "Mendapatkan total jumlah anggota perpustakaan secara real-time, termasuk rincian jumlah siswa, guru, dan petugas.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
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
    console.log("--> Menerima request di POST /api/chat:", req.body);
    try {
        const { pesan, userName, userRole } = req.body;
        if (!pesan) {
            console.warn("Pesan kosong diterima di /api/chat");
            return res.status(400).json({ error: "Pesan wajib diisi." });
        }

        const systemInstruction = `
            Anda adalah PustakaAI, asisten kecerdasan buatan resmi khusus Perpustakaan Digital SDN Pengampon 3 Cirebon.

            INFORMASI PENGGUNA LOGIN SAAT INI:
            - Nama Pengguna: ${userName || 'Faidil Aqiel'}
            - Peran / Jabatan: ${userRole || 'Petugas Perpustakaan'}

            TUGAS DAN ATURAN UTAMA:
            1. BASIS PENGETAHUAN APLIKASI PERPUSTAKAAN DIGITAL SDN PENGAMPON 3:
               - Jam Operasional: Buka Senin - Jumat, pukul 07.30 - 14.00 WIB. Sabtu, Minggu, dan Hari Libur Nasional TUTUP.
               - Syarat Peminjaman: Terdaftar sebagai anggota aktif (siswa, guru, atau staf sekolah).
               - Batas Maksimal Pinjam: Maksimal 2 (dua) buku sekaligus untuk siswa.
               - Durasi Peminjaman: Maksimal 7 (tujuh) hari kalender per buku.
               - Ketentuan Denda: Denda keterlambatan Rp 1.000,- per hari per buku pada hari buka sekolah. Jika jatuh tempo di hari libur/Minggu, TIDAK DIKENAKAN DENDA (denda tidak berjalan).
               - Sanksi Rusak/Hilang: Wajib segera lapor ke petugas perpustakaan dan mengganti buku dengan judul yang sama atau membayar biaya penggantian buku.
               - Alur Pinjam Buku di Aplikasi Web:
                 1) Buka menu 'Katalog' / 'Daftar Buku'.
                 2) Pilih buku yang berstatus 'Tersedia'.
                 3) Klik tombol 'Pinjam Buku Ini' untuk mengajukan pinjaman.
                 4) Status pinjaman akan menjadi 'Menunggu'.
                 5) Datang ke ruang perpustakaan untuk mengambil buku fisik dan verifikasi/scan QR oleh Petugas.
                 6) Setelah disetujui, buku aktif dipinjam dan batas pengembalian dapat dipantau di menu 'Riwayat'.
               - Kontak & Profil:
                 * Sekolah: SDN Pengampon 3 Kota Cirebon, Jawa Barat.
                 * Kepala Sekolah: H. Noni Risanto, M.Pd.I.
                 * Telepon: 0231-232048 | Email: sdpengampon3cirebon@gmail.com.

            2. MENOLAK PERTANYAAN DI LUAR KONTEKS:
               Jika pengguna bertanya tentang hal di luar perpustakaan atau sekolah (misalnya resep, politik, game lain, cuaca umum, dsb.), WAJIB MENOLAK secara sopan:
               "Mohon maaf, saya adalah PustakaAI, asisten digital khusus Perpustakaan SDN Pengampon 3. Saya hanya dapat membantu pertanyaan seputar perpustakaan, koleksi buku, peminjaman, dan informasi sekolah. Ada yang bisa saya bantu mengenai buku atau perpustakaan?"

            3. MENJAWAB SESUAI IDENTITAS & DATABASE REAL-TIME:
               - Jika ditanya "siapa anda" / "siapa kamu": Jelaskan bahwa Anda adalah PustakaAI, asisten digital cerdas resmi Perpustakaan SDN Pengampon 3.
               - Jika ditanya "siapa saya": Jelaskan bahwa pengguna adalah ${userName || 'Faidil Aqiel'} dengan peran ${userRole || 'Petugas Perpustakaan'}.
               - Selalu panggil fungsi/tools yang sesuai jika ditanya data real-time:
                 * Jumlah anggota / total anggota perpustakaan -> getTotalAnggota
                 * Siswa belum absen / kunjungan hari ini -> getSiswaBelumAbsen
                 * Total transaksi / total buku / stok -> getTotalBuku
                 * Wali kelas / guru -> getWaliKelas

            4. GAYA BAHASA:
               Gunakan bahasa Indonesia yang santun, ramah, edukatif, rapi (gunakan bullet points jika menjelaskan alur), dan to the point.
        `;

        console.log("Menginisialisasi model gemini-3-flash-preview...");
        const model = genAI.getGenerativeModel({
            model: "gemini-3-flash-preview",
            systemInstruction: systemInstruction,
            tools: tools
        });

        const chat = model.startChat();
        console.log("Mengirim pesan user ke Gemini:", pesan);
        let result = await chat.sendMessage(pesan);
        let response = await result.response;

        // Tangani Function Calls jika model meminta data database
        const functionCalls = response.functionCalls();
        if (functionCalls && functionCalls.length > 0) {
            const call = functionCalls[0];
            console.log("Gemini memicu Function Call:", call.name, call.args);
            let functionResult = null;

            if (call.name === "getTotalAnggota") {
                functionResult = await getTotalAnggota();
            } else if (call.name === "getSiswaBelumAbsen") {
                functionResult = await getSiswaBelumAbsen();
            } else if (call.name === "getTotalBuku") {
                functionResult = await getTotalBuku();
            } else if (call.name === "getWaliKelas") {
                functionResult = await getWaliKelas(call.args || {});
            }
            console.log("Hasil query database untuk", call.name, ":", JSON.stringify(functionResult).slice(0, 100) + "...");

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
        console.log("<-- Jawaban berhasil diperoleh dari Gemini:", jawaban.slice(0, 80) + "...");
        return res.json({ jawaban });

    } catch (error) {
        console.error("❌ ERROR ASLI pada pemanggilan Gemini di POST /api/chat:", error);
        return res.status(500).json({ 
            jawaban: "Mohon maaf, terjadi kendala saat memproses jawaban AI (" + (error.message || "Unknown error") + ").",
            error: error.message
        });
    }
});

const PORT = process.env.CHAT_PORT || 3000;
app.listen(PORT, () => {
    console.log(`🤖 PustakaAI Gemini Server berjalan di http://localhost:${PORT}`);
});
