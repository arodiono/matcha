<?php
/**
 * Created by PhpStorm.
 * User: oklymeno
 * Date: 3/19/18
 * Time: 3:12 AM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Connections extends Model
{
    protected $table = 'connections';

    public function setUserConnection(int $connectionId)
    {
        $this::select('*')->get();
//        (
//            ['connection_id' => $connectionId, 'user_id' => 1]
//        );
    }

}