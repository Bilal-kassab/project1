<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

Interface CommentRepositoryInterface{

    public function setComment($data);
    public function showAllPlaceComment($id);

}
