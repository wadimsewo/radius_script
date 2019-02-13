<?php

namespace App;

use Medoo\Medoo;

/**
 * Search for nearest cities
 * User: Wadim Sewostjanow
 * Date: 2019-02-13
 * Time: 13:22
 */

class NearestCities {

    private $database;
    private $city;
    private $country;
    private $distance;

    /**
     * App constructor.
     *
     * @param $city
     * @param $country
     * @param $distance
     */
    public function __construct (string $city, string $country, int $distance) {

        //connect to the database
        $this->database = new Medoo([
            'database_type' => 'mysql',
            'database_name' => 'geo',
            'server' => 'localhost',
            'username' => 'root',
            'password' => '',
            "charset" => "utf8",
        ]);

        //set variables
        $this->city = $city;
        $this->country = $country;
        $this->distance = $distance;
    }



    /**
     * Get results of nearest cities with latitude and longitude
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param $lat
     * @param $lng
     *
     * @return array|bool
     */
    private function getNearestCities (float $lat, float $lng) : array {

        // formula to calculate nearest cities
        $formula = '( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) 
                    - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( lat ) ) ) )';

        // get results by formula
        $result = $this->database
                        ->select("cities", ['city', 'distance' => Medoo::raw($formula)],
                        Medoo::raw('HAVING distance < ' . $this->distance . ' ORDER BY distance;'));

        return $result;
    }



    /**
     * Search for nearest cities
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @return array
     */
    public function search (): array {

        $results = [];

        // get lat & lng from selected city
        $cityCoordinates = $this->database->select('cities', ['city', 'lat', 'lng'],
                                            Medoo::raw("WHERE city regexp '[[:<:]]" . $this->city . "[[:>:]]'"));

        // build result array
        foreach($cityCoordinates as $coordinate) {
            foreach($this->getNearestCities($coordinate['lat'], $coordinate['lng']) as $row)
                $results[$coordinate['city']][] = $row['city'];
        }

        return $results;
    }

}