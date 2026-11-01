<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Cron Job Otomatis: Cek Pengingat Jatuh Tempo H-1 & Keterlambatan setiap pukul 08.00 pagi
Schedule::command('wa:send-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->description('Kirim Notifikasi WhatsApp Otomatis (H-1 & Keterlambatan) ke Anggota Perpustakaan SDN Pengampon 3');
