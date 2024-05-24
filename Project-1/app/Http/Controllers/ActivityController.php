<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\AddActivityRequest;
use App\Http\Requests\Activity\SearchActivityRequest;
use App\Repositories\ActivityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    private $activityrepository;

    public function __construct(ActivityRepository $activityrepository)
    {
        $this->activityrepository = $activityrepository;
    }
    public function addActivity(AddActivityRequest $request):JsonResponse
    {
        $data=[
            'name'=>$request->name,
        ];
        $activity=$this->activityrepository->addActivity($data);
        return response()->json([
            'data'=>$activity
        ],200);
    }

    public function searchActivity(SearchActivityRequest $request):JsonResponse
    {
        $data=[
            'name'=>$request->name,
        ];
        $activities=$this->activityrepository->searchActivity($data);
        return response()->json([
            'data'=>$activities
        ],200);
    }
    public function getAllActivity():JsonResponse
    {
        $activities=$this->activityrepository->getAllActivity();

        return response()->json([
            'data'=>$activities
        ]);
    }
}
