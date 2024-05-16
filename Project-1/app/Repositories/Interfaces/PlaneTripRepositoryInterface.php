<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface PlaneTripRepositoryInterface{

    public function addTrip($data);
    public function getAllTripForCountry($data);
}
