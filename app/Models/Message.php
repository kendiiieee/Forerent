<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    // Relationship to the Sender
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }

    // Relationship to the Receiver
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'user_id');
    }
}
