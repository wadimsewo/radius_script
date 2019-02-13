<?php

use App\NearestCities;

require 'vendor/autoload.php';

$loader = new Nette\Loaders\RobotLoader;
$loader->addDirectory(__DIR__ . '/app');
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->register();

?>

    <form action="/" method="GET">
        <input type="text" name="city" placeholder="Stadt" value="<?php if(isset($_GET['city'])) echo $_GET['city']; ?>">
        <select name="country">
            <option value="DE" selected>DE</option>
            <option value="AT">AT</option>
            <option value="CH">CH</option>
        </select>
        <select name="distance">
            <option value="5" selected>5KM</option>
            <option value="10">10KM</option>
            <option value="25">20KM</option>
            <option value="50">50KM</option>
            <option value="100">100KM</option>
            <option value="200">200KM</option>
        </select>
        <button type="submit">Absenden</button>
    </form>

<?php

if(isset($_GET['city']) && isset($_GET['country'])) {

    $city = $_GET['city'];
    $country = $_GET['country'];
    $distance = $_GET['distance'];

    $nearestCities = new NearestCities();
    echo "<pre>";
    var_dump($nearestCities->city($city)->country($country)->distance($distance)->search());
}

?>