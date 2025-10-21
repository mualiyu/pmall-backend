<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; // 👈 important

class Vendor extends Model
{
    use HasFactory, Notifiable; // 👈 add Notifiable here

    protected $fillable = [
        'name',
        'email',
        'phone',
        // add other vendor fields
    ];
}
