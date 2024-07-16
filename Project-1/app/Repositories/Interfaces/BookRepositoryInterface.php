<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface BookRepositoryInterface{

    public function store_Admin($data);
    public function editAdmin($request,$id);
    public function showStaticTrip($id);
    public function index();
    public function checkStaticTrip($request,$id);
    public function bookStaticTrip($request);
    public function editBook($request,$id);
    public function deleteBook($id);

    public function tripCancellation($id);

    public function getDetailsStaticTrip($id);

    public function getTripAdminTrips();
    public function getTripAdminTripDetails($id);

    public function searchTrip($request);
}
