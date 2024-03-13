<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Retrieve query parameters
        $page = $request->query('page');
        $rowsPerPage = $request->query('rows_per_page');
        $searchName = $request->query('search_name');
        $searchStatus = $request->query('search_status');

        $posts = Post::when($searchName, function (Builder $query, string $searchName) {
                            $query->where('title', 'like', '%' . $searchName . '%'); // Corrected string interpolation
                        })
                        ->when($searchStatus, function (Builder $query, string $searchStatus) {
                            $query->where('status', $searchStatus);
                        })
                        ->orderBy('id', 'desc')
                        ->paginate($rowsPerPage);
        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
