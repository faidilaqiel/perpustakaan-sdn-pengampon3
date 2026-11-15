import express from 'express';
import cors from 'cors';
import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import { GoogleGenerativeAI } from '@google/generative-ai';

dotenv.config();

const app = express();
app.use(cors());
app.use(express.json({ limit: '20mb' }));

// 1. Inisialisasi Google Gemini API
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
if (!GEMINI_API_KEY) {
    console.error("❌ PERINGATAN: GEMINI_API_KEY tidak ditemukan di environment (.env).");
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

// ==========================================
// BAGIAN 1: IMPLEMENTASI FUNGSI DATABASE (TOOLS)
// ==========================================

// 1. cariSiswa: Mencari siswa berdasarkan nama spesifik
async function cariSiswa({ nama }) {
    console.log(`[DB] Menjalankan cariSiswa dengan parameter nama: "${nama}"`);
    try {
        const keyword = String(nama || '').trim();
        const [rows] = await dbPool.query(`
            SELECT id, name as nama, class as kelas, nisn, gender, status 
            FROM users 
            WHERE role = 'siswa' AND name LIKE ?
        `, [`%${keyword}%`]);
        console.log(`[DB] cariSiswa menemukan ${rows.length} data.`);
        return {
            status: rows.length > 0 ? 'success' : 'not_found',
            kata_kunci: keyword,
            total_ditemukan: rows.length,
            hasil: rows
        };
    } catch (err) {
        console.error(`[DB ERROR] cariSiswa gagal:`, err.message);
        return { error: err.message };
    }
}

// 2. cariGuru: Mencari guru berdasarkan nama spesifik
async function cariGuru({ nama }) {
    console.log(`[DB] Menjalankan cariGuru dengan parameter nama: "${nama}"`);
    try {
        const keyword = String(nama || '').trim();
        const [rows] = await dbPool.query(`
            SELECT id, name as nama, class as kelas_wali, nip, phone as telepon 
            FROM users 
            WHERE role = 'guru' AND name LIKE ?
        `, [`%${keyword}%`]);
        console.log(`[DB] cariGuru menemukan ${rows.length} data.`);
        return {
            status: rows.length > 0 ? 'success' : 'not_found',
            kata_kunci: keyword,
            total_ditemukan: rows.length,
            hasil: rows
        };
    } catch (err) {
        console.error(`[DB ERROR] cariGuru gagal:`, err.message);
        return { error: err.message };
    }
}

// 3. cariBuku: Mencari buku berdasarkan judul spesifik
async function cariBuku({ judul }) {
    console.log(`[DB] Menjalankan cariBuku dengan parameter judul: "${judul}"`);
    try {
        const keyword = String(judul || '').trim();
        const [rows] = await dbPool.query(`
            SELECT b.id, b.title as judul, b.stock_available as stok, b.stock_total as total_stok, 
                   b.author as penulis, b.publisher as penerbit, b.year as tahun, c.name as kategori
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.title LIKE ?
        `, [`%${keyword}%`]);
        console.log(`[DB] cariBuku menemukan ${rows.length} data.`);
        return {
            status: rows.length > 0 ? 'success' : 'not_found',
            kata_kunci: keyword,
            total_ditemukan: rows.length,
            hasil: rows
        };
    } catch (err) {
        console.error(`[DB ERROR] cariBuku gagal:`, err.message);
        return { error: err.message };
    }
}

// 4. getTotalAnggota: Menghitung total seluruh anggota
async function getTotalAnggota() {
    console.log(`[DB] Menjalankan getTotalAnggota`);
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
        console.log(`[DB] getTotalAnggota berhasil:`, counts[0]);
        return {
            status: 'success',
            statistik: counts[0]
        };
    } catch (err) {
        console.error(`[DB ERROR] getTotalAnggota gagal:`, err.message);
        return { error: err.message };
    }
}

// 5. getStatusPeminjaman: Mengecek status peminjaman buku oleh siswa
async function getStatusPeminjaman({ nama_siswa }) {
    console.log(`[DB] Menjalankan getStatusPeminjaman untuk siswa: "${nama_siswa}"`);
    try {
        const keyword = String(nama_siswa || '').trim();
        const [rows] = await dbPool.query(`
            SELECT l.id as id_pinjam, u.name as nama_siswa, u.class as kelas, 
                   b.title as judul_buku, b.code as kode_buku,
                   l.loan_date as tanggal_pinjam, l.due_date as jatuh_tempo, l.status
            FROM loans l
            JOIN users u ON l.user_id = u.id
            JOIN books b ON l.book_id = b.id
            WHERE u.name LIKE ?
            ORDER BY l.id DESC
        `, [`%${keyword}%`]);
        console.log(`[DB] getStatusPeminjaman menemukan ${rows.length} riwayat/transaksi.`);
        return {
            status: rows.length > 0 ? 'success' : 'not_found',
            nama_siswa: keyword,
            total_pinjaman: rows.length,
            daftar_pinjaman: rows
        };
    } catch (err) {
        console.error(`[DB ERROR] getStatusPeminjaman gagal:`, err.message);
        return { error: err.message };
    }
}

// Helper tool tambahan yang sudah ada sebelumnya
async function getDaftarBuku({ kategori } = {}) {
    console.log(`[DB] Menjalankan getDaftarBuku, kategori: "${kategori || 'Semua'}"`);
    try {
        let query = `
            SELECT b.title as judul, b.author as penulis, b.stock_available as stok, c.name as kategori, b.publisher as penerbit, b.year as tahun
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
        `;
        const params = [];
        if (kategori) {
            query += ` WHERE c.name LIKE ?`;
            params.push(`%${kategori}%`);
        }
        query += ` ORDER BY b.title ASC`;
        const [rows] = await dbPool.query(query, params);
        return { status: 'success', total_buku: rows.length, daftar_buku: rows };
    } catch (err) {
        return { error: err.message };
    }
}

async function getBukuByPenulis({ nama_penulis }) {
    console.log(`[DB] Menjalankan getBukuByPenulis: "${nama_penulis}"`);
    try {
        const searchPenulis = String(nama_penulis || '').trim();
        const [rows] = await dbPool.query(`
            SELECT title as judul, author as penulis, stock_available as stok, publisher as penerbit, year as tahun
            FROM books 
            WHERE author LIKE ?
        `, [`%${searchPenulis}%`]);
        return { status: 'success', total_ditemukan: rows.length, daftar_buku: rows };
    } catch (err) {
        return { error: err.message };
    }
}

async function getDaftarGuru() {
    console.log(`[DB] Menjalankan getDaftarGuru`);
    try {
        const [rows] = await dbPool.query(`
            SELECT name as nama, nip, class as kelas, phone as telepon FROM users WHERE role = 'guru' ORDER BY class, name
        `);
        return { total_guru: rows.length, daftar_guru: rows };
    } catch (err) {
        return { error: err.message };
    }
}

async function getSiswaBelumAbsen() {
    console.log(`[DB] Menjalankan getSiswaBelumAbsen`);
    try {
        const today = new Date().toISOString().slice(0, 10);
        const [rows] = await dbPool.query(`
            SELECT u.id, u.name, u.class, u.nisn 
            FROM users u 
            WHERE u.role = 'siswa' 
              AND (u.status = 'aktif' OR u.status IS NULL)
              AND u.id NOT IN (
                  SELECT user_id FROM kunjungans WHERE tanggal = ?
              )
            ORDER BY u.class, u.name
        `, [today]);
        return { tanggal: today, jumlah_belum_absen: rows.length, daftar_siswa: rows };
    } catch (err) {
        return { error: err.message };
    }
}

async function getTotalBuku() {
    console.log(`[DB] Menjalankan getTotalBuku`);
    try {
        const [books] = await dbPool.query(`
            SELECT COUNT(*) as total_judul, IFNULL(SUM(stock_total), 0) as total_eksemplar, IFNULL(SUM(stock_available), 0) as total_tersedia FROM books
        `);
        const [loans] = await dbPool.query(`
            SELECT COUNT(*) as total_transaksi,
                   SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
                   SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu_persetujuan,
                   SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_kembali
            FROM loans
        `);
        return { statistik_buku: books[0], statistik_sirkulasi: loans[0] };
    } catch (err) {
        return { error: err.message };
    }
}

async function getWaliKelas({ kelas }) {
    console.log(`[DB] Menjalankan getWaliKelas untuk kelas: "${kelas}"`);
    try {
        const searchKelas = String(kelas || '').trim();
        const [rows] = await dbPool.query(`
            SELECT name, nip, phone, role, class FROM users WHERE role = 'guru' AND class LIKE ? LIMIT 1
        `, [`%${searchKelas}%`]);
        return rows.length > 0 ? { status: 'ditemukan', wali_kelas: rows[0] } : { status: 'tidak_ditemukan' };
    } catch (err) {
        return { error: err.message };
    }
}

// Definisi Tools untuk Gemini API
const tools = [
    {
        functionDeclarations: [
            {
                name: "cariSiswa",
                description: "Mencari data siswa tertentu berdasarkan nama (contoh: 'Nacinta', 'Adrian', 'Dewi') untuk mengecek apakah siswa tersebut terdaftar di perpustakaan/sekolah beserta kelasnya.",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        nama: {
                            type: "STRING",
                            description: "Nama atau potongan nama siswa yang dicari"
                        }
                    },
                    required: ["nama"]
                }
            },
            {
                name: "cariGuru",
                description: "Mencari data guru tertentu berdasarkan nama untuk mengecek apakah guru tersebut terdaftar dan mengajar/wali kelas apa.",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        nama: {
                            type: "STRING",
                            description: "Nama atau potongan nama guru yang dicari"
                        }
                    },
                    required: ["nama"]
                }
            },
            {
                name: "cariBuku",
                description: "Mencari buku tertentu berdasarkan judul (contoh: 'Laskar Pelangi', 'Matematika', 'Harry Potter') untuk mengetahui ketersediaan dan stoknya.",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        judul: {
                            type: "STRING",
                            description: "Judul atau potongan judul buku yang dicari"
                        }
                    },
                    required: ["judul"]
                }
            },
            {
                name: "getTotalAnggota",
                description: "Mendapatkan jumlah total seluruh anggota perpustakaan yang terdaftar secara real-time.",
                parameters: {
                    type: "OBJECT",
                    properties: {}
                }
            },
            {
                name: "getStatusPeminjaman",
                description: "Mengecek status peminjaman buku oleh siswa tertentu berdasarkan nama siswa (apakah sedang meminjam buku, judul buku yang dipinjam, dan batas pengembaliannya).",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        nama_siswa: {
                            type: "STRING",
                            description: "Nama siswa yang ingin diperiksa status peminjamannya"
                        }
                    },
                    required: ["nama_siswa"]
                }
            },
            {
                name: "getDaftarBuku",
                description: "Mendapatkan seluruh daftar buku atau rekomendasi buku bacaan/fiksi/pelajaran di perpustakaan.",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        kategori: { type: "STRING", description: "Filter kategori opsional" }
                    }
                }
            },
            {
                name: "getBukuByPenulis",
                description: "Mencari koleksi buku berdasarkan nama penulis/pengarang (contoh: 'Andrea Hirata').",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        nama_penulis: { type: "STRING", description: "Nama penulis buku" }
                    },
                    required: ["nama_penulis"]
                }
            },
            {
                name: "getDaftarGuru",
                description: "Mendapatkan daftar lengkap seluruh guru dan wali kelas.",
                parameters: { type: "OBJECT", properties: {} }
            },
            {
                name: "getSiswaBelumAbsen",
                description: "Mendapatkan daftar siswa yang belum melakukan absen/kunjungan hari ini.",
                parameters: { type: "OBJECT", properties: {} }
            },
            {
                name: "getTotalBuku",
                description: "Mendapatkan statistik total koleksi buku dan transaksi sirkulasi perpustakaan.",
                parameters: { type: "OBJECT", properties: {} }
            },
            {
                name: "getWaliKelas",
                description: "Mencari wali kelas berdasarkan kelas (misal: '2A', 'Kelas 1').",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        kelas: { type: "STRING", description: "Tingkatan kelas" }
                    },
                    required: ["kelas"]
                }
            }
        ]
    }
];

// ==========================================
// BAGIAN 2 & 3: ENDPOINT CHAT & SYSTEM PROMPT
// ==========================================

app.post('/api/chat', async (req, res) => {
    console.log("\n=======================================================");
    console.log("--> [REQUEST MASUK] di POST /api/chat:", req.body ? { ...req.body, imageBase64: req.body.imageBase64 ? '[IMAGE_ATTACHED]' : undefined } : {});
    
    try {
        const { pesan, userName, userRole, imageBase64, imageMime } = req.body;
        if (!pesan && !imageBase64) {
            console.warn("⚠️ Pesan dan gambar kosong diterima.");
            return res.status(400).json({ error: "Pesan atau gambar wajib diisi." });
        }

        // MASTER SYSTEM PROMPT (OTAK AI)
        const systemInstruction = `Kamu adalah PustakaAI, asisten virtual cerdas untuk Perpustakaan SDN Pengampon 3. Tugas utamamu adalah membantu siswa, guru, dan petugas dalam mencari informasi. 

INFORMASI PENGGUNA AKTIF SAAT INI:
- Nama Pengguna: ${userName || 'Faidil Aqiel'}
- Peran: ${userRole || 'Petugas Perpustakaan'}

ATURAN MUTLAK (JANGAN DILANGGAR):
1. DILARANG KERAS memberikan sapaan default (seperti 'Ada yang bisa saya bantu?') jika user sudah mengajukan pertanyaan spesifik. LANGSUNG JAWAB PERTANYAANNYA.
2. Jika user bertanya tentang nama orang (siswa/guru) atau judul buku spesifik, KAMU WAJIB memanggil fungsi pencarian (cariSiswa, cariGuru, atau cariBuku). JANGAN mengarang jawaban.
3. Jika fungsi pencarian mengembalikan data kosong, katakan dengan jujur: 'Maaf, data tersebut tidak ditemukan di sistem perpustakaan.'
4. Jika user bertanya tentang PROSEDUR (cara pinjam, aturan), JAWAB LANGSUNG dengan teks di bawah ini (jangan panggil fungsi database).

PENGETAHUAN DASAR (PROSEDUR):
- Cara Pinjam: Bawa kartu anggota & buku ke meja petugas. Maksimal 3 buku, batas waktu 7 hari.
- Peraturan: Dilarang membawa makanan/minuman. Wajib menjaga ketenangan.
- Jika kartu hilang, segera lapor petugas untuk diblokir dan dicetak ulang.
- Jam Operasional: Buka Senin - Jumat pukul 07:00 - 14:00 WIB. Sabtu, Minggu, dan Hari Libur Nasional tutup.
- Denda: Denda keterlambatan Rp 1.000,-/hari pada hari buka sekolah.

GAYA BAHASA:
- Gunakan bahasa Indonesia yang ramah, sopan, dan tidak kaku.
- Jika user menggunakan sapaan santai (seperti 'Halo kak'), balaslah dengan ramah. Jika formal, balaslah dengan formal.

FORMAT JAWABAN:
- Jika memberikan daftar (misal: daftar nama atau buku), WAJIB gunakan format poin (1, 2, 3) atau bullet points. Jangan menumpuk teks dalam satu paragraf panjang.
`;

        console.log("⚙️ Menginisialisasi Gemini Generative Model (gemini-3.1-flash-lite)...");
        const model = genAI.getGenerativeModel({
            model: "gemini-3.1-flash-lite",
            systemInstruction: systemInstruction,
            tools: tools
        });

        const chat = model.startChat();
        console.log(`🤖 Mengirim pesan ke Gemini: "${pesan || '[Gambar Lampiran]'}"`);

        let messagePayload;
        if (imageBase64) {
            const rawBase64 = imageBase64.includes(',') ? imageBase64.split(',')[1] : imageBase64;
            const mimeType = imageMime || 'image/jpeg';
            messagePayload = [
                {
                    inlineData: {
                        data: rawBase64,
                        mimeType: mimeType
                    }
                },
                pesan || "Tolong amati gambar ini dan berikan informasi seputar buku/perpustakaan."
            ];
        } else {
            messagePayload = pesan;
        }

        let result = await chat.sendMessage(messagePayload);
        let response = await result.response;

        // Loop penanganan Function Calls (bisa menangani beberapa tool call beruntun)
        let functionCalls = response.functionCalls();
        while (functionCalls && functionCalls.length > 0) {
            const call = functionCalls[0];
            console.log(`🎯 [TOOL CALL DIPICU]: ${call.name}`, JSON.stringify(call.args));
            let functionResult = null;

            switch (call.name) {
                case "cariSiswa":
                    functionResult = await cariSiswa(call.args || {});
                    break;
                case "cariGuru":
                    functionResult = await cariGuru(call.args || {});
                    break;
                case "cariBuku":
                    functionResult = await cariBuku(call.args || {});
                    break;
                case "getTotalAnggota":
                    functionResult = await getTotalAnggota();
                    break;
                case "getStatusPeminjaman":
                    functionResult = await getStatusPeminjaman(call.args || {});
                    break;
                case "getDaftarBuku":
                    functionResult = await getDaftarBuku(call.args || {});
                    break;
                case "getBukuByPenulis":
                    functionResult = await getBukuByPenulis(call.args || {});
                    break;
                case "getDaftarGuru":
                    functionResult = await getDaftarGuru();
                    break;
                case "getSiswaBelumAbsen":
                    functionResult = await getSiswaBelumAbsen();
                    break;
                case "getTotalBuku":
                    functionResult = await getTotalBuku();
                    break;
                case "getWaliKelas":
                    functionResult = await getWaliKelas(call.args || {});
                    break;
                default:
                    console.warn(`Tool "${call.name}" tidak dikenali.`);
                    functionResult = { error: "Fungsi tidak ditemukan" };
            }

            console.log(`📤 Mengirim balasan database untuk [${call.name}] kembali ke Gemini...`);
            result = await chat.sendMessage([
                {
                    functionResponse: {
                        name: call.name,
                        response: { output: functionResult }
                    }
                }
            ]);
            response = await result.response;
            functionCalls = response.functionCalls();
        }

        const jawaban = response.text();
        console.log(`✅ [JAWABAN SUKSES DIKIRIM]:\n${jawaban}\n=======================================================\n`);
        return res.json({ jawaban });

    } catch (error) {
        console.error("❌ ERROR ASLI pada pemrosesan Gemini di POST /api/chat:", error);
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
