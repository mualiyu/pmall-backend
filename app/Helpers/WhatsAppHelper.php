<?php

namespace App\Helpers;

use GuzzleHttp\Client;

class WhatsAppHelper
{
    public static function sendWhatsappNotification($phone, $message)
    {
        $url = 'https://graph.facebook.com/v22.0/830657196803476/messages';
        $token = 'EAAVaW8QBq90BPt0lvcIdVZCyNMM8EcMjmFOKxTijuHg6uxLWHHLd0cOGWhiGjxWeJwVkAhqzNxeh0TeccIRzZCCPHb8M8WyJe5lNqypmI0fSaau5dJOxTtcxGIl0E8rb5qsy3scI6VnNY8FsFkKZAXHnuYp4mev6qYxYd3ZA5mAgBlW8D5WzWm8ob4InRpddegZDZD';

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => 'hello_world',
                'language' => ['code' => 'en_US']
            ],
        ];

        $client = new Client();
        $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }
}
