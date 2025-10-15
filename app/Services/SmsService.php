<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $basicToken;
    protected $senderName;
    protected $url;

    public function __construct()
    {
        $this->basicToken = config('services.nimba.basic_token');
        $this->senderName = config('services.nimba.sender');
        $this->url = config('services.nimba.url');
    }

    public function sendMessage(string $phone, string $message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$this->basicToken,
            'Accept'        => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->url, [
            'sender_name' => $this->senderName,
            'to' => [$phone],
            'message' => $message,
        ]);

        if ($response->status() != 201) {
            throw new \Exception("Erreur d'envoi du message: ".json_encode($response->json()));
        }

        return $response->json();
    }

    public function sendMessageToMany(array $phones, string $message)
    {
        $recipients = array_filter(array_unique($phones));

        $response = Http::withHeaders([
            'Authorization' => 'Basic '.$this->basicToken,
            'Accept'        => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->url, [
            'sender_name' => $this->senderName,
            'to' => $recipients,
            'message' => $message,
        ]);

        if ($response->status() !== 201) {
            throw new \Exception("Erreur d'envoi du message: ".json_encode($response->json()));
        }

        return $response->json();
    }
}

