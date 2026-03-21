<?php

namespace App\Http\Controllers\Api\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCookingMethodRequest;
use App\Http\Requests\UpdateCookingMethodRequest;
use App\Models\CookingMethod;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CookingMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $cookingMethods = CookingMethod::query()->get();

        return $this->success($cookingMethods);
    }

    public function store(StoreCookingMethodRequest $request): JsonResponse
    {
        $cookingMethod = CookingMethod::create($request->validated());

        return $this->success($cookingMethod, 'Phuong thuc che bien da duoc tao thanh cong.', Response::HTTP_CREATED);
    }

    public function destroy(string $id): JsonResponse
    {
        $existingCookingMethod = CookingMethod::query()->findOrFail((int) $id);
        $existingCookingMethod->update([
            'is_active' => 'N',
        ]);

        return $this->success(null, 'Xoa phuong thuc che bien thanh cong.');
    }

    public function update(UpdateCookingMethodRequest $request, string $id): JsonResponse
    {
        $existingCookingMethod = CookingMethod::query()->findOrFail((int) $id);
        $existingCookingMethod->update($request->validated());

        return $this->success($existingCookingMethod->fresh(), 'Cap nhat phuong thuc che bien thanh cong.');
    }
}
