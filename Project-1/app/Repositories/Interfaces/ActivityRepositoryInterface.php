<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface ActivityRepositoryInterface{

    public function addActivity($data);
    public function searchActivity($data);
    public function getAllActivity();
}
