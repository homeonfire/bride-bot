<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = [
        'title', 'message', 'photo_file_id', 
        'status', 'sent_count', 'error_count'
    ];
}