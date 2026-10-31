<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;


/**
 * @group Manajemen Kategori
 *
 * Endpoint untuk mengelola master data kategori.
 */
class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return CategoryResource::collection($this->categoryService->getAllCategories());
    }

/**
     * Buat Kategori Baru
     *
     * @authenticated
     * @bodyParam nama_kategori string required Nama kategori unik. Example: Teknologi
     */

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return response()->json(['message' => 'Kategori dibuat', 'data' => new CategoryResource($category)], 201);
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

/**
     * Ubah Kategori
     *
     * @authenticated
     * @urlParam category integer required ID kategori. Example: 1
     * @bodyParam nama_kategori string required Nama kategori baru. Example: Sains & Teknologi
     */

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $updated = $this->categoryService->updateCategory($category, $request->validated());
        return response()->json(['message' => 'Kategori diperbarui', 'data' => new CategoryResource($updated)]);
    }

    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);
        return response()->json(['message' => 'Kategori dihapus']);
    }
}
