<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 5/23/18
 * Time: 10:05 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Fake extends Model
{
    protected $table = 'fake';

    protected $fillable = [
        "reporter",
        "fake"
    ];
}