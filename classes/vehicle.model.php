<?php
namespace App;

class Vehicle {
    public string $vin;
    public string $modelDescription;
    public string $modelYear;
    public string $modelImageUrl;
    
    public string $batteryUnit;
    public float $batteryPercent;
    public float $batteryRange;

    public string $mileageUnit;
    public float $mileage;

    public function __construct(string $vin, string $modelDescription, string $modelYear, string $modelImage)
    {
        $this->vin = $vin;
        $this->modelDescription = $modelDescription;
        $this->modelYear = $modelYear;
        $this->modelImageUrl = $modelImage;
    }

    public function setBattery(float $percent, float $range, string $unit)
    {
        $this->batteryPercent = $percent;
        $this->batteryRange = $range;
        $this->batteryUnit = $unit;
    }

    public function setMileage(float $distance, string $unit)
    {
        $this->mileage = $distance;
        $this->mileageUnit = $unit;
    }
}