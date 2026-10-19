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

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $message]]]
                ],
                'systemInstruction' => [
                    'parts' => [['text' => 'Anda adalah asisten perpustakaan digital SDN Pengampon 3. Jawablah dengan ramah dan singkat.']]
                ]
            ]);

            $data = $response->json();
            
            // --- INI BAGIAN YANG DIPERBAIKI ---
            // Kita baca jawabannya dengan lebih teliti
            $reply = 'Maaf, saya sedang tidak bisa memproses jawaban.';
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = $data['candidates'][0]['content']['parts'][0]['text'];
            } elseif (isset($data['candidates'][0]['content']['parts'])) {
                // Jika formatnya agak beda, kita gabungkan semua teksnya
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
            // ----------------------------------

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            return response()->json(['reply' => 'Terjadi kesalahan pada server AI: ' . $e->getMessage()], 500);
        }
    }
}