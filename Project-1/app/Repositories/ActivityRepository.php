<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Place;
use App\Repositories\Interfaces\ActivityRepositoryInterface;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class ActivityRepository implements ActivityRepositoryInterface
{

    public function addActivity($data)
    {
        $activity=Activity::create(['name'=>$data['name']]);
        return $activity;
    }

    public function searchActivity($data)
    {
        $activities=Activity::where('name','like','%'.$data['name'].'%')->get();
        return $activities;
    }
    public function getAllActivity()
    {
        $activities=Activity::get();
        return $activities;
    }
}
