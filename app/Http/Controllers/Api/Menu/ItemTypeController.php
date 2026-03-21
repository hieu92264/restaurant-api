<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemTypeRequest;
use App\Http\Requests\UpdateItemTypeRequest;
use App\Models\ItemType;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ItemTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $itemTypes = ItemType::query()->get();

        return $this->success($itemTypes);
    }

    public function store(StoreItemTypeRequest $request): JsonResponse
    {
        $itemType = ItemType::create($request->validated());

        return $this->success($itemType, 'Loai mon an da duoc tao thanh cong.', Response::HTTP_CREATED);
    }

    public function destroy(string $id): JsonResponse
    {
        $existingItemType = ItemType::query()->findOrFail((int) $id);
        $existingItemType->update([
            'is_active' => 'N',
        ]);

        return $this->success(null, 'Xoa loai mon an thanh cong.');
    }

    public function update(UpdateItemTypeRequest $request, string $id): JsonResponse
    {
        $existingItemType = ItemType::query()->findOrFail((int) $id);
        $existingItemType->update($request->validated());

        return $this->success($existingItemType->fresh(), 'Cap nhat loai mon an thanh cong.');
    }
}
