<?php

use App\Mail\ResetPassword;
use App\Services\MukeeyMailService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send:pin', function () {
    $pin = '12345';
    // Logic to send email with the PIN
    // Mail::to("mualiyuoox@gmail.com")->send(new ResetPassword($pin));

    $mailData = [
        'pin' => "12345",
    ];

    MukeeyMailService::send("mualiyuoox@gmail.com", "Reset Password", $mailData, "emails.password");

    $this->comment("Email sent with PIN: {$pin}");
})->purpose('Send an email with a PIN of 12345');
