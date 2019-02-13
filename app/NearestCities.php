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
    private $show = [];
    private $distance = 10;
    private $precision = 0;
    private $distanceUnit = 6371;

    /**
     * App constructor.
     *
     * @param array $options
     */
    public function __construct ($options = NULL) {
        // connect to the database
        $this->database = new Medoo(Config::DATABASE_CONFIG);
        // set options
        $this->setOptions($options);
    }



    /**
     * Set city
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param string $city
     *
     * @return $this
     */
    public function city (string $city) {

        $this->city = $city;
        return $this;
    }



    /**
     * Set country
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param string $country
     *
     * @return $this
     */
    public function country (string $country) {

        $this->country = $country;
        return $this;
    }



    /**
     * Set distance (10km is default)
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param int $distance
     *
     * @return $this
     */
    public function distance (int $distance) {

        $this->distance = $distance;
        return $this;
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
        $formula = '( ' . $this->distanceUnit . ' * acos( cos( radians(' . $lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) 
                    - radians(' . $lng . ') ) + sin( radians(' . $lat . ') ) * sin( radians( lat ) ) ) )';

        // get results by formula
        $result = $this->database
            ->select("cities", ['city', 'lat', 'lng', 'distance' => Medoo::raw($formula)],
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

        // get lat & lng from selected city
        $cityCoordinates = $this->database->select('cities', ['city', 'lat', 'lng'],
            Medoo::raw("WHERE city regexp '[[:<:]]" . $this->city . "[[:>:]]'"));

        try {
            $results = $this->buildResult($cityCoordinates);
            return $results;
        } catch (\Exception $e) {
            echo $e;
        }
    }



    /**
     * Check if city & country is set
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @return bool
     */
    private function dataIsSet () {

        return $this->city && $this->country;
    }



    /**
     * Build the result array
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param array $cityCoordinates
     *
     * @return array
     * @throws \Exception
     */
    private function buildResult (array $cityCoordinates) {

        $results = [];

        // build result array
        if ($this->dataIsSet()) {
            foreach($cityCoordinates as $coordinate) {
                foreach($this->getNearestCities($coordinate['lat'], $coordinate['lng']) as $row)
                    if ($coordinate['city'] != $row['city']) {
                        if (empty($this->show)) {
                            $results[$coordinate['city']][] = $row['city'];
                        } else {
                            $showList = [];
                            foreach($this->show as $show) {
                                if ($show === 'distance') {
                                    $row[$show] = round($row[$show], $this->precision);
                                }
                                $showList[] = $row[$show];
                            }
                            $results[$coordinate['city']][] = $showList;
                        }
                    }
            }
            return $results;
        } else {
            throw new \Exception('Important data is missing (city or country)');
        }
    }



    /**
     * Set options
     *
     * User: Wadim Sewostjanow
     * Date: 2019-02-13
     *
     * @param array $options
     */
    private function setOptions ($options) {

        if ($options) {
            if (isset($options['show']) && is_array($options['show'])) {
                foreach($options['show'] as $show)
                    $this->show[] = $show;
            }
            if (isset($options['distancePrecision']) && is_numeric($options['distancePrecision'])) {
                $this->precision = $options['distancePrecision'];
            }
            if (isset($options['miles']) && $options['miles'] == true) {
                $this->distanceUnit = 3959;
            }
        }
    }

}