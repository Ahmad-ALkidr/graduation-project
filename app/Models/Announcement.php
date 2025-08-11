<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'file_path',
        'file_type',
        'telegram_message_id', // <-- تم إضافة الفاصلة هنا
    ];
}
