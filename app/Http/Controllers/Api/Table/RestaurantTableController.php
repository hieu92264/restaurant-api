<?php

namespace App\Http\Controllers\Api\Table;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantTableRequest;
use App\Http\Requests\UpdateRestaurantTableRequest;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RestaurantTableController extends Controller
{
    public function index(): JsonResponse
    {
        $tables = RestaurantTable::query()
            ->withComputedStatus()
            ->with('holdingReservation')
            ->ordered()
            ->get();

        $tables->each->append('reservation');

        return $this->success($tables);
    }

    public function show(string $slug): JsonResponse
    {
        $table = RestaurantTable::query()
            ->withComputedStatus()
            ->with('holdingReservation')
            ->where('slug', $slug)
            ->firstOrFail();

        $table->append('reservation');

        return $this->success($table);
    }

    public function store(StoreRestaurantTableRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        $table = RestaurantTable::query()->create($data);

        return $this->success(
            $table->fresh(),
            'Tạo bàn thành công.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateRestaurantTableRequest $request, string $slug): JsonResponse
    {
        $table = RestaurantTable::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $request->validated();

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $table->id);
        }

        $table->update($data);

        return $this->success($table->fresh(), 'Cập nhật bàn thành công.');
    }

    public function destroy(string $slug): JsonResponse
    {
        $table = RestaurantTable::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $table->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn bàn thành công.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'table';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            RestaurantTable::withoutGlobalScopes()
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
