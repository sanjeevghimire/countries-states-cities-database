<?php
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
ini_set('memory_limit', '-1');
header('Content-type: text/plain');
date_default_timezone_set('Asia/Kolkata');

$servername = "127.0.0.1";
$username = "root";
$password = "mysql";
$dbname = "world"; // Database Name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$i = 0;
$j = 0;
$k = 0;
$l = 0;
$m = 0;

$countriesArray = array();
$statesArray = array();
$citiesArray = array();
$stateCityArray = array();
$countryStateArray = array();
$countryStateCityArray = array();
$stateNamesArray = array();
$cityNamesArray = array();

// Fetching All Countries
$sql = "SELECT * FROM countries ORDER BY name";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Pushing it into Fresh Array
        $countriesArray[$m]['id'] = (int)$row['id'];
        $countriesArray[$m]['name'] = $row['name'];
        $countriesArray[$m]['iso3'] = $row['iso3'];
        $countriesArray[$m]['iso2'] = $row['iso2'];
        $countriesArray[$m]['phone_code'] = $row['phonecode'];
        $countriesArray[$m]['capital'] = $row['capital'];
        $countriesArray[$m]['currency'] = $row['currency'];

        $m++;
    }
}

// Fetching All States
// $sql = "SELECT * FROM states ORDER BY NAME";
// $result = $conn->query($sql);
// if ($result->num_rows > 0) {
//     while($state = $result->fetch_assoc()) {

//         // Only States Array
//         $statesArray[$i]['id'] = $state['id'];
//         $statesArray[$i]['name'] = $state['name'];
//         $statesArray[$i]['country_id'] = $state['country_id'];

//         $i++;

//     }
// }

// Fetching All States
// $sql = "SELECT * FROM cities ORDER BY NAME";
// $result = $conn->query($sql);
// if ($result->num_rows > 0) {
//     while($city = $result->fetch_assoc()) {

//         // Only Cities Array
//         $citiesArray[$j]['id'] = $city['id'];
//         $citiesArray[$j]['name'] = $city['name'];
//         $citiesArray[$j]['state_id'] = $city['state_id'];
//         $citiesArray[$j]['country_id'] = $city['country_id'];

//         $j++;
//     }
// }

// Fetching States & Cities Array
// foreach($statesArray as $state) :
//     $stateCityArray[$k]['id'] = $state['id'];
//     $stateCityArray[$k]['name'] = $state['name'];
//     $stateCityArray[$k]['country_id'] = $state['country_id'];

//     $countryId = $state['country_id'];
//     $stateId = $state['id'];

//     $sql = "SELECT * FROM cities WHERE country_id=$countryId AND state_id=$stateId ORDER BY name";
//     $result = $conn->query($sql);

//     if ($result->num_rows > 0) {
//         while($city = $result->fetch_assoc()) {
//             array_push($cityNamesArray,$city['name']);
//         }
//     }

//     $stateCityArray[$k]['cities'] = $cityNamesArray;
//     $k++;
// endforeach;


// Validating Country Names
foreach($countriesArray as $country) {

    $countryId = (int)$country['id'];
    $countryStateCityArray[$k]['id'] = $countryId;
    $countryStateCityArray[$k]['name'] = utf8_encode($country['name']);
    $countryStateCityArray[$k]['iso3'] = $country['iso3'];
    $countryStateCityArray[$k]['iso2'] = $country['iso2'];
    $countryStateCityArray[$k]['phone_code'] = $country['phone_code'];
    $countryStateCityArray[$k]['capital'] = utf8_encode($country['capital']);
    $countryStateCityArray[$k]['currency'] = $country['currency'];

    // Fetching All States Based on Country
    $sql = "SELECT * FROM states WHERE country_id=$countryId ORDER BY NAME";
    $stateResult = $conn->query($sql);

    if ($stateResult->num_rows > 0) {
        $stateNamesArray = array();
        while($state = $stateResult->fetch_assoc()) {

            // Only States Array
            $stateId = (int)$state['id'];
            $stateName = utf8_encode($state['name']);
            $statesArray[$i]['id'] = $stateId;
            $statesArray[$i]['name'] = utf8_encode($state['name']);
            $statesArray[$i]['country_id'] = $countryId;

            // For Country State Array
            array_push($stateNamesArray,$stateName);

            // Fetching All States Based on Country & State
            $sql = "SELECT * FROM cities WHERE country_id=$countryId AND state_id=$stateId ORDER BY NAME";
            $cityResult = $conn->query($sql);

            if ($cityResult->num_rows > 0) {
                $cityNamesArray = array();
                while($city = $cityResult->fetch_assoc()) {

                    // Only Cities Array
                    $cityId = $city['id'];
                    $citiesArray[$j]['id'] = $cityId;
                    $citiesArray[$j]['name'] = utf8_encode($city['name']);
                    $citiesArray[$j]['state_id'] = $stateId;
                    $citiesArray[$j]['country_id'] = (int)$countryId;

                    // For State City Array
                    array_push($cityNamesArray,utf8_encode($city['name']));

                    $j++;
                }
            }

            // Completing CountryStateCity Array State by State
            $countryStateCityArray[$k]['states'][$stateName] = $cityNamesArray;

            // Completing StateCity Array
            $stateCityArray[$i]['id'] = $stateId;
            $stateCityArray[$i]['name'] = $stateName;
            $stateCityArray[$i]['country_id'] = $countryId;
            $stateCityArray[$i]['cities'] = $cityNamesArray;

            $i++;
        }
    }

    // Completing Country States Array
    $countryStateArray[$k]['id'] = (int)$row['id'];
    $countryStateArray[$k]['name'] = utf8_encode($row['name']);
    $countryStateArray[$k]['iso3'] = $row['iso3'];
    $countryStateArray[$k]['iso2'] = $row['iso2'];
    $countryStateArray[$k]['phone_code'] = $row['phonecode'];
    $countryStateArray[$k]['capital'] = utf8_encode($row['capital']);
    $countryStateArray[$k]['currency'] = $row['currency'];
    $countryStateArray[$k]['states'] = $stateNamesArray;

    $k++;

}

echo 'Total Countries Count : '.count($countriesArray).PHP_EOL;
echo 'Total States Count : '.count($statesArray).PHP_EOL;
echo 'Total Cities Count : '.count($citiesArray).PHP_EOL;

// print_r($countriesArray);
$fp = fopen('data/countries.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($countriesArray, JSON_PRETTY_PRINT));
fclose($fp);

// print_r($statesArray);
$fp = fopen('data/states.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($statesArray, JSON_PRETTY_PRINT));
fclose($fp);

// print_r($citiesArray);
$fp = fopen('data/cities.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($citiesArray, JSON_PRETTY_PRINT));
fclose($fp);

// print_r($stateCityArray);
$fp = fopen('data/states+cities.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($stateCityArray, JSON_PRETTY_PRINT));
fclose($fp);

// print_r($countryStateArray);
$fp = fopen('data/countries+states.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($countryStateArray, JSON_PRETTY_PRINT));
fclose($fp);

// print_r($countryStateCityArray);
$fp = fopen('data/countries+states+cities.json', 'w'); // Putting Array to JSON
fwrite($fp, json_encode($countryStateCityArray, JSON_PRETTY_PRINT));
fclose($fp);


?>