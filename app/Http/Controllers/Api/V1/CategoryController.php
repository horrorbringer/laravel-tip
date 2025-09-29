<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CateogryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
     /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Get list of categories",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *     )
     * )
     */
    public function index()
    {
        // abort_if(! auth()->user()->tokenCan('categories-list'), 403);

        return CategoryResource::collection((cache()->rememberForever('categories', function () {
            return Category::all();
        })));
    }

    public function show(Category $category)
    {
        // abort_if(! auth()->user()->tokenCan('categories-show'), 403);

        return new CategoryResource($category);
    }

    public function store(CateogryRequest $request)
    {
        $data = $request->validated();

         if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = Str::uuid() . '.' . $file->extension();
            $filename = Storage::disk('public')->putFileAs('categories', $file, $name);
            $data['photo'] = $filename;
        }

        $category = Category::create($data);

        return new CategoryResource($category);
    }

    public function update(Category $category,CateogryRequest $request)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        // return response(null, Response::HTTP_NO_CONTENT);
        return response()->noContent();
    }
}
