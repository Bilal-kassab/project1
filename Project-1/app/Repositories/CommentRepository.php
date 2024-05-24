<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\Place;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Exception;

class CommentRepository implements CommentRepositoryInterface
{
    public function setComment($data)
    {
        $comment=Comment::create([
            'user_id'=>auth()->id(),
            'place_id'=>$data['place_id'],
            'comment'=>$data['comment'],
        ]);
        return Comment::with('user:id,name,image')->where('id',$comment->id)->first();
    }

    public function showAllPlaceComment($id)
    {
        try{
            $place=Place::whereHas('comments')->with('comments.user:id,name,image')->where('id',$id)->first();
        }catch(Exception $exception)
        {
            return 1;
            // return response()->json([
            //     'message'=>$exception->getMessage()
            // ]);
        }

        return $place;
    }
}
