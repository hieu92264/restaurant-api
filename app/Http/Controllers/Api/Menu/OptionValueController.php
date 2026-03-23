<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionValueRequest;
use App\Http\Requests\UpdateOptionValueRequest;
use App\Models\OptionValue;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OptionValueController extends Controller
{
    public function index(): JsonResponse
    {
        $optionValues = OptionValue::query()->get();

        return $this->success($optionValues);
    }

    public function store(StoreOptionValueRequest $request): JsonResponse
    {
        $optionValue = OptionValue::create($request->validated());

        return $this->success($optionValue, 'Khởi tạo giá trị tuỳ chọn thành công.', Response::HTTP_CREATED);
    }

    public function destroy(string $id): JsonResponse
    {
        $existingOptionValue = OptionValue::query()->findOrFail((int)$id);

        $existingOptionValue->update(
            [
                'is_active' => 'N',
            ]
        );

        return $this->success(null, 'Xoá giá trị tuỳ chọn thành công.');
    }

    public function update(UpdateOptionValueRequest $request, string $id): JsonResponse
    {
        $existingOptionValue = OptionValue::findOrFail((int)$id);

        $optionValue = $existingOptionValue->update($request->validated());

        return $this->success($optionValue, 'Cập nhật giá trị tuỳ chọn thành công.');
    }
}
