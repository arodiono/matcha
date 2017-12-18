<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersTags
 * @package App\Models
 */
class UsersTags extends Model
{
    /**
     * @var string
     */
    protected $table = 'users-tags';
    /**
     * @var array
     */
    protected $fillable = [
        'tag_id',
        'user_id',
    ];
}