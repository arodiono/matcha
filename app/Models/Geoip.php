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
        /** @var /Illuminate\Database\Eloquent\Model $this */
        if (array_key_exists('user', $_SESSION)) {
            $data = $this::select('lat', 'lon')
                ->where('user_id', '=', $_SESSION['user'])
                ->get()
                ->first();
            if ($data) {
                $currentCoords = $data->toArray();
            } else {
                echo 'Error';
                die();
            }
            $minLon = $currentCoords['lon'] - $radius / abs(cos(deg2rad($currentCoords['lat'])) * $this->_latConst);
            $maxLon = $currentCoords['lon'] + $radius / abs(cos(deg2rad($currentCoords['lat'])) * $this->_latConst);
            $minLat = $currentCoords['lon'] - ($radius / $this->_latConst);
            $maxLat = $currentCoords['lat'] + ($radius / $this->_latConst);

            $data = $this->getConnection()
                ->select('select *, users.*, 6373*2*ASIN(SQRT(POWER(SIN((' . $currentCoords['lat'] . '-abs(lat))* pi()/180 / 2),2) + COS(' . $currentCoords['lat'] . '*pi()/180 )*COS(abs(lat)*pi()/180)*POWER(SIN((' . $currentCoords['lon'] . ' - lon)*pi()/180 / 2), 2))) as distance FROM geoip left join users on users.id = geoip.user_id having distance < ' . $radius . ' and lon between ' . $minLon . ' and ' . $maxLon . ' and lat between ' . $minLat . ' and ' . $maxLat);
            $res = (array)$data;
            return $res;
        }
    }

    public function setCoordinates($data)
    {
        if (array_key_exists('user', $_SESSION)) {
            if (!$this::where('user_id', $_SESSION['user'])
                ->update(['lat' => $data['latitude'], 'lon' => $data['longitude']])) {

                $this->user_id = $_SESSION['user'];
                $this->lat = $data['latitude'];
                $this->lon = $data['longitude'];
                $this->save();
            }
        }
    }
}