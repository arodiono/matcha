<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Photo
 * @package App\Models
 */
class Photo extends Model
{
    /**
     * @var string
     */
    protected $table = 'photos';
    /**
     * @var array
     */
    protected $fillable = [
        'photo',
        'user_id'
    ];


}