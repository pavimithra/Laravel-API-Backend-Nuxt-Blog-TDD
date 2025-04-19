<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostWithContentResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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
        $this->authorize('create', Post::class);
        $validated = $request->validated();
        
        if($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/posts', 'public');
        }

        $validated['user_id'] = $request->user()->id; // Assign the authenticated user's ID

        $validated['status'] = "draft";
    
        $post = Post::create($validated);
 
        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);
        return new PostWithContentResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        $validated = $request->validated();

        if($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/posts', 'public');
            if($post->image) {
                Storage::disk('public')->delete($post->image);
            }
        }

        $post->update($validated);

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        
        $post->delete();
        return response()->noContent();
    }

    public function getCategories()
    {
        $categories = Category::pluck('name', 'id');
        return response()->json($categories);
    }

    public function performAction(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'actionType' => 'required|in:publish,delete',
            'selectedPosts' => 'required|array',
        ]);

        $actionType = $request->input('actionType');
        $selectedPosts = $request->input('selectedPosts');

        // Perform operation based on the actionType
        if ($actionType === 'publish') {
            // Update status to published for selected posts
            Post::whereIn('id', $selectedPosts)->update(['status' => 'published']);
            return response()->json(['message' => 'Selected posts published successfully']);
        } elseif ($actionType === 'delete') {
            // Delete selected posts
            Post::whereIn('id', $selectedPosts)->delete();
            return response()->json(['message' => 'Selected posts deleted successfully']);
        }

        // If actionType is neither publish nor delete
        return response()->json(['error' => 'Invalid operation type'], 400);
    }
}
