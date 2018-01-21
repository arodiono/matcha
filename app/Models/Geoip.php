<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use app\Models\User;
use app\Auth\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Config\Definition\Exception\Exception;

class Geoip extends Model
{
    protected $table = 'geoip';
    protected $container2;
    protected $logger;

    private $_latConst = 111; //value of 1 Lat degree in kilometers

    /*
     * Database structure:
     * lat
     * lon
     * user_id
     *
     */

    public function getUsers(int $radius)
    {
        $data = $this::select('latitude', 'longitude')
            ->where('user_id', '=', $_SESSION['user'])
            ->get()
            ->first();
        $currentCoords = $data->toArray();
        $minLon = $currentCoords['longitude'] - $radius / abs(cos(deg2rad($currentCoords['latitude'])) * $this->_latConst);
        $maxLon = $currentCoords['longitude'] + $radius / abs(cos(deg2rad($currentCoords['latitude'])) * $this->_latConst);
        $minLat = $currentCoords['longitude'] - ($radius / $this->_latConst);
        $maxLat = $currentCoords['latitude'] + ($radius / $this->_latConst);

        $data = $this->getConnection()
            ->select('select *, users.*, 6373*2*ASIN(SQRT(POWER(SIN((' . $currentCoords['latitude'] . '-abs(latitude))* pi()/180 / 2),2) + COS(' . $currentCoords['latitude'] . '*pi()/180 )*COS(abs(latitude)*pi()/180)*POWER(SIN((' . $currentCoords['longitude'] . ' - longitude)*pi()/180 / 2), 2))) as distance FROM geoip left join users on users.id = geoip.user_id having distance < ' . $radius . ' and longitude between ' . $minLon . ' and ' . $maxLon . ' and latitude between ' . $minLat . ' and ' . $maxLat);
        $res = (array)$data;
        die();
        //TODO add JSON to return
    }

    public function setCoordinates($data=['latitude' => 0, 'longitude' => 0])
    {
        $this::updateOrCreate(
            ['user_id' => $_SESSION['user']],
                [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]
        );
    }
}