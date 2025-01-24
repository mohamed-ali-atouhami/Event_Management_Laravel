<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() : AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request) : JsonResponse
    {
        $validated = $request->validated(); 
        $category = Category::create($validated);
        $response = new CategoryResource($category);
        return response()->json([
            "category" => $response,
            "message" => __("Category Created successfully !!")
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category) : JsonResponse
    {
        $response = new CategoryResource($category);
        return response()->json([
            "category" => $response,
            "message" => __("Category Fetched successfully !!")
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category) : JsonResponse
    {
        $category->update($request->validated());
        $response = new CategoryResource($category);
        return response()->json([
            "category" => $response,
            "message" => __("Category Updated successfully !!")
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category) : JsonResponse  
    {
        $category->delete();
        return response()->json([
            "message" => __("Category Deleted successfully !!")
        ], 200);
    }
}
