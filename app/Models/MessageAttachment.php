<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    protected $connection = 'chat_db';
    
    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
