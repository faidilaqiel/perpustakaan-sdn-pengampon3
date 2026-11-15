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

async function getDaftarBuku({ kategori } = {}) {
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
        return {
            status: 'success',
            total_buku: rows.length,
            daftar_buku: rows
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
                name: "getDaftarBuku",
                description: "Mendapatkan daftar buku yang ada di perpustakaan beserta kategorinya, judul, penulis, dan stok. Gunakan fungsi ini jika user meminta rekomendasi buku, buku cerita/dongeng/fiksi anak, atau daftar koleksi buku.",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        kategori: {
                            type: "STRING",
                            description: "Filter opsional berdasarkan nama kategori (contoh: 'Cerita Rakyat', 'Pelajaran', 'Umum')"
                        }
                    }
                }
            },
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
    console.log("--> Menerima request di POST /api/chat:", req.body ? { ...req.body, imageBase64: req.body.imageBase64 ? '[IMAGE_BASE64_ATTACHED]' : undefined } : {});
    try {
        const { pesan, userName, userRole, imageBase64, imageMime } = req.body;
        if (!pesan && !imageBase64) {
            console.warn("Pesan dan gambar kosong diterima di /api/chat");
            return res.status(400).json({ error: "Pesan atau gambar wajib diisi." });
        }

        const systemInstruction = `
Kamu adalah PustakaAI, asisten virtual Perpustakaan SDN Pengampon 3.
Tugasmu adalah membantu siswa, guru, dan petugas perpustakaan.

INFORMASI PENGGUNA AKTIF:
- Nama Pengguna: ${userName || 'Faidil Aqiel'}
- Peran: ${userRole || 'Petugas Perpustakaan'}

PENGETAHUAN DASAR (KNOWLEDGE BASE) - JAWAB DENGAN TEKS BIASA TANPA MEMANGGIL DATABASE:
Gunakan panduan berikut untuk menjawab pertanyaan user seputar prosedur, panduan, keanggotaan, atau peraturan:

A. PROSEDUR PEMINJAMAN:
- Bawa kartu anggota dan buku ke meja petugas.
- Petugas akan memindai kartu dan buku.
- Maksimal pinjam 3 buku.
- Batas waktu peminjaman adalah 7 hari.

B. KEANGGOTAAN:
- Seluruh siswa dan guru SDN Pengampon 3 boleh menjadi anggota.
- Jika kartu hilang, segera lapor ke petugas untuk diblokir dan dicetak ulang.

C. PERATURAN PERPUSTAKAAN:
- Dilarang membawa makanan atau minuman.
- Wajib menjaga ketenangan.
- Denda akan dikenakan jika buku rusak atau hilang.
- Denda keterlambatan pengembalian buku adalah Rp 1.000,-/hari pada hari buka sekolah.
- Jam Operasional: Buka Senin - Jumat pukul 07:00 - 14:00 WIB. Sabtu, Minggu, dan Hari Libur Nasional tutup.

D. TENTANG APLIKASI:
- Aplikasi ini digunakan oleh Petugas Piket untuk mencatat sirkulasi (peminjaman/pengembalian) dan data buku secara real-time.

ATURAN MUTLAK PENGGUNAAN DATABASE (TOOLS) VS TEKS BIASA:
1. ATURAN KAPAN MENJAWAB TEKS BIASA (DILARANG MEMANGGIL DATABASE):
   - JIKA user menanyakan PROSEDUR, PANDUAN, ATURAN, CARA PINJAM, SYARAT ANGGOTA, atau TENTANG APLIKASI (contoh: "Bagaimana cara meminjam buku?", "Berapa lama batas peminjaman?", "Apa syarat anggota?", "Bolehkah bawa makanan?"), AI WAJIB menjawab menggunakan teks panduan A, B, C, D di atas.
   - DILARANG KERAS memanggil fungsi database buku (seperti getDaftarBuku atau getTotalBuku) untuk pertanyaan prosedur/aturan!

2. ATURAN KAPAN MEMANGGIL DATABASE (TOOLS):
   - HANYA panggil fungsi database JIKA user menanyakan DATA SPESIFIK:
     * "Ada buku apa saja?" / "Rekomendasi buku anak" -> getDaftarBuku
     * "Buku karya Andrea Hirata ada?" / "Buku karya [Penulis]?" -> getBukuByPenulis
     * "Berapa jumlah anggota?" / "Siapa saja anggotanya?" -> getTotalAnggota
     * "Siapa saja guru di sekolah?" -> getDaftarGuru
     * "Berapa total buku & transaksi?" -> getTotalBuku
     * "Siapa siswa yang belum absen hari ini?" -> getSiswaBelumAbsen
     * "Siapa wali kelas 2A?" -> getWaliKelas

3. ATURAN SAPAAN & INTERAKSI:
   - DILARANG KERAS memberikan sapaan default (seperti 'Ada yang bisa saya bantu?') jika user sudah mengajukan pertanyaan spesifik. Langsung jawab pertanyaannya dengan ramah dan to-the-point.
   - Jika user hanya menyapa singkat ("Halo", "Hai"), balas: "Halo! Saya PustakaAI. Mau cari buku cerita, buku pelajaran, atau cek status peminjaman hari ini?"
   - Jika user mengucapkan "Terima kasih" atau "Mantap", balas antusias: "Sama-sama! Semoga bukunya bermanfaat. Jangan lupa berikan ulasan atau saran untuk perpustakaan kami ya!"
   - Jika user siswa ("Halo Kak/Min"), gunakan nada santai dan ramah anak. Jika guru/staf, gunakan nada profesional.
   - DILARANG HALUSINASI: Jangan mengarang judul buku atau nama orang yang tidak ada di hasil database. Jika tidak ditemukan, katakan data belum tersedia.
   - Gunakan penomoran rapi (1, 2, 3) atau bullet points dengan baris baru (\\n) agar tampilan rapi.
        `;

        console.log("Menginisialisasi model gemini-3.1-flash-lite...");
        const model = genAI.getGenerativeModel({
            model: "gemini-3.1-flash-lite",
            systemInstruction: systemInstruction,
            tools: tools
        });

        const chat = model.startChat();
        console.log("Mengirim pesan user ke Gemini:", pesan || '[Lampiran Gambar]');

        let messagePayload;
        if (imageBase64) {
            // Bersihkan data URL prefix jika ada (misal data:image/jpeg;base64,...)
            const rawBase64 = imageBase64.includes(',') ? imageBase64.split(',')[1] : imageBase64;
            const mimeType = imageMime || 'image/jpeg';
            messagePayload = [
                {
                    inlineData: {
                        data: rawBase64,
                        mimeType: mimeType
                    }
                },
                pesan || "Tolong amati dan jelaskan gambar/buku ini sehubungan dengan informasi perpustakaan SDN Pengampon 3."
            ];
        } else {
            messagePayload = pesan;
        }

        let result = await chat.sendMessage(messagePayload);
        let response = await result.response;

        // Tangani Function Calls jika model meminta data database
        const functionCalls = response.functionCalls();
        if (functionCalls && functionCalls.length > 0) {
            const call = functionCalls[0];
            console.log("Gemini memicu Function Call:", call.name, call.args);
            let functionResult = null;

            if (call.name === "getDaftarBuku") {
                functionResult = await getDaftarBuku(call.args || {});
            } else if (call.name === "getBukuByPenulis") {
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
