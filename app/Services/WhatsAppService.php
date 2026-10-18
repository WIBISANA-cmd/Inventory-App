<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('FONNTE_API_KEY'); // Simpan API Key di .env
    }

    public function sendMessage($phone, $message)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62' // Kode negara Indonesia
        ]);

        return $response->json();
    }
}
