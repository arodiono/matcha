<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tag
 * @package App\Models
 */
class Tag extends Model
{
    /**
     * @var string
     */
    protected $table = 'tags';
    /**
     * @var array
     */
    protected $fillable = [
        'tag',
    ];

    public static function isExist($tag)
    {
        return self::where('tag', $tag)->count() === 1;
    }
	
	public function users()
	{
		return $this->belongsToMany('App\Models\User')->using('App\Pivots\TagUserPivot');
	}
}