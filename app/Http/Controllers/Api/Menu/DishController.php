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

        if ($image = $this->resolveUploadedImage($request)) {
            $data['image'] = $this->storeWebpImage($image, $data['slug']);
        }

        unset($data['image_url'], $data['data']);

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

        if ($image = $this->resolveUploadedImage($request)) {
            $this->deleteStoredImage($dish->getRawOriginal('image') ?? $dish->image);
            $data['image'] = $this->storeWebpImage($image, $currentSlug);
        }

        unset($data['image_url'], $data['data']);

        $dish->update($data);

        return $this->success(
            $dish->fresh()->load('category'),
            'Cập nhật món ăn thành công.'
        );
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
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function resolveUploadedImage(StoreDishRequest|UpdateDishRequest $request): ?UploadedFile
    {
        if ($request->hasFile('image')) {
            return $request->file('image');
        }

        if ($request->hasFile('image_url')) {
            return $request->file('image_url');
        }

        return null;
    }

    private function storeWebpImage(UploadedFile $file, string $slug): string
    {
        $imageData = file_get_contents($file->getRealPath());
        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            abort(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Không thể đọc file ảnh hợp lệ.'
            );
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

        return json_encode([
            'size' => (int) Storage::disk('public')->size($relativePath),
            'name' => basename($relativePath),
            'url' => 'storage/' . $relativePath,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function deleteStoredImage(array|string|null $image): void
    {
        if ($image === null || $image === '') {
            return;
        }

        if (is_string($image)) {
            $decodedImage = json_decode($image, true);
            $image = is_array($decodedImage) ? $decodedImage : ['url' => $image];
        }

        $imageUrl = $image['url'] ?? null;

        if (!is_string($imageUrl) || $imageUrl === '') {
            return;
        }

        $relativePath = Str::of($imageUrl)->after('storage/')->value();

        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
