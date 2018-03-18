<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{

    protected $table = 'likes';

    protected $fillable = [
        "whom_id",
        "who_id"
    ];


    public function who()
    {
        return $this->hasOne('App\Model\User', 'who_id');
    }

    public function whom()
    {
        return $this->hasOne('App\Model\User', 'whom_id');
    }
}