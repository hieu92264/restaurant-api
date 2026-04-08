<?php

namespace App\Http\services;

use App\Common\Constants\ReservationStatus;
use App\Common\Constants\RestaurantTableStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\interfaces\ITableStatusService;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Carbon\Carbon;

class TableStatusService implements ITableStatusService
{
    public function syncTableStatus(string $tableCode): void
    {
        $table = RestaurantTable::query()
            ->where('slug', $tableCode)
            ->first();

        if (! $table) {
            return;
        }

        if ($table->status === RestaurantTableStatus::DISABLED) {
            return;
        }

        $now = Carbon::now();

        $hasOpenSession = TableSession::query()
            ->whereHas('table', function ($query) use ($tableCode) {
                $query->where('slug', $tableCode);
            })
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::OPEN,
                TableSessionStatus::PAYMENT_PENDING,
            ])
            ->exists();

        if ($hasOpenSession) {
            $this->updateTableStatus($table, RestaurantTableStatus::OCCUPIED);
            return;
        }

        $hasHoldingReservation = Reservation::query()
            ->where('table_code', $tableCode)
            ->where('is_active', true)
            ->whereIn('status', [
                ReservationStatus::PENDING,
                ReservationStatus::CONFIRMED,
            ])
            ->whereNotNull('hold_start_time')
            ->whereNotNull('hold_end_time')
            ->where('hold_start_time', '<=', $now)
            ->where('hold_end_time', '>=', $now)
            ->exists();

        if ($hasHoldingReservation) {
            $this->updateTableStatus($table, RestaurantTableStatus::RESERVED);
            return;
        }

        if ($table->status === RestaurantTableStatus::CLEANING) {
            return;
        }

        $this->updateTableStatus($table, RestaurantTableStatus::AVAILABLE);
    }

    protected function updateTableStatus(RestaurantTable $table, string $status): void
    {
        if ($table->status === $status) {
            return;
        }

        $table->update([
            'status' => $status,
        ]);
    }
}
