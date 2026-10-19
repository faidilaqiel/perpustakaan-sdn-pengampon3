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
            return response()->json(['reply' => 'Konfigurasi Chatbot belum lengkap. Pastikan API Key sudah diisi.'], 500);
        }

        // Daftar model yang akan dicoba (dari yang paling stabil)
        $models = [
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-pro'
        ];

        $lastError = 'Tidak ada model yang tersedia.';

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
                        'parts' => [['text' => 'Anda adalah asisten perpustakaan digital SDN Pengampon 3. Jawablah dengan ramah dan singkat.']]
                    ]
                ]);

                $data = $response->json();

                // Jika Google mengirim error (misal model sibuk), simpan pesannya dan coba model berikutnya
                if (isset($data['error'])) {
                    $lastError = $data['error']['message'];
                    continue; // Lanjut ke model berikutnya
                }

                // Membaca jawaban AI
                $reply = null;
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'];
                } elseif (isset($data['candidates'][0]['content']['parts'])) {
                    $parts = $data['candidates'][0]['content']['parts'];
                    $texts = [];
                    foreach ($parts as $part) {
                        if (isset($part['text'])) {
                            $texts[] = $part['text'];
                        }
                    }
                    if (!empty($texts)) {
                        $reply = implode(' ', $texts);
                    }
                }

                // Jika berhasil dapat jawaban, kembalikan ke pengguna
                if ($reply) {
                    return response()->json(['reply' => $reply]);
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        // Jika semua model gagal, tampilkan error terakhir
        return response()->json(['reply' => 'Error dari Google: ' . $lastError], 500);
    }
}