<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Models\MenuCategory;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MenuCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = MenuCategory::query()
            ->with(['children', 'itemType'])
            ->orderBy('sort_order')
            ->get();

        return $this->success($categories);
    }

    public function store(StoreMenuCategoryRequest $request): JsonResponse
    {
        $category = MenuCategory::create($request->validated());

        return $this->success(
            $category->fresh()->load(['children', 'itemType']),
            'Danh muc da duoc tao thanh cong.',
            Response::HTTP_CREATED
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $category = MenuCategory::query()->findOrFail((int) $id);
        $category->update([
            'is_active' => 'N',
        ]);

        return $this->success(null, 'Xoa danh muc thanh cong.');
    }

    public function update(UpdateMenuCategoryRequest $request, string $id): JsonResponse
    {
        $existingCategory = MenuCategory::query()->findOrFail((int) $id);
        $existingCategory->update($request->validated());

        return $this->success(
            $existingCategory->fresh()->load(['children', 'itemType']),
            'Cap nhat danh muc thanh cong.'
        );
    }
}
