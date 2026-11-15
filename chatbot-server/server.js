require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const https = require('https');

const app = express();
app.use(cors());
app.use(express.json());

const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
const MODEL_NAME = process.env.GEMINI_MODEL || "gemini-flash-latest";

// Pool Koneksi MySQL Basis Data Perpustakaan
const dbPool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'perpustakaan_sdn_pengampon3',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Helper request HTTPS ke Gemini API
function callGeminiAPI(payload) {
    return new Promise((resolve, reject) => {
        const bodyStr = JSON.stringify(payload);
        const url = `https://generativelanguage.googleapis.com/v1beta/models/${MODEL_NAME}:generateContent?key=${GEMINI_API_KEY}`;
        const req = https.request(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(bodyStr)
            }
        }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(data);
                    if (res.statusCode >= 400) {
                        return reject(new Error(parsed.error?.message || `HTTP ${res.statusCode}: ${data}`));
                    }
                    resolve(parsed);
                } catch (e) {
                    reject(new Error(`Gagal parse respons Gemini: ${data}`));
                }
            });
        });

        req.on('error', reject);
        req.write(bodyStr);
        req.end();
    });
}

// ── 1. Implementasi Fungsi Database ──────────────────────────────────────

// Skenario 1: Siswa yang belum absen / berkunjung hari ini
async function getSiswaBelumAbsen() {
    try {
        const today = new Date().toISOString().slice(0, 10);
        const [rows] = await dbPool.query(`
            SELECT u.id, u.name, u.class, u.nisn, u.phone 
            FROM users u 
            WHERE u.role = 'siswa' 
              AND (u.status = 'aktif' OR u.status IS NULL)
              AND u.id NOT IN (
                  SELECT user_id FROM kunjungans WHERE tanggal = ?
              )
            ORDER BY u.class, u.name
        `, [today]);
        
        return {
            tanggal: today,
            jumlah_siswa_belum_absen: rows.length,
            daftar_siswa: rows.map(s => ({
                nama: s.name,
                kelas: s.class || '-',
                nisn: s.nisn || '-'
            }))
        };
    } catch (err) {
        console.error("getSiswaBelumAbsen error:", err);
        return { error: err.message };
    }
}

// Skenario 2: Total buku, stok, dan statistik sirkulasi transaksi
async function getTotalBuku() {
    try {
        const [books] = await dbPool.query(`
            SELECT 
                COUNT(*) as total_judul_buku,
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
        const [koleksiList] = await dbPool.query(`
            SELECT code, title, author, year, stock_available, stock_total 
            FROM books 
            ORDER BY title ASC
        `);

        return {
            ringkasan_buku: books[0],
            ringkasan_sirkulasi: loans[0],
            daftar_koleksi: koleksiList
        };
    } catch (err) {
        console.error("getTotalBuku error:", err);
        return { error: err.message };
    }
}

// Skenario 3: Mencari Wali Kelas berdasarkan nama kelas
async function getWaliKelas(kelas) {
    try {
        const cleanKelas = (kelas || '').trim();
        const [rows] = await dbPool.query(`
            SELECT name, nip, phone, role, class 
            FROM users 
            WHERE role = 'guru' AND (class LIKE ? OR class = ?)
            LIMIT 1
        `, [`%${cleanKelas}%`, cleanKelas]);
        
        if (rows.length === 0) {
            const [allGuru] = await dbPool.query(`SELECT name, class, nip FROM users WHERE role = 'guru' AND class IS NOT NULL ORDER BY class`);
            return {
                status: 'tidak_ditemukan',
                pesan: `Wali kelas ${cleanKelas} tidak ditemukan secara spesifik.`,
                daftar_wali_kelas_tersedia: allGuru
            };
        }
        return {
            status: 'ditemukan',
            wali_kelas: rows[0]
        };
    } catch (err) {
        console.error("getWaliKelas error:", err);
        return { error: err.message };
    }
}

// ── 2. Definisi Tools (Function Calling) untuk Gemini ─────────────────────
const tools = [
    {
        functionDeclarations: [
            {
                name: "getSiswaBelumAbsen",
                description: "Mengambil data daftar siswa yang belum hadir atau belum absen / belum berkunjung ke perpustakaan hari ini.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
            {
                name: "getTotalBuku",
                description: "Mengambil statistik total judul buku, total stok eksemplar, stok buku tersedia, dan total transaksi peminjaman (sirkulasi) dari database MySQL.",
                parameters: {
                    type: "OBJECT",
                    properties: {},
                }
            },
            {
                name: "getWaliKelas",
                description: "Mencari data nama guru atau wali kelas berdasarkan nomor atau nama kelas (contoh: '2A', 'Kelas 1', '4', '6').",
                parameters: {
                    type: "OBJECT",
                    properties: {
                        kelas: {
                            type: "STRING",
                            description: "Identitas atau nama kelas yang dicari, contoh: '2A', 'Kelas 1', '6'"
                        }
                    },
                    required: ["kelas"]
                }
            }
        ]
    }
];

// ── 3. Endpoint POST /api/chat ───────────────────────────────────────────
app.post('/api/chat', async (req, res) => {
    try {
        const { pesan, userName, userRole } = req.body;
        if (!pesan || typeof pesan !== 'string') {
            return res.status(400).json({ error: "Pesan tidak boleh kosong." });
        }

        const nameUser = userName || 'Faidil Aqiel';
        const roleUser = userRole || 'Petugas Perpustakaan / Administrator';

        const systemInstruction = {
            parts: [
                {
                    text: `Anda adalah PustakaAI, asisten digital cerdas dan ramah di Perpustakaan Digital SDN Pengampon 3 Cirebon.
Pengguna yang sedang aktif saat ini:
- Nama Pengguna: ${nameUser}
- Peran Resmi: ${roleUser}

ATURAN DAN PEDOMAN:
1. Jika pengguna bertanya "siapa saya", "profil saya", atau siapa akun yang sedang login, sapa dengan ramah dan jelaskan bahwa pengguna adalah ${nameUser}, bertindak sebagai ${roleUser} di SDN Pengampon 3.
2. Jika pengguna menanyakan data siswa yang belum absen/hadir, total buku/koleksi/stok, atau wali kelas, Anda WAJIB memanggil Function Call yang relevan agar mendapatkan data real-time dari database MySQL perpustakaan.
3. Selalu berikan jawaban yang ramah, sopan, jelas, terstruktur (gunakan bullet points jika daftar), dan akurat.`
                }
            ]
        };

        // Buat payload awal untuk Gemini
        const contents = [
            {
                role: 'user',
                parts: [{ text: pesan }]
            }
        ];

        const payloadStep1 = {
            systemInstruction: systemInstruction,
            contents: contents,
            tools: tools
        };

        const resStep1 = await callGeminiAPI(payloadStep1);
        const candidate = resStep1.candidates?.[0];
        if (!candidate || !candidate.content) {
            return res.json({ jawaban: "Maaf, PustakaAI belum dapat merespons saat ini." });
        }

        const modelParts = candidate.content.parts || [];
        const functionCallPart = modelParts.find(p => p.functionCall);

        // Jika Gemini memanggil fungsi (Function Calling)
        if (functionCallPart && functionCallPart.functionCall) {
            const call = functionCallPart.functionCall;
            let queryResult = null;

            if (call.name === "getSiswaBelumAbsen") {
                queryResult = await getSiswaBelumAbsen();
            } else if (call.name === "getTotalBuku") {
                queryResult = await getTotalBuku();
            } else if (call.name === "getWaliKelas") {
                queryResult = await getWaliKelas(call.args?.kelas || '');
            }

            // Siapkan respons fungsi untuk dikirim kembali ke Gemini
            const fnResponsePart = {
                functionResponse: {
                    name: call.name,
                    response: { output: queryResult }
                }
            };
            if (call.id) {
                fnResponsePart.functionResponse.id = call.id;
            }

            const step2Contents = [
                ...contents,
                candidate.content,
                {
                    role: 'user',
                    parts: [fnResponsePart]
                }
            ];

            const payloadStep2 = {
                systemInstruction: systemInstruction,
                contents: step2Contents,
                tools: tools
            };

            const resStep2 = await callGeminiAPI(payloadStep2);
            const candidate2 = resStep2.candidates?.[0];
            const answerText = candidate2?.content?.parts?.map(p => p.text).filter(Boolean).join("\n") || "Data berhasil diproses dari basis data.";
            return res.json({ jawaban: answerText });
        }

        // Jika respons teks biasa (tanpa pemanggilan fungsi)
        const answerText = modelParts.map(p => p.text).filter(Boolean).join("\n") || "Halo! Ada yang bisa PustakaAI bantu hari ini?";
        return res.json({ jawaban: answerText });

    } catch (error) {
        console.error("Error pada endpoint /api/chat:", error);
        return res.status(500).json({ 
            jawaban: "Maaf, terjadi kendala saat memproses permintaan: " + (error.message || "Koneksi terputus") 
        });
    }
});

// Endpoint status server
app.get('/api/status', (req, res) => {
    res.json({ 
        status: 'ok', 
        service: 'PustakaAI Gemini Function Calling Server', 
        model: MODEL_NAME,
        timestamp: new Date() 
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`🚀 [PustakaAI] Server Gemini Function Calling aktif di http://localhost:${PORT}`);
});
