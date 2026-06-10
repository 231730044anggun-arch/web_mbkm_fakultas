<?php

namespace App\Mail;

use App\Models\PembimbingLapangan;
use App\Models\PengajuanMagang;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PembimbingLapanganAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PembimbingLapangan $pembimbing,
        public PengajuanMagang $pengajuan,
        public string $temporaryPassword
    ) {}

    public function build()
    {
        return $this->subject('Informasi Akun Pembimbing Lapangan Magang')
            ->view('emails.pembimbing-akun');
    }
}