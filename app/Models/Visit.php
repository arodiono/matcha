<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{

    protected $table = 'visits';

    protected $fillable = [
        "whom_id",
        "who_id"
    ];

    public function who()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function whom()
    {
        return $this->belongsTo('App\Models\User');
    }
}