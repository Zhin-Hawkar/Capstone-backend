<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AiChatLog extends Model
{
    use HasFactory;
    protected $table = 'ai_chat_log';
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'prompt',
        'response',
    ];

 
}
