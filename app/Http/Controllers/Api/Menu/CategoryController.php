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

    public function show(Category $category): JsonResponse
    {
        return $this->success($category->load(['parent', 'children', 'dishes']));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = $this->generateUniqueCode($data['name']);

        $category = Category::create($data);

        return $this->success(
            $category->fresh()->load(['parent', 'children']),
            'Tao danh muc thanh cong.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::query()->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $data['code'] = $this->generateUniqueCode($data['name'], $category->id);
        }

        if (($data['parent_id'] ?? null) === $category->id) {
            return $this->error(null, 'Danh muc khong the chon chinh no lam danh muc cha.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category->update($data);

        return $this->success(
            $category->fresh()->load(['parent', 'children']),
            'Cap nhat danh muc thanh cong.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::query()->withCount(['children', 'dishes'])->findOrFail($id);

        if ($category->children_count > 0 || $category->dishes_count > 0) {
            return $this->error(
                null,
                'Khong the xoa danh muc dang co danh muc con hoac mon an.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $category->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'An danh muc thanh cong.');
    }

    private function generateUniqueCode(string $name, ?int $ignoreId = null): string
    {
        $baseCode = Str::of(Str::ascii($name))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        if ($baseCode === '') {
            $baseCode = 'category';
        }

        $code = $baseCode;
        $counter = 2;

        while (
            Category::withoutGlobalScopes()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('code', $code)
                ->exists()
        ) {
            $code = $baseCode . '_' . $counter;
            $counter++;
        }

        return $code;
    }
}
