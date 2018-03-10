<?php

namespace App\Pivots;


use Illuminate\Database\Eloquent\Relations\Pivot;

class TagUserPivot extends Pivot
{
    protected $table = 'tag_user';
    protected $fillable = ['tag_id', 'user_id'];

}