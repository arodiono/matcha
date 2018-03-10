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


}