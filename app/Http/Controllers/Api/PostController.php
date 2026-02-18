<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->active()->paginate(20);

        return (new PostResource(true, 'Posts retrieved', $posts))
            ->response()
            ->setStatusCode(200);
    }

    public function create()
    {
        return (new PostResource(true, 'posts.create', null))
            ->response()
            ->setStatusCode(200);
    }

    public function store(PostRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = Auth::id();

        $post = Post::create($validated);

        return (new PostResource(true, 'Create Post Data Success', $post))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Post $post)
    {
        if ($post->is_draft || ($post->published_at !== null && $post->published_at->isFuture())) {
            abort(404);
        }

        return (new PostResource(true, 'Post retrieved', $post->load('user')))
            ->response()
            ->setStatusCode(200);
    }

    public function edit(Post $post)
    {
        Gate::authorize('update', $post);

        return (new PostResource(true, 'posts.edit', null))
            ->response()
            ->setStatusCode(200);
    }

    public function update(PostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $post->update($request->validated());

        return (new PostResource(true, 'Post updated', $post->load('user')))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return (new PostResource(true, 'Post deleted', null))
            ->response()
            ->setStatusCode(200);
    }
}
