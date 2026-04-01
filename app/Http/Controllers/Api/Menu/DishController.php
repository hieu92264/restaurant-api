<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DishController extends Controller
{
    public function index(): JsonResponse
    {
        $dishes = Dish::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($dishes);
    }

    public function show(string $slug): JsonResponse
    {
        $dish = Dish::query()->where('slug', $slug)->firstOrFail();

        return $this->success($dish->load('category'));
    }

    public function store(StoreDishRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['published_at'] = null;

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeWebpImage($request->file('image'), $data['slug']);
        }

        unset($data['image']);

        $dish = Dish::create($data);

        return $this->success(
            $dish->fresh()->load('category'),
            'Tạo món ăn thành công.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateDishRequest $request, string $slug): JsonResponse
    {
        $dish = Dish::query()->where('slug', $slug)->firstOrFail();
        $data = $request->validated();
        $currentSlug = $dish->slug;

        if (array_key_exists('name', $data)) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $dish->id);
            $currentSlug = $data['slug'];
        }

        if (array_key_exists('is_new', $data)) {
            $data['published_at'] = $data['is_new'] ? now()->toDateString() : null;
            unset($data['is_new']);
        }

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($dish->image_url);
            $data['image_url'] = $this->storeWebpImage($request->file('image'), $currentSlug);
        }

        unset($data['image']);

        $dish->update($data);

        return $this->success($dish->fresh()->load('category'), 'Cập nhật món ăn thành công.');
    }

    public function destroy(string $slug): JsonResponse
    {
        $dish = Dish::query()->where('slug', $slug)->firstOrFail();

        $dish->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn món ăn thành công.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'dish';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Dish::withoutGlobalScopes()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeWebpImage(UploadedFile $file, string $slug): string
    {
        $imageData = file_get_contents($file->getRealPath());
        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Không thể đọc file ảnh hợp lệ.');
        }

        imagesavealpha($image, true);

        $relativePath = 'dishes/' . $slug . '_' . Str::random(8) . '.webp';
        $absolutePath = Storage::disk('public')->path($relativePath);
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagewebp($image, $absolutePath, 85);
        imagedestroy($image);

        return 'storage/' . $relativePath;
    }

    private function deleteStoredImage(?string $imageUrl): void
    {
        if (!$imageUrl) {
            return;
        }

        $relativePath = Str::of($imageUrl)->after('storage/')->value();

        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
