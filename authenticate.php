<?php
namespace App;

include_once('classes/porscheauth.class.php');
include_once('classes/vehicle.model.php');

if(isset($_POST)) {
    $user = $_POST["email"];
    $pass = $_POST["password"];

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

    echo json_encode($vehicle);

    // $cars = $porscheAuth->getCar("https://connect-portal.porsche.com/core/api/v3/gb/en_GB/vehicles", 0);

    // $carList = array();

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

    // echo json_encode($carList);
}
else {
    die();
}