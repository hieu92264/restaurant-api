<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\OrderLineStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartOrderRequest;
use App\Http\Requests\UpdateCartOrderRequest;
use App\Models\CartOrder;
use App\Models\CartOrderItem;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CartOrderController extends Controller
{
    /**
     * Lấy danh sách đơn tạm tính với các bộ lọc
     */
    public function index(Request $request): JsonResponse
    {
        $cartOrders = CartOrder::query()
            ->with($this->relations())
            ->when(
                $request->filled('session_id'),
                fn(Builder $query) => $query->where('session_id', (int) $request->input('session_id'))
            )
            ->when(
                $request->filled('table_id'),
                fn(Builder $query) => $query->where('table_id', (int) $request->input('table_id'))
            )
            ->when(
                $request->filled('status'),
                fn(Builder $query) => $query->where('status', (string) $request->input('status'))
            )
            ->when(
                $request->has('is_active'),
                fn(Builder $query) => $query->where('is_active', $request->boolean('is_active'))
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return $this->success($cartOrders);
    }

    /**
     * Chi tiết một đơn tạm tính
     */
    public function show(int $cartOrderId): JsonResponse
    {
        $cartOrder = $this->findCartOrder($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        return $this->success($cartOrder);
    }

    /**
     * Tạo mới đơn tạm tính kèm danh sách món
     */
    public function showCurrentByTable(int $tableId): JsonResponse
    {
        $cartOrder = CartOrder::query()
            ->with([
                'table',
                'session.reservation',
                'items.combo.dishes',
                'items.dish',
            ])
            ->where('table_id', $tableId)
            ->where('is_active', true)
            ->whereIn('status', [
                CartOrderStatus::OPEN,
                CartOrderStatus::LOCKED_FOR_PAYMENT,
            ])
            ->whereHas('session', function (Builder $query): void {
                $query->where('is_active', true)
                    ->whereIn('status', [
                        TableSessionStatus::OPEN,
                        TableSessionStatus::PAYMENT_PENDING,
                    ]);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính đang mở của bàn này!', Response::HTTP_NOT_FOUND);
        }

        return $this->success($this->transformCurrentCartOrder($cartOrder));
    }

    public function store(StoreCartOrderRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $resolvedItems = $this->buildRequestedCartOrderItemsPayload($payload['items']);
        $financials = $this->buildCartOrderFinancials($payload, $resolvedItems);
        $tableSession = $this->resolveLiveSession($payload['session_id']);

        if (! $tableSession) {
            return $this->error(null, 'Phiên bản không hợp lệ hoặc đã kết thúc', Response::HTTP_BAD_REQUEST);
        }

        if ($tableSession->table_id !== $payload['table_id']) {
            return $this->error(null, 'Bàn không thuộc phiên bản này', Response::HTTP_BAD_REQUEST);
        }

        $cartOrder = DB::transaction(function () use ($payload, $resolvedItems, $financials): CartOrder {
            // 1. Tạo đơn hàng chính
            $cartOrder = CartOrder::query()->create([
                'session_id' => $payload['session_id'],
                'table_id' => $payload['table_id'],
                'order_no' => $this->generateOrderNo(),
                'created_by_employee' => $this->getUserName(),
                'status' => CartOrderStatus::OPEN,
                'subtotal_amount' => $financials['subtotal_amount'],
                'discount_amount' => $financials['discount_amount'],
                'service_charge_amount' => $financials['service_charge_amount'],
                'tax_amount' => $financials['tax_amount'],
                'total_amount' => $financials['total_amount'],
                'remark' => $payload['remark'] ?? null,
                'is_active' => true,
            ]);

            // 2. Tạo các món ăn trong đơn (cart_order_id tự động được gán)
            foreach ($resolvedItems as $item) {
                $cartOrder->items()->create([
                    'dish_id' => $item['dish_id'],
                    'combo_id' => $item['combo_id'] ?? null,
                    'item_name_snapshot' => $item['item_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'] ?? null,
                    'quantity' => $item['quantity'],
                    'base_unit_price' => $item['base_unit_price'],
                    'option_total_price' => $item['option_total_price'] ?? 0,
                    'unit_final_price' => $item['unit_final_price'],
                    'line_total' => $item['line_total'],
                    'item_note' => $item['item_note'] ?? null,
                    'line_status' => OrderLineStatus::ACTIVE,
                    'is_active' => true,
                ]);
            }

            return $cartOrder;
        });

        return $this->success(
            $cartOrder->fresh()->load($this->relations()),
            'Tạo đơn tạm tính thành công.',
            Response::HTTP_CREATED
        );
    }

    /**
     * Cập nhật thông tin đơn hàng và danh sách món
     */
    public function update(UpdateCartOrderRequest $request, int $cartOrderId): JsonResponse
    {
        $cartOrder = CartOrder::query()
            ->with(['session', 'items'])
            ->find($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validated();
        $resolvedItems = array_key_exists('items', $payload)
            ? $this->buildRequestedCartOrderItemsPayload($payload['items'])
            : null;

        // Kiểm tra logic chuyển đổi trạng thái
        if (
            array_key_exists('status', $payload)
            && ! $this->isAllowedStatusTransition($cartOrder->status, $payload['status'])
        ) {
            return $this->error(null, 'Không thể chuyển trạng thái đơn tạm tính này', Response::HTTP_BAD_REQUEST);
        }

        // Kiểm tra tính hợp lệ của session nếu có thay đổi bàn/phiên
        if (
            array_key_exists('session_id', $payload)
            || array_key_exists('table_id', $payload)
        ) {
            $nextSessionId = $payload['session_id'] ?? $cartOrder->session_id;
            $nextTableId = $payload['table_id'] ?? $cartOrder->table_id;
            $tableSession = $this->resolveLiveSession($nextSessionId);

            if (! $tableSession) {
                return $this->error(null, 'Phiên bản mới không hợp lệ hoặc đã kết thúc', Response::HTTP_BAD_REQUEST);
            }

            if ($tableSession->table_id !== $nextTableId) {
                return $this->error(null, 'Bàn không thuộc phiên bản được chọn', Response::HTTP_BAD_REQUEST);
            }
        }

        DB::transaction(function () use ($cartOrder, $payload, $resolvedItems): void {
            // Cập nhật danh sách món nếu có truyền lên
            if ($resolvedItems !== null) {
                $this->syncCartOrderItems($cartOrder, $resolvedItems);
                $payload = $this->buildCartOrderFinancials($payload, $resolvedItems, $cartOrder);
            }

            // Cập nhật các thông tin còn lại của đơn hàng
            $cartOrder->update(Arr::except($payload, ['items']));
        });

        return $this->success(
            $cartOrder->fresh()->load($this->relations()),
            'Cập nhật đơn tạm tính thành công.'
        );
    }

    /**
     * Xóa mềm (ẩn) đơn tạm tính
     */
    public function destroy(int $cartOrderId): JsonResponse
    {
        $cartOrder = CartOrder::query()->find($cartOrderId);

        if (! $cartOrder) {
            return $this->error(null, 'Không tìm thấy đơn tạm tính', Response::HTTP_NOT_FOUND);
        }

        $cartOrder->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'Ẩn đơn tạm tính thành công.');
    }

    /**
     * Định nghĩa các quan hệ cần load kèm theo
     */
    protected function relations(): array
    {
        return [
            'session',
            'table',
            'items',
            'items.combo',
            'items.dish',
            'invoice',
        ];
    }

    /**
     * Tìm đơn tạm tính kèm theo các quan hệ
     */
    protected function findCartOrder(int $cartOrderId): ?CartOrder
    {
        return CartOrder::query()
            ->with($this->relations())
            ->find($cartOrderId);
    }

    /**
     * Kiểm tra phiên hoạt động hợp lệ
     */
    protected function resolveLiveSession(int $sessionId): ?TableSession
    {
        return TableSession::query()
            ->where('id', $sessionId)
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
            ])
            ->first();
    }

    /**
     * Quản lý logic chuyển đổi trạng thái đơn hàng (State Machine)
     */
    protected function isAllowedStatusTransition(string $currentStatus, string $nextStatus): bool
    {
        $allowedTransitions = [
            CartOrderStatus::OPEN => [
                CartOrderStatus::OPEN,
                CartOrderStatus::LOCKED_FOR_PAYMENT,
                CartOrderStatus::CANCELLED,
            ],
            CartOrderStatus::LOCKED_FOR_PAYMENT => [
                CartOrderStatus::LOCKED_FOR_PAYMENT,
                CartOrderStatus::OPEN,
                CartOrderStatus::CANCELLED,
                CartOrderStatus::CONVERTED_TO_INVOICE,
            ],
            CartOrderStatus::CANCELLED => [
                CartOrderStatus::CANCELLED,
            ],
            CartOrderStatus::CONVERTED_TO_INVOICE => [
                CartOrderStatus::CONVERTED_TO_INVOICE,
            ],
        ];

        return in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true);
    }

    /**
     * Tạo mã đơn hàng duy nhất
     */
    protected function generateOrderNo(): string
    {
        do {
            $orderNo = 'ORD-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CartOrder::query()->where('order_no', $orderNo)->exists());

        return $orderNo;
    }

    protected function transformCurrentCartOrder(CartOrder $cartOrder): array
    {
        $dishMap = $this->resolveDishMap($cartOrder->items);

        return [
            'cart_order_id' => $cartOrder->id,
            'table_id' => $cartOrder->table_id,
            'table_name' => $cartOrder->table?->name,
            'session_id' => $cartOrder->session_id,
            'status' => $cartOrder->status,
            'remark' => $cartOrder->remark,
            'item_list' => $cartOrder->items
                ->where('is_active', true)
                ->values()
                ->map(fn ($item) => $this->transformCartOrderItem($item, $dishMap))
                ->all(),
        ];
    }

    protected function transformCartOrderItem(object $item, Collection $dishMap): array
    {
        $dish = $item->combo_id === null
            ? ($item->dish ?? $dishMap->get($item->dish_id ?? $item->item_name_snapshot))
            : null;

        return [
            'type' => $item->combo_id === null ? 'dish' : 'combo',
            'name' => $item->item_name_snapshot,
            'image' => $item->combo?->combo_image ?? $dish?->image,
            'unit_price' => (int) $item->unit_final_price,
            'quantity' => (float) $item->quantity,
        ];
    }

    protected function resolveDishMap(Collection $items): Collection
    {
        $dishIds = $items
            ->where('combo_id', null)
            ->pluck('dish_id')
            ->filter(fn ($dishId) => is_numeric($dishId))
            ->unique()
            ->values();

        if ($dishIds->isNotEmpty()) {
            return Dish::query()
                ->whereIn('id', $dishIds->all())
                ->get()
                ->keyBy('id');
        }

        $dishNames = $items
            ->where('combo_id', null)
            ->pluck('item_name_snapshot')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->values();

        if ($dishNames->isEmpty()) {
            return collect();
        }

        return Dish::query()
            ->whereIn('name', $dishNames->all())
            ->get()
            ->keyBy('name');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function buildRequestedCartOrderItemsPayload(array $items): array
    {
        $normalizedItems = collect($items)
            ->map(function (array $item): array {
                $itemType = strtoupper((string) $item['item_type']);

                return [
                    'sync_key' => $this->makeCartOrderItemSyncKey($itemType, (int) $item['item_id']),
                    'item_type' => $itemType,
                    'item_id' => (int) $item['item_id'],
                    'quantity' => (float) $item['quantity'],
                ];
            })
            ->groupBy('sync_key')
            ->map(function (Collection $group): array {
                $firstItem = $group->first();

                return [
                    'sync_key' => $firstItem['sync_key'],
                    'item_type' => $firstItem['item_type'],
                    'item_id' => $firstItem['item_id'],
                    'quantity' => (float) $group->sum('quantity'),
                ];
            })
            ->values();

        $dishes = Dish::query()
            ->whereIn(
                'id',
                $normalizedItems
                    ->where('item_type', 'DISH')
                    ->pluck('item_id')
                    ->all()
            )
            ->get()
            ->keyBy('id');

        $combos = Combo::query()
            ->with('dishes')
            ->whereIn(
                'id',
                $normalizedItems
                    ->where('item_type', 'COMBO')
                    ->pluck('item_id')
                    ->all()
            )
            ->get()
            ->keyBy('id');

        $resolvedItems = $normalizedItems->map(function (array $item) use ($dishes, $combos): array {
            if ($item['item_type'] === 'DISH') {
                $dish = $dishes->get($item['item_id']);

                if (! $dish) {
                    throw ValidationException::withMessages([
                        'items' => ['Mon an khong ton tai hoac khong hop le.'],
                    ]);
                }

                $unitPrice = (int) round((float) $dish->discounted_price);

                return [
                    'sync_key' => $item['sync_key'],
                    'dish_id' => $dish->id,
                    'combo_id' => null,
                    'item_name_snapshot' => $dish->name,
                    'variant_name_snapshot' => null,
                    'quantity' => $item['quantity'],
                    'base_unit_price' => $unitPrice,
                    'option_total_price' => 0,
                    'unit_final_price' => $unitPrice,
                    'line_total' => (int) round($unitPrice * $item['quantity']),
                    'item_note' => null,
                ];
            }

            $combo = $combos->get($item['item_id']);

            if (! $combo) {
                throw ValidationException::withMessages([
                    'items' => ['Combo khong ton tai hoac khong hop le.'],
                ]);
            }

            $unitPrice = (int) $combo->combo_price;

            return [
                'sync_key' => $item['sync_key'],
                'dish_id' => null,
                'combo_id' => $combo->id,
                'item_name_snapshot' => $combo->name,
                'variant_name_snapshot' => null,
                'quantity' => $item['quantity'],
                'base_unit_price' => $unitPrice,
                'option_total_price' => 0,
                'unit_final_price' => $unitPrice,
                'line_total' => (int) round($unitPrice * $item['quantity']),
                'item_note' => null,
            ];
        });

        return $resolvedItems->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $resolvedItems
     */
    protected function syncCartOrderItems(CartOrder $cartOrder, array $resolvedItems): void
    {
        $existingItems = $cartOrder->items()->get();
        $dishIdsByName = $this->resolveDishIdsByName($existingItems);
        $groupedExistingItems = [];
        $staleItemIds = [];

        foreach ($existingItems as $existingItem) {
            $syncKey = $this->resolveExistingCartOrderItemSyncKey($existingItem, $dishIdsByName);

            if ($syncKey === null) {
                $staleItemIds[] = $existingItem->id;

                continue;
            }

            $groupedExistingItems[$syncKey] ??= [];
            $groupedExistingItems[$syncKey][] = $existingItem;
        }

        foreach ($resolvedItems as $item) {
            $matchedItemsForKey = $groupedExistingItems[$item['sync_key']] ?? [];
            $matchedItem = array_shift($matchedItemsForKey);
            $groupedExistingItems[$item['sync_key']] = $matchedItemsForKey;
            $itemPayload = Arr::except($item, ['sync_key']);

            if ($matchedItem instanceof CartOrderItem) {
                $matchedItem->update(array_merge($itemPayload, [
                    'item_note' => $matchedItem->item_note,
                    'line_status' => $matchedItem->line_status,
                    'is_active' => true,
                ]));

                continue;
            }

            $cartOrder->items()->create(array_merge($itemPayload, [
                'line_status' => OrderLineStatus::ACTIVE,
                'is_active' => true,
            ]));
        }

        foreach ($groupedExistingItems as $items) {
            foreach ($items as $staleItem) {
                $staleItemIds[] = $staleItem->id;
            }
        }

        if ($staleItemIds !== []) {
            $cartOrder->items()->whereIn('id', array_values(array_unique($staleItemIds)))->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, array<string, mixed>>  $resolvedItems
     * @return array<string, mixed>
     */
    protected function buildCartOrderFinancials(
        array $payload,
        array $resolvedItems,
        ?CartOrder $cartOrder = null
    ): array
    {
        $subtotalAmount = (int) round(collect($resolvedItems)->sum('line_total'));
        $discountAmount = (int) ($payload['discount_amount'] ?? $cartOrder?->discount_amount ?? 0);
        $serviceChargeAmount = (int) ($payload['service_charge_amount'] ?? $cartOrder?->service_charge_amount ?? 0);
        $taxAmount = (int) ($payload['tax_amount'] ?? $cartOrder?->tax_amount ?? 0);

        $payload['subtotal_amount'] = $subtotalAmount;
        $payload['discount_amount'] = $discountAmount;
        $payload['service_charge_amount'] = $serviceChargeAmount;
        $payload['tax_amount'] = $taxAmount;
        $payload['total_amount'] = max($subtotalAmount - $discountAmount + $serviceChargeAmount + $taxAmount, 0);

        return $payload;
    }

    protected function resolveDishIdsByName(Collection $items): Collection
    {
        $dishNames = $items
            ->where('combo_id', null)
            ->whereNull('dish_id')
            ->pluck('item_name_snapshot')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->values();

        if ($dishNames->isEmpty()) {
            return collect();
        }

        return Dish::query()
            ->whereIn('name', $dishNames->all())
            ->pluck('id', 'name');
    }

    protected function resolveExistingCartOrderItemSyncKey(CartOrderItem $item, Collection $dishIdsByName): ?string
    {
        if ($item->combo_id !== null) {
            return $this->makeCartOrderItemSyncKey('COMBO', (int) $item->combo_id);
        }

        if ($item->dish_id !== null) {
            return $this->makeCartOrderItemSyncKey('DISH', (int) $item->dish_id);
        }

        $dishId = $dishIdsByName->get($item->item_name_snapshot);

        if (! $dishId) {
            return null;
        }

        return $this->makeCartOrderItemSyncKey('DISH', (int) $dishId);
    }

    protected function makeCartOrderItemSyncKey(string $itemType, int $itemId): string
    {
        return strtoupper($itemType) . ':' . $itemId;
    }
}
