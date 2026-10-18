<?php

namespace Tests\Feature;

use App\Mail\StokMenipisNotification;
use App\Models\Bahan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StokNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stok_notification_can_be_sent(): void
    {
        Mail::fake();

        Bahan::create([
            'kode_bahan' => 'B001',
            'nama_bahan' => 'Biji Kopi Arabika',
            'tipe' => Bahan::TIPE_NON_PROCESS,
            'jenis_bahan' => 'baku',
            'kategori_bahan' => 'bar',
            'sisa_stok' => 2,
            'satuan' => 'kg',
        ]);

        $response = $this->get('/kirim-notifikasi-stok');

        $response->assertStatus(200);
        $this->assertEquals('Email notifikasi stok berhasil dikirim!', $response->getContent());

        Mail::assertSent(StokMenipisNotification::class, function ($mail) {
            return $mail->hasTo('owner@coffeeshop.com');
        });
    }

    public function test_stok_notification_when_no_low_stock(): void
    {
        Mail::fake();

        $response = $this->get('/kirim-notifikasi-stok');

        $response->assertStatus(200);
        $this->assertEquals('Tidak ada stok yang menipis atau habis.', $response->getContent());

        Mail::assertNothingSent();
    }
}

