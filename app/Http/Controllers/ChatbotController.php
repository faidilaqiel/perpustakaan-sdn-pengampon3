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

        // Model yang valid untuk API v1beta
        $model = 'gemini-3.8-flash'; 

        try {
            // Mengirim API Key lewat URL (?key=...), bukan lewat header
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

            // Jika Google mengirim error (misal key salah, model tidak ada), tampilkan pesannya
            if (isset($data['error'])) {
                return response()->json(['reply' => 'Error dari Google: ' . $data['error']['message']], 500);
            }

            // Membaca jawaban AI
            $reply = 'Maaf, format jawaban tidak dikenali.';
            
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

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            return response()->json(['reply' => 'Kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}