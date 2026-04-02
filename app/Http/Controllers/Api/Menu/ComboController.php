<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComboRequest;
use App\Http\Requests\UpdateComboRequest;
use App\Models\Combo;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ComboController extends Controller
{
    public function index(): JsonResponse
    {
        $combos = Combo::query()
            ->with(['dishes' => fn ($query) => $query->orderByPivot('sort_order')->orderBy('name')])
            ->orderBy('name')
            ->get();

        return $this->success($combos);
    }

    public function show(string $slug): JsonResponse
    {
        $combo = Combo::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success(
            $combo->load(['dishes' => fn ($query) => $query->orderByPivot('sort_order')->orderBy('name')])
        );
    }

    public function store(StoreComboRequest $request): JsonResponse
    {
        $data = $request->validated();
        $dishes = $data['dishes'] ?? [];
        unset($data['dishes']);

        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $combo = DB::transaction(function () use ($data, $dishes) {
            $combo = Combo::query()->create($data);
            $this->syncComboDishes($combo, $dishes);

            return $combo;
        });

        return $this->success(
            $combo->fresh()->load(['dishes' => fn ($query) => $query->orderByPivot('sort_order')->orderBy('name')]),
            'Tạo combo thành công.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateComboRequest $request, string $slug): JsonResponse
    {
        $combo = Combo::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validated();
        $hasDishes = array_key_exists('dishes', $data);
        $dishes = $data['dishes'] ?? [];
        unset($data['dishes']);

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $combo->id);
        }

        DB::transaction(function () use ($combo, $data, $hasDishes, $dishes) {
            $combo->update($data);

            if ($hasDishes) {
                $this->syncComboDishes($combo, $dishes);
            }
        });

        return $this->success(
            $combo->fresh()->load(['dishes' => fn ($query) => $query->orderByPivot('sort_order')->orderBy('name')]),
            'Cập nhật combo thành công.'
        );
    }

    public function destroy(string $slug): JsonResponse
    {
        $combo = Combo::query()->where('slug', $slug)->firstOrFail();

        $combo->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn combo thành công.');
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function syncComboDishes(Combo $combo, array $items): void
    {
        if ($items === []) {
            $combo->dishes()->sync([]);

            return;
        }

        $dishIdsBySlug = Dish::query()
            ->whereIn('slug', collect($items)->pluck('dish_slug')->all())
            ->pluck('id', 'slug');

        $payload = [];

        foreach ($items as $item) {
            $dishId = $dishIdsBySlug[$item['dish_slug']] ?? null;

            if ($dishId === null) {
                continue;
            }

            $payload[$dishId] = [
                'quantity' => $item['quantity'] ?? 1,
                'sort_order' => $item['sort_order'] ?? 0,
                'is_active' => $item['is_active'] ?? true,
            ];
        }

        $combo->dishes()->sync($payload);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'combo';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Combo::withoutGlobalScopes()
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
