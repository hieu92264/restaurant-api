<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($categories);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success($category->load(['parent', 'children', 'dishes']));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $category = Category::create($data);

        return $this->success(
            $category->fresh()->load(['parent', 'children']),
            'Tạo danh mục thành công.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateCategoryRequest $request, string $slug): JsonResponse
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        if (($data['parent_id'] ?? null) === $category->id) {
            return $this->error(
                null,
                'Danh mục không thể chọn chính nó làm danh mục cha.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $category->update($data);

        return $this->success(
            $category->fresh()->load(['parent', 'children']),
            'Cập nhật danh mục thành công.'
        );
    }

    public function destroy(string $slug): JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->withCount(['children', 'dishes'])
            ->firstOrFail();

        if ($category->children_count > 0 || $category->dishes_count > 0) {
            return $this->error(
                null,
                'Không thể xóa danh mục đang có danh mục con hoặc món ăn.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $category->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn danh mục thành công.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::withoutGlobalScopes()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
