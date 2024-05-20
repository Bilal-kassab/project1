<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface BookRepositoryInterface{

    public function store_Admin($data);
    public function editAdmin($request,$id);

    public function showStaticTrip($id);

}
