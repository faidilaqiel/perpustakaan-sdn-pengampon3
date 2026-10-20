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

        // Daftar model yang VALID
        $models = [
            'gemini-3.8-flash',
            'gemini-2.5-flash',
            'gemini-3-flash-preview'
        ];

        $lastError = 'Tidak ada model yang tersedia.';

        // SYSTEM INSTRUCTION (Pengetahuan Dasar AI)
        $systemInstruction = "Anda adalah asisten perpustakaan digital SDN Pengampon 3. Jawablah dengan ramah dan singkat. Informasi penting: Kepala Sekolah SDN Pengampon 3 saat ini adalah H. Noni Risanto, M.Pd.I.";

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