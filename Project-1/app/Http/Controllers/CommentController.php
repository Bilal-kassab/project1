<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\SetCommentRequest;
use App\Repositories\CommentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    private $commentrepository;

    public function __construct(CommentRepository $commentrepository)
    {
        $this->commentrepository = $commentrepository;
    }
    public function setComment(SetCommentRequest $request):JsonResponse
    {
        $data=[
            'place_id'=>$request->place_id,
            'comment'=>$request->comment
        ];
       $comment=$this->commentrepository->setComment($data);

       return response()->json([
        'data'=>$comment
       ],200);
    }

    public function showAllPlaceComment($id):JsonResponse
    {
        $comments=$this->commentrepository->showAllPlaceComment($id);
        if($comments===1)
        {
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }

        return response()->json([
            'data'=>$comments
        ],200);
    }
}
