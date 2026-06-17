<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $connection = 'chat_db';
    
    protected $guarded = [];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
