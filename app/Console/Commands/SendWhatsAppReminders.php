<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReminders extends Command
{
    /**
     * Nama dan signature console command.
     */
    protected $signature = 'wa:send-reminders {--type=all : all, reminder, atau overdue}';

    /**
     * Deskripsi console command.
     */
    protected $description = 'Kirim notifikasi WhatsApp otomatis untuk pengingat jatuh tempo H-1 dan keterlambatan peminjaman';

    /**
     * Eksekusi console command.
     */
    public function handle()
    {
        $this->info("Memulai proses pengecekan Notifikasi WhatsApp Otomatis...");
        Log::info("[Cron Job WA] Memulai proses pengecekan peminjaman...");

        $type = $this->option('type');
        $today = Carbon::now()->startOfDay();
        $tomorrowDate = Carbon::tomorrow()->toDateString();

        // Ambil semua pinjaman berstatus aktif ('dipinjam')
        $activeLoans = Loan::with(['user', 'book'])
            ->where('status', 'dipinjam')
            ->get();

        $this->info("Ditemukan {$activeLoans->count()} transaksi peminjaman aktif.");

        $countReminder = 0;
        $countOverdue = 0;
        $countSkipped = 0;

        foreach ($activeLoans as $loan) {
            $user = $loan->user;
            $book = $loan->book;

            if (!$user || !$book) {
                continue;
            }

            if (empty($user->phone)) {
                $this->warn("Dilewati: Anggota {$user->name} belum memiliki nomor WhatsApp.");
                $countSkipped++;
                continue;
            }

            $dueDate = Carbon::parse($loan->due_date)->startOfDay();

            // 1. Cek Pengingat H-1 (Jatuh tempo besok)
            if (($type === 'all' || $type === 'reminder') && $dueDate->toDateString() === $tomorrowDate) {
                $this->line("📢 Mengirim Pengingat H-1 ke {$user->name} ({$user->phone}) untuk buku: {$book->title}");
                $res = WhatsAppService::sendReminderH1($loan);
                if ($res && ($res['success'] ?? false)) {
                    $countReminder++;
                }
            }

            // 2. Cek Pengingat Hari-H (Jatuh tempo hari ini)
            if (($type === 'all' || $type === 'reminder') && $dueDate->toDateString() === $today->toDateString()) {
                $this->line("📢 Mengirim Pengingat Hari-H ke {$user->name} ({$user->phone}) untuk buku: {$book->title}");
                $res = WhatsAppService::sendReminderHariIni($loan);
                if ($res && ($res['success'] ?? false)) {
                    $countReminder++;
                }
            }

            // 2. Cek Keterlambatan (Lewat dari jatuh tempo)
            if (($type === 'all' || $type === 'overdue') && $today->greaterThan($dueDate)) {
                $daysLate = (int)$dueDate->diffInDays($today);
                if ($daysLate > 0) {
                    $denda = $daysLate * 1000;
                    $this->line("⚠️ Mengirim Peringatan Keterlambatan ({$daysLate} hari, Denda Rp " . number_format($denda, 0, ',', '.') . ") ke {$user->name} ({$user->phone})");
                    $res = WhatsAppService::sendOverdueWarning($loan, $daysLate, $denda);
                    if ($res && ($res['success'] ?? false)) {
                        $countOverdue++;
                    }
                }
            }
        }

        $summary = "Selesai: {$countReminder} pengingat H-1 terkirim, {$countOverdue} peringatan keterlambatan terkirim, {$countSkipped} tanpa nomor WA.";
        $this->info($summary);
        Log::info("[Cron Job WA] " . $summary);

        return Command::SUCCESS;
    }
}
