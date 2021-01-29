<?php
namespace App;

include_once('classes/porscheauth.class.php');
include_once('classes/vehicle.model.php');

$user = "YOUR USER";
$pass = "YOUR PASSWORD";

$tempSession = new \GuzzleHttp\Client(['cookies' => true]);

$porscheAuth = new PorscheAuth($user, $pass, $tempSession, "", "", "");



$car = $porscheAuth->getCar("https://connect-portal.porsche.com/core/api/v3/gb/en_GB/vehicles", 0);

$vehicle = new Vehicle(
    $car->vin,
    $car->modelDescription,
    $car->modelYear,
    $car->pictures[0]->url
);

// ICE cars do not have this?
// At least not Macan
// Donate me one of each online connected Porsche
// so I can test. (:
if ($car->carControlData != null)
{
    $vehicle->setBattery(
        $car->carControlData->batteryLevel->value,
        $car->carControlData->remainingRanges->electricalRange->distance->value,
        $car->carControlData->remainingRanges->electricalRange->distance->unit
    );

    $vehicle->setMileage(
        $car->carControlData->mileage->value,
        $car->carControlData->mileage->unit
    );
}

// var_dump($vehicle);
// var_dump($car);

echo json_encode($vehicle);

//echo "Cars: " . count($cars) ."\n";

//$carList = array();

// foreach ($cars as $car)
// {
//     //echo $car->modelDescription."\n";

//     $vehicle = new Vehicle(
//         $car->vin,
//         $car->modelDescription,
//         $car->modelYear,
//         $car->pictures[0]->url
//     );

//     $carList[] = $vehicle;
// }

//var_dump($carList);