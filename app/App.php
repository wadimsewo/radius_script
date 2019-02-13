<?php

namespace App;

use Medoo\Medoo;

/**
 * App Functions
 * User: Wadim Sewostjanow
 * Date: 2019-02-13
 * Time: 13:22
 */

class App {

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
    public function __construct ($city, $country, $distance) {
        $this->database = new Medoo([
            'database_type' => 'mysql',
            'database_name' => 'geo',
            'server' => 'localhost',
            'username' => 'root',
            'password' => '',
            "charset" => "utf8",
        ]);

        $this->city = $city;
        $this->country = $country;
        $this->distance = $distance;
    }



    /**
     * Search for city in the database
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     */
    private function search ($lat, $lng) {

        $result = $this->database->select(
            "cities",
            ['city', 'distance' => Medoo::raw('( 6371 * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( lat ) ) ) )')],
            Medoo::raw('HAVING distance < ' . $this->distance . ' ORDER BY distance;'));

        return $result;
    }



    /**
     * Get results
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @return array
     */
    public function getResults () {

        $results = [];
        $cityKoordinates = $this->database->select('cities', ['city', 'lat', 'lng'],
            Medoo::raw("WHERE city regexp '[[:<:]]" . $this->city . "[[:>:]]'"));

        foreach($cityKoordinates as $koordinate) {
            foreach($this->search($koordinate['lat'], $koordinate['lng']) as $row)
            $results[$koordinate['city']][] = $row['city'];
        }

        return $results;
    }

}