<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        if (!$message || !$apiKey) {
            return response()->json(['reply' => 'Konfigurasi Chatbot belum lengkap.'], 500);
        }

        $models = ['gemini-3.8-flash', 'gemini-2.5-flash', 'gemini-3-flash-preview'];
        $lastError = 'Tidak ada model yang tersedia.';

        // PROMPT AI YANG CERDAS & SOFT
        $systemInstruction = "
            Anda adalah 'Asisten Perpustakaan Digital SDN Pengampon 3'. 
            Tugas Anda adalah membantu siswa, guru, dan petugas dalam mencari informasi seputar perpustakaan.
            
            Gaya Bicara:
            - Gunakan bahasa Indonesia yang ramah, sopan, dan hangat (seperti guru yang baik).
            - Jawab dengan singkat, padat, dan jelas. Jangan bertele-tele.
            - Gunakan emoji yang relevan (misalnya 📚, 😊, ✨) untuk membuat percakapan lebih hidup.
            
            Pengetahuan Dasar:
            - Kepala Sekolah SDN Pengampon 3 adalah H. Noni Risanto, M.Pd.I.
            - Perpustakaan memiliki koleksi Buku Pelajaran, Cerita Rakyat, Buku Agama, dan Ensiklopedia.
            - Jika ditanya tentang cara pinjam, jelaskan bahwa siswa bisa memilih buku di Katalog dan menunggu konfirmasi Petugas.
            - Jika Anda tidak tahu jawabannya, katakan dengan sopan bahwa Anda hanya asisten perpustakaan yang siap membantu hal-hal seputar buku.
        ";

        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $message]]]
                    ],
                    'systemInstruction' => [
                        'parts' => [['text' => $systemInstruction]]
                    ]
                ]);

                $data = $response->json();

                if (isset($data['error'])) {
                    $lastError = $data['error']['message'];
                    continue; 
                }

                $reply = null;
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
                }

                if ($reply) {
                    return response()->json(['reply' => $reply]);
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return response()->json(['reply' => 'Error dari Google: ' . $lastError], 500);
    }
}