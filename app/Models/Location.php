<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'geoip';
    private $_latConst = 111; //value of 1 Lat degree in kilometers

    public function getUsers(int $radius)
    {
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

    /**
     * @param array $source ['lat' => $lat, 'lon' => $lon]
     * @param array $destination ['lat' => $lat, 'lon' => $lon]
     * @return int
     */
    public function getDistance(array $source, array $destination) : int
    {
        if (array_key_exists('lat', $source) &&
            array_key_exists('lon', $source) &&
            array_key_exists('lat', $destination) &&
            array_key_exists('lon', $destination)) {
            $lat1 = deg2rad($source['lat']);
            $lon1 = deg2rad($source['lon']);
            $lat2 = deg2rad($destination['lat']);
            $lon2 = deg2rad($destination['lon']);
            $haversine = pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon2 - $lon1) / 2), 2);
            return 6373 * 2 * atan2(sqrt($haversine), sqrt(1 - $haversine));
        }
        return -1;
    }

    public function getCurrentCoords() : array
    {
        $srcData = $this::select('lat', 'lon')
            ->where('user_id', '=', $_SESSION['user'])
            ->get()
            ->first();
        if ($srcData) {
            return $srcData->toArray();
        }
        return [];
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