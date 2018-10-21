<?php

namespace App\Http\Controllers\Api;

use App\Annotation\Loggable;
use App\Article;
use App\Comment;
use App\Http\Requests\Api\CreateComment;
use App\Http\Requests\Api\DeleteComment;
use App\RealWorld\Transformers\CommentTransformer;
use Illuminate\Support\Facades\Cache;

class CommentController extends ApiController
{
    /**
     * CommentController constructor.
     *
     * @param CommentTransformer $transformer
     */
    public function __construct(CommentTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('auth.api')->except('index');
        $this->middleware('auth.api:optional')->only('index');
    }

    /**
     * Get all the comments of the article given by its slug.
     *
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Article $article)
    {
        $cacheKey = 'comments:' . $article->id;
        $result   = Cache::get($cacheKey);
        if ($result === null) {
            $comments = $article->comments()->get();
            $result   = $this->respondWithTransformer($comments);
            Cache::put($cacheKey, $result);
        }


        return $result;
    }

    /**
     * Add a comment to the article given by its slug and return the comment if successful.
     *
     * @Loggable(template="Add comment")
     *
     * @param CreateComment $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateComment $request, Article $article)
    {
        $comment = $article->comments()->create([
            'body' => $request->input('comment.body'),
            'user_id' => auth()->id(),
        ]);

        return $this->respondWithTransformer($comment);
    }

    /**
     * Delete the comment given by its id.
     *
     * @Loggable(template="Delete comment")
     *
     * @param DeleteComment $request
     * @param $article
     * @param Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DeleteComment $request, $article, Comment $comment)
    {
        $comment->delete();

        return $this->respondSuccess();
    }
}
