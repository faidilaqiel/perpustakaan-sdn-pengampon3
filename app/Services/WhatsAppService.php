<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatsAppService
{
    /**
     * Format nomor telepon Indonesia ke format internasional (62...)
     */
    public static function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Ambil hanya digit
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // Jika diawali 08 -> ubah 0 jadi 62
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Dapatkan link WhatsApp Web / App (wa.me) dengan pesan yang sudah di-encode
     */
    public static function getWaLink($phone, $message)
    {
        $target = self::formatPhone($phone);
        if (!$target) {
            return null;
        }
        return 'https://wa.me/' . $target . '?text=' . urlencode($message);
    }

    /**
     * Kirim pesan WhatsApp melalui API Gateway (Fonnte / Wablas / Custom)
     */
    public static function sendMessage($phone, $message)
    {
        $target = self::formatPhone($phone);
        if (!$target) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp kosong atau tidak valid.',
                'wa_link' => null,
            ];
        }

        $provider = env('WA_GATEWAY_PROVIDER', 'fonnte');
        $token = env('WA_API_TOKEN', 'b9UZBMFepfWR2P5neAAK');
        $apiUrl = env('WA_API_URL');

        // Jika belum ada token API, jalankan dalam mode simulasi aman & log
        if (empty($token)) {
            Log::info("[WhatsApp Simulator] Ke: {$target} | Pesan:\n{$message}");
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Simulasi Berhasil: Token WA_API_TOKEN belum disetel di .env. Pesan dicatat di log aplikasi.',
                'wa_link' => self::getWaLink($target, $message),
            ];
        }

        try {
            if ($provider === 'fonnte') {
                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->timeout(12)->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

                $resData = $response->json();
                $isSuccess = $response->successful() && ($resData['status'] ?? false);

                Log::info("[WhatsApp Gateway Fonnte] Ke: {$target} | Status: " . ($isSuccess ? 'SUKSES' : 'GAGAL') . " | Respon: " . json_encode($resData));

                return [
                    'success' => $isSuccess,
                    'response' => $resData,
                    'wa_link' => self::getWaLink($target, $message),
                ];
            } elseif ($provider === 'wablas') {
                $endpoint = $apiUrl ?: 'https://phone.wablas.com/api/send-message';
                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->timeout(12)->post($endpoint, [
                    'phone' => $target,
                    'message' => $message,
                ]);

                return [
                    'success' => $response->successful(),
                    'response' => $response->json(),
                    'wa_link' => self::getWaLink($target, $message),
                ];
            } else {
                // Generic Gateway
                $endpoint = $apiUrl ?: 'https://api.fonnte.com/send';
                $response = Http::withHeaders([
                    'Authorization' => $token,
                ])->timeout(12)->post($endpoint, [
                    'target' => $target,
                    'message' => $message,
                ]);

                return [
                    'success' => $response->successful(),
                    'response' => $response->json(),
                    'wa_link' => self::getWaLink($target, $message),
                ];
            }
        } catch (\Throwable $e) {
            Log::error("[WhatsApp Gateway Error] Ke: {$target} | Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal koneksi ke API WhatsApp: ' . $e->getMessage(),
                'wa_link' => self::getWaLink($target, $message),
            ];
        }
    }

    /**
     * 1. Notifikasi Konfirmasi Peminjaman
     */
    /**
     * 1. Notifikasi Konfirmasi Peminjaman
     */
    public static function sendLoanConfirmation(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;

        if (!$user || !$book) {
            return null;
        }

        $nama = $user->name;
        $judul = $book->title;
        $tglPinjam = Carbon::parse($loan->loan_date)->locale('id')->translatedFormat('d F Y');
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        $pesan = "Halo {$nama},\n"
            . "Peminjaman buku Anda telah berhasil.\n"
            . "📚 Buku: {$judul}\n"
            . "📅 Tanggal Pinjam: {$tglPinjam}\n"
            . "⏳ Batas Kembali: {$tglTempo}\n"
            . "Mohon jaga buku dengan baik. Terima kasih.\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";

        return self::sendMessage($user->phone, $pesan);
    }

    /**
     * Dapatkan teks template konfirmasi peminjaman
     */
    public static function getLoanConfirmationText(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;
        if (!$user || !$book) return '';

        $nama = $user->name;
        $judul = $book->title;
        $tglPinjam = Carbon::parse($loan->loan_date)->locale('id')->translatedFormat('d F Y');
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        return "Halo {$nama},\n"
            . "Peminjaman buku Anda telah berhasil.\n"
            . "📚 Buku: {$judul}\n"
            . "📅 Tanggal Pinjam: {$tglPinjam}\n"
            . "⏳ Batas Kembali: {$tglTempo}\n"
            . "Mohon jaga buku dengan baik. Terima kasih.\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";
    }

    /**
     * 2. Notifikasi Pengingat Otomatis (Reminder) H-1
     */
    public static function sendReminderH1(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;

        if (!$user || !$book) {
            return null;
        }

        $nama = $user->name;
        $judul = $book->title;
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        $pesan = "Halo {$nama},\n"
            . "Pengingat: Buku \"{$judul}\" yang Anda pinjam akan jatuh tempo Besok ({$tglTempo}). Jangan lupa dikembalikan ya!\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";

        return self::sendMessage($user->phone, $pesan);
    }

    /**
     * Dapatkan teks template pengingat H-1
     */
    public static function getReminderH1Text(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;
        if (!$user || !$book) return '';

        $nama = $user->name;
        $judul = $book->title;
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        return "Halo {$nama},\n"
            . "Pengingat: Buku \"{$judul}\" yang Anda pinjam akan jatuh tempo Besok ({$tglTempo}). Jangan lupa dikembalikan ya!\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";
    }

    /**
     * Notifikasi Pengingat Hari Ini (Hari-H Jatuh Tempo)
     */
    public static function sendReminderHariIni(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;

        if (!$user || !$book) {
            return null;
        }

        $nama = $user->name;
        $judul = $book->title;
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        $pesan = "Halo {$nama},\n"
            . "Pengingat: Buku \"{$judul}\" yang Anda pinjam akan jatuh tempo Hari Ini ({$tglTempo}). Jangan lupa dikembalikan ya!\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";

        return self::sendMessage($user->phone, $pesan);
    }

    /**
     * Dapatkan teks template pengingat Hari Ini
     */
    public static function getReminderHariIniText(Loan $loan)
    {
        $user = $loan->user;
        $book = $loan->book;
        if (!$user || !$book) return '';

        $nama = $user->name;
        $judul = $book->title;
        $tglTempo = Carbon::parse($loan->due_date)->locale('id')->translatedFormat('d F Y');

        return "Halo {$nama},\n"
            . "Pengingat: Buku \"{$judul}\" yang Anda pinjam akan jatuh tempo Hari Ini ({$tglTempo}). Jangan lupa dikembalikan ya!\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";
    }

    /**
     * 3. Notifikasi Keterlambatan
     */
    public static function sendOverdueWarning(Loan $loan, $daysLate = null, $denda = null)
    {
        $user = $loan->user;
        $book = $loan->book;

        if (!$user || !$book) {
            return null;
        }

        if ($daysLate === null) {
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            $daysLate = $today->greaterThan($dueDate) ? (int)$dueDate->diffInDays($today) : 1;
        }

        if ($denda === null) {
            $denda = $daysLate * 1000;
        }

        $nama = $user->name;
        $judul = $book->title;
        $nominalDenda = number_format($denda, 0, ',', '.');

        $pesan = "Halo {$nama},\n"
            . "Buku \"{$judul}\" telah melewati batas pengembalian selama {$daysLate} hari.\n"
            . "Saat ini Anda sudah dikenakan denda sebesar Rp {$nominalDenda}.\n"
            . "Mohon segera kembalikan buku ke perpustakaan untuk menghentikan perhitungan denda. Terima kasih.\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";

        return self::sendMessage($user->phone, $pesan);
    }

    /**
     * Dapatkan teks template keterlambatan
     */
    public static function getOverdueWarningText(Loan $loan, $daysLate = null, $denda = null)
    {
        $user = $loan->user;
        $book = $loan->book;
        if (!$user || !$book) return '';

        if ($daysLate === null) {
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            $daysLate = $today->greaterThan($dueDate) ? (int)$dueDate->diffInDays($today) : 1;
        }

        if ($denda === null) {
            $denda = $daysLate * 1000;
        }

        $nama = $user->name;
        $judul = $book->title;
        $nominalDenda = number_format($denda, 0, ',', '.');

        return "Halo {$nama},\n"
            . "Buku \"{$judul}\" telah melewati batas pengembalian selama {$daysLate} hari.\n"
            . "Saat ini Anda sudah dikenakan denda sebesar Rp {$nominalDenda}.\n"
            . "Mohon segera kembalikan buku ke perpustakaan untuk menghentikan perhitungan denda. Terima kasih.\n"
            . "╰┈➤ Tim Perpustakaan SDN Pengampon 3";
    }
}
