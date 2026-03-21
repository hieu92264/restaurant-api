<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionGroupRequest;
use App\Http\Requests\UpdateOptionGroupRequest;
use App\Models\OptionGroup;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OptionGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $optionGroups = OptionGroup::query()
            ->with('values')
            ->orderBy('display_order')
            ->get();

        return $this->success($optionGroups);
    }

    public function store(StoreOptionGroupRequest $request): JsonResponse
    {
        $optionGroup = OptionGroup::create($request->validated());

        return $this->success(
            $optionGroup->fresh()->load('values'),
            'Nhom tuy chon da duoc tao thanh cong.',
            Response::HTTP_CREATED
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $existingOptionGroup = OptionGroup::query()->findOrFail((int) $id);
        $existingOptionGroup->update([
            'is_active' => 'N',
        ]);

        return $this->success(null, 'Xoa nhom tuy chon thanh cong.');
    }

    public function update(UpdateOptionGroupRequest $request, string $id): JsonResponse
    {
        $existingOptionGroup = OptionGroup::query()->findOrFail((int) $id);
        $existingOptionGroup->update($request->validated());

        return $this->success(
            $existingOptionGroup->fresh()->load('values'),
            'Cap nhat nhom tuy chon thanh cong.'
        );
    }
}
