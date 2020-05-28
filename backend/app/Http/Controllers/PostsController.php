<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostLikeRequest;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Post;
use App\Repositories\PostRepository;
use App\User;
use Exception;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;

class PostsController extends Controller
{

  private PostRepository $postRepository;

  /**
   * PostsController constructor
   *
   * @param PostRepository $postRepository
   */
  public function __construct(PostRepository $postRepository)
  {
    $this->postRepository = $postRepository;
  }


  /**
   * Find and show all posts in a page
   *
   * @return ResourceCollection
   */
  public function index()
  {
    $page = Paginator::resolveCurrentPage();

    return PostResource::collection($this->postRepository->findAllPostsInPage($page));
  }

  /**
   * Find and show all user's posts in a page
   *
   * @param User $user
   * @return ResourceCollection
   */
  public function user(User $user)
  {
    $page = Paginator::resolveCurrentPage();

    return PostResource::collection($this->postRepository->findAllPostsOfUserInPage($user, $page));
  }

  public function show(Post $post)
  {
    return new PostResource($post);
  }

  /**
   * Store post in database
   *
   * @param PostStoreRequest $request
   * @return PostResource
   */
  public function store(PostStoreRequest $request)
  {
    $post = $this->postRepository->store($request->user(), $request->only([
      'title',
      'description'
    ]));

    $this->postRepository->forgetPostFromCache($post);

    return new PostResource($post);
  }

  /**
   * Find and like post
   *
   * @param PostLikeRequest $request
   * @param Post $post
   * @return Response
   */
  public function like(PostLikeRequest $request, Post $post)
  {
    $post->likes()->save($request->user());

    $this->postRepository->forgetPostFromCache($post);

    return response()->noContent();
  }

  /**
   * Find and update post
   *
   * @param Post $post
   * @param PostUpdateRequest $request
   * @return Response
   */
  public function update(Post $post, PostUpdateRequest $request)
  {
    $post->update($request->only([
      'title',
      'description'
    ]));

    $this->postRepository->forgetPostFromCache($post);

    return response()->noContent();
  }

  /**
   * Find and delete post
   *
   * @param Post $post
   * @return Response
   * @throws Exception
   */
  public function delete(Post $post)
  {
    $this->postRepository->forgetPostFromCache($post);

    $post->delete();

    return response()->noContent();
  }

}
