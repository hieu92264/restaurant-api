<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTableSessionRequest;
use App\Http\Requests\UpdateTableSessionRequest;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\isEmpty;

class TableSessionController extends Controller
{

//'table_id' => 'integer',
//'opened_by_employee' => 'string',
//'closed_by_employee' => 'string',
//'guest_count' => 'integer',
//'status' => 'string',
//'opened_at' => 'datetime',
//'closed_at' => 'datetime',
//'reservation_code' => 'string',
//'is_active' => 'boolean',
//'created_at' => 'datetime',
//'updated_at' => 'datetime',

    public function index(): JsonResponse
    {
        $tableSession = TableSession::with('cartOrders', 'invoices')->get();

        return $this->success($tableSession);
    }

    public function show(?int $tableId, ?string $reservationCode): JsonResponse
    {
        $tableCode = isEmpty($tableId) ? null : (int)$tableId;
        $reservationCode = isEmpty($reservationCode) ? null : (string)$reservationCode;

        $tableSession = TableSession::with('cartOrders', 'invoices');

        if (!isEmpty($tableCode)) $tableSession = $tableSession->where('table_sessions.table_id', $tableCode);

        if (!isEmpty($tableSession)) $tableSession = $tableSession->first();

        return $this->success($tableSession->toArray());
    }

    public function store(StoreTableSessionRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $table = RestaurantTable::find($payload['table_id']);

        $reservation = null;
        if (!empty($payload['reservation_code'])) {
            $reservation = Reservation::where('reservation_code', $payload['reservation_code'])->first();
        }

        $isValidReservedTable =
            $table->status === RestaurantTableStatus::RESERVED
            && !empty($reservation)
            && $reservation->table_code === $table->slug;

        if (
            $table->status !== RestaurantTableStatus::AVAILABLE
            && !$isValidReservedTable
        ) {
            return $this->error(null, 'Bàn hiện tại không khả dụng');
        }

        $payload['reservation_code'] = $reservation?->reservation_code ?? null;
        $payload['status'] = TableSessionStatus::OPEN;
        $payload['opened_at'] = now();
        $payload['opened_by_employee'] = $this->getUserName();

        $tableSession = TableSession::create($payload);

        
        return $this->success($tableSession->fresh()->toArray(), 'Mở phiên bàn thành công', Response::HTTP_CREATED);
    }

    public function update(UpdateTableSessionRequest $request, int $tableSessionId): JsonResponse
    {

    }
}
