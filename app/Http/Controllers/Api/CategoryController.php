<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('order', 'asc')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('create', Category::class);
        $validated = $request->validated();
        
        if($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/category', 'public');
        }

        $category = Category::create($validated);
 
        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
        return new CategoryResource($category);
    }

    /**
     * Reorder the category resources.
     */
    public function reOrder(Request $request)
    {
        $categoryMoves = $request->get('moves');

        foreach ($categoryMoves as $move) {
            $category = Category::findOrFail($move['id']);
            $category->order = $move['order'];
            $category->save();
        }

        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);
        $validated = $request->validated();

        if($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/category', 'public');
            if($category->image) {
                Storage::disk('public')->delete($category->image);
            }
        }

        $category->update($validated);

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Ensure the user is authorized to delete the category
        $this->authorize('delete', $category);

        $categoryId = $category->id; // Getting the id of the deleted category
        $categoryOrder = $category->order; // Getting the order of the deleted category
        $category->delete();

        // Update order for categories with order greater than the deleted category
        Category::where('order', '>', $categoryOrder)->decrement('order');

        return response()->noContent();
    }
}
