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
        const [guruRows] = await dbPool.query(`
            SELECT name, nip, class FROM users WHERE role = 'guru' ORDER BY class, name
        `);
        const [siswaRows] = await dbPool.query(`
            SELECT name, class, nisn FROM users WHERE role = 'siswa' ORDER BY class, name LIMIT 20
        `);
        return {
            status: 'success',
            ringkasan_anggota: counts[0],
            daftar_guru: guruRows,
            contoh_daftar_siswa: siswaRows
        };
    } catch (err) {
        return { error: err.message };
    }
}

async function getDaftarGuru() {
    try {
        const [rows] = await dbPool.query(`
            SELECT name, nip, class, phone FROM users WHERE role = 'guru' ORDER BY class, name
        `);
        return {
            total_guru: rows.length,
            daftar_guru: rows
        };
    } catch (err) {
        return { error: err.message };
    }
}

async function getBukuByPenulis({ nama_penulis }) {
    try {
        const searchPenulis = String(nama_penulis || '').trim();
        const [rows] = await dbPool.query(`
            SELECT title as judul, author as penulis, stock_available as stok, publisher as penerbit, year as tahun
            FROM books 
            WHERE author LIKE ?
        `, [`%${searchPenulis}%`]);
        
        return {
            status: 'success',
            pencarian: searchPenulis,
            total_ditemukan: rows.length,
            daftar_buku: rows
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
                name: "getBukuByPenulis",
                description: "Mencari koleksi buku di perpustakaan berdasarkan nama penulis atau pengarang (misal: 'Andrea Hirata', 'Erlangga').",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        nama_penulis: {
                            type: "STRING",
                            description: "Nama penulis atau pengarang buku yang dicari"
                        }
                    },
                    required: ["nama_penulis"]
                }
            },
            {
                name: "getDaftarGuru",
                description: "Mendapatkan daftar lengkap nama seluruh guru dan staf pengajar di SDN Pengampon 3 dari database MySQL.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
            {
                name: "getTotalAnggota",
                description: "Mendapatkan total jumlah anggota perpustakaan dan siapa saja anggotanya secara real-time dari database MySQL.",
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
Kamu adalah PustakaAI, asisten virtual ramah untuk Perpustakaan SDN Pengampon 3. Tugasmu adalah membantu siswa, guru, dan petugas perpustakaan.

INFORMASI PENGGUNA AKTIF:
- Nama Pengguna: ${userName || 'Faidil Aqiel'}
- Peran: ${userRole || 'Petugas Perpustakaan'}

PENGETAHUAN OPERASIONAL & ATURAN INTERAKSI:
1. FITUR JAM OPERASIONAL:
   - Perpustakaan buka pada hari Senin-Jumat, pukul 07:00 - 14:00 WIB. Sabtu, Minggu, dan Hari Libur Nasional tutup.
   - Jika user bertanya atau mengingatkan perihal jam operasional di luar jam tersebut, ingatkan dengan sopan: "Perpustakaan sedang tutup, silakan datang kembali besok pagi ya."
   - Durasi pinjam maksimal 7 hari kalender. Denda keterlambatan Rp 1.000,-/hari pada hari buka sekolah.

2. FITUR SARAN & KRITIK (INTERAKSI DUA ARAH):
   - Jika user mengucapkan kata kunci seperti "Terima kasih", "Makasih min", "Mantap", atau apresiasi lainnya, responslah dengan antusias:
     "Sama-sama! Semoga bukunya bermanfaat. Jangan lupa berikan ulasan atau saran untuk perpustakaan kami ya!"

3. ATURAN PENOLAKAN BUKU FIKTIF (ANTI-HALUSINASI TINGKAT LANJUT):
   - AI DILARANG mengarang judul buku atau nama penulis.
   - Jika user mencari buku yang tidak ada di database (contoh: "Buku Harry Potter ada?"), AI WAJIB menjawab:
     "Maaf, sepertinya buku tersebut belum tersedia di koleksi Perpustakaan SDN Pengampon 3. Silakan cek katalog buku lainnya."

4. MODE SAPAAN AWAL:
   - Jika user menyapa singkat seperti "halo", "hai", atau membuka chat pertama kali, sambutlah:
     "Halo! Saya PustakaAI. Mau cari buku cerita, buku pelajaran, atau cek status peminjaman hari ini?"

5. KAPAN MEMANGGIL FUNGSI DATABASE (TOOLS):
   - Panggil fungsi database HANYA JIKA user menanyakan data spesifik dari sistem:
     * Penulis buku (misal "Buku apa saja karya Andrea Hirata?") -> Panggil getBukuByPenulis
     * Daftar anggota atau siapa anggotanya -> Panggil getTotalAnggota
     * Daftar guru -> Panggil getDaftarGuru
     * Total buku & transaksi -> Panggil getTotalBuku
     * Siswa belum absen hari ini -> Panggil getSiswaBelumAbsen
     * Wali kelas -> Panggil getWaliKelas

6. KAPAN MENJAWAB TEKS BIASA:
   - Jika user bertanya tentang prosedur atau tata cara (misal: "Bagaimana cara meminjam buku?"), jawab teks biasa:
     "Prosedur peminjaman: Anggota harus membawa kartu anggota, mencari buku di rak atau katalog aplikasi, lalu mencatat peminjaman ke petugas. Maksimal peminjaman adalah 3 buku selama 7 hari."
   - Syarat anggota: Siswa atau guru SDN Pengampon 3 yang sudah mendaftar dan aktif di sekolah.

7. ATURAN LAIN:
   - DILARANG KERAS memberikan sapaan default (seperti 'Ada yang bisa saya bantu?') jika user sudah mengajukan pertanyaan spesifik.
   - Jawablah dengan ringkas, jelas, dan langsung pada intinya.
        `;

        console.log("Menginisialisasi model gemini-3.1-flash-lite...");
        const model = genAI.getGenerativeModel({
            model: "gemini-3.1-flash-lite",
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

            if (call.name === "getBukuByPenulis") {
                functionResult = await getBukuByPenulis(call.args || {});
            } else if (call.name === "getDaftarGuru") {
                functionResult = await getDaftarGuru();
            } else if (call.name === "getTotalAnggota") {
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
