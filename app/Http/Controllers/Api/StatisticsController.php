<?php

namespace App\Http\Controllers\Api;

use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function monthlyRevenue(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        [$monthStart, $monthEnd] = $this->buildPeriodBoundaries($year, $month);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
                'from_date' => $monthStart->toDateString(),
                'to_date' => $monthEnd->toDateString(),
            ],
            'monthly_revenue' => $this->buildRevenueSnapshot($monthStart, $monthEnd, sprintf('%02d/%04d', $month, $year)),
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        [$monthStart, $monthEnd, $yearStart, $yearEnd] = $this->buildPeriodBoundaries($year, $month);

        $monthlyRevenue = $this->paidInvoicesBetween($monthStart, $monthEnd)->sum('total_amount');
        $yearlyRevenue = $this->paidInvoicesBetween($yearStart, $yearEnd)->sum('total_amount');

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
            ],
            'month' => [
                'label' => sprintf('%02d/%04d', $month, $year),
                'total_amount' => (int) $monthlyRevenue,
            ],
            'year' => [
                'label' => (string) $year,
                'total_amount' => (int) $yearlyRevenue,
            ],
        ]);
    }

    public function revenueChart(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        [$monthStart, $monthEnd] = $this->buildPeriodBoundaries($year, $month);

        $chartEnd = $this->resolveRevenueChartEnd($year, $month, $monthEnd);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
                'from_date' => $monthStart->toDateString(),
                'to_date' => $chartEnd->toDateString(),
            ],
            'data' => $this->buildRevenueChart($monthStart, $chartEnd),
        ]);
    }

    public function averageServiceTime(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        [$monthStart, $monthEnd] = $this->buildPeriodBoundaries($year, $month);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
            ],
            'average_service_time' => $this->buildAverageServiceTime($monthStart, $monthEnd),
        ]);
    }

    public function topDishesReport(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        $topLimit = $this->resolveTopLimit($request);
        [$monthStart, $monthEnd] = $this->buildPeriodBoundaries($year, $month);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
                'top_limit' => $topLimit,
            ],
            'items' => $this->topDishes($monthStart, $monthEnd, $topLimit),
        ]);
    }

    public function topCombosReport(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        $topLimit = $this->resolveTopLimit($request);
        [$monthStart, $monthEnd] = $this->buildPeriodBoundaries($year, $month);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
                'top_limit' => $topLimit,
            ],
            'items' => $this->topCombos($monthStart, $monthEnd, $topLimit),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        [$year, $month] = $this->resolvePeriod($request);
        $topLimit = $this->resolveTopLimit($request);

        [$monthStart, $monthEnd, $yearStart, $yearEnd] = $this->buildPeriodBoundaries($year, $month);

        $monthlyRevenue = $this->paidInvoicesBetween($monthStart, $monthEnd)->sum('total_amount');
        $yearlyRevenue = $this->paidInvoicesBetween($yearStart, $yearEnd)->sum('total_amount');

        $averageServiceTime = $this->buildAverageServiceTime($monthStart, $monthEnd);

        return $this->success([
            'filter' => [
                'year' => $year,
                'month' => $month,
                'top_limit' => $topLimit,
            ],
            'revenue' => [
                'month' => [
                    'label' => sprintf('%02d/%04d', $month, $year),
                    'total_amount' => (int) $monthlyRevenue,
                ],
                'year' => [
                    'label' => (string) $year,
                    'total_amount' => (int) $yearlyRevenue,
                ],
            ],
            'average_service_time' => $averageServiceTime,
            'top_dishes' => $this->topDishes($monthStart, $monthEnd, $topLimit),
            'top_combos' => $this->topCombos($monthStart, $monthEnd, $topLimit),
        ]);
    }

    protected function resolvePeriod(Request $request): array
    {
        $year = max($request->integer('year', now()->year), 2000);
        $month = min(max($request->integer('month', now()->month), 1), 12);

        return [$year, $month];
    }

    protected function resolveTopLimit(Request $request): int
    {
        return min(max($request->integer('top_limit', 5), 1), 20);
    }

    protected function buildPeriodBoundaries(int $year, int $month): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = $yearStart->copy()->endOfYear();

        return [$monthStart, $monthEnd, $yearStart, $yearEnd];
    }

    protected function paidInvoicesBetween(Carbon $start, Carbon $end)
    {
        return Invoice::query()
            ->where('is_active', true)
            ->where('payment_status', InvoicePaymentStatus::PAID)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$start, $end]);
    }

    protected function buildRevenueSnapshot(Carbon $start, Carbon $end, string $label): array
    {
        $aggregate = $this->paidInvoicesBetween($start, $end)
            ->selectRaw('COUNT(*) as paid_invoice_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        return [
            'label' => $label,
            'from_date' => $start->toDateString(),
            'to_date' => $end->toDateString(),
            'paid_invoice_count' => (int) ($aggregate?->paid_invoice_count ?? 0),
            'total_amount' => (int) ($aggregate?->total_amount ?? 0),
        ];
    }

    protected function resolveRevenueChartEnd(int $year, int $month, Carbon $monthEnd): Carbon
    {
        $today = now();

        if ($year === $today->year && $month === $today->month) {
            return $today->copy()->endOfDay();
        }

        return $monthEnd;
    }

    protected function buildRevenueChart(Carbon $start, Carbon $end): array
    {
        $totalsByDate = $this->paidInvoicesBetween($start, $end)
            ->selectRaw('DATE(paid_at) as paid_date')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total_amount', 'paid_date');

        $data = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();

            $data[] = [
                'total_amount' => (int) ($totalsByDate[$date] ?? 0),
                'date' => $date,
            ];

            $cursor->addDay();
        }

        return $data;
    }

    protected function buildAverageServiceTime(Carbon $start, Carbon $end): array
    {
        $sessions = TableSession::query()
            ->where('is_active', true)
            ->whereIn('status', [
                TableSessionStatus::PAID,
                TableSessionStatus::CLOSED,
            ])
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$start, $end])
            ->get(['opened_at', 'closed_at']);

        $servedSessions = $sessions->count();
        $averageMinutes = $servedSessions === 0
            ? 0
            : (int) round($sessions->avg(fn (TableSession $session) => $session->opened_at->diffInMinutes($session->closed_at)));

        return [
            'served_sessions' => $servedSessions,
            'average_minutes' => $averageMinutes,
            'average_hours' => round($averageMinutes / 60, 2),
        ];
    }

    protected function topDishes(Carbon $start, Carbon $end, int $limit): array
    {
        $items = InvoiceItem::query()
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoice_items.is_active', true)
            ->whereNull('invoice_items.combo_id')
            ->where('invoices.is_active', true)
            ->where('invoices.payment_status', InvoicePaymentStatus::PAID)
            ->whereNotNull('invoices.paid_at')
            ->whereBetween('invoices.paid_at', [$start, $end])
            ->groupBy('invoice_items.item_name_snapshot')
            ->orderByDesc('quantity_sold')
            ->orderByDesc('revenue_amount')
            ->limit($limit)
            ->get([
                'invoice_items.item_name_snapshot as name',
                DB::raw('MAX(invoice_items.dish_id) as dish_id'),
                DB::raw('SUM(invoice_items.quantity) as quantity_sold'),
                DB::raw('SUM(invoice_items.line_total) as revenue_amount'),
            ]);

        [$dishesById, $dishesByName] = $this->resolveStatisticDishes($items);

        return $items
            ->map(fn ($item) => [
                'name' => $item->name,
                'image' => $this->resolveStatisticDishImage($item, $dishesById, $dishesByName),
                'quantity_sold' => (float) $item->quantity_sold,
                'revenue_amount' => (int) $item->revenue_amount,
            ])
            ->all();
    }

    protected function topCombos(Carbon $start, Carbon $end, int $limit): array
    {
        $items = InvoiceItem::query()
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoice_items.is_active', true)
            ->whereNotNull('invoice_items.combo_id')
            ->where('invoices.is_active', true)
            ->where('invoices.payment_status', InvoicePaymentStatus::PAID)
            ->whereNotNull('invoices.paid_at')
            ->whereBetween('invoices.paid_at', [$start, $end])
            ->groupBy('invoice_items.combo_id', 'invoice_items.item_name_snapshot')
            ->orderByDesc('quantity_sold')
            ->orderByDesc('revenue_amount')
            ->limit($limit)
            ->get([
                'invoice_items.combo_id',
                'invoice_items.item_name_snapshot as name',
                DB::raw('SUM(invoice_items.quantity) as quantity_sold'),
                DB::raw('SUM(invoice_items.line_total) as revenue_amount'),
            ]);

        $combosById = $this->resolveStatisticCombos($items);

        return $items
            ->map(fn ($item) => [
                'combo_id' => (int) $item->combo_id,
                'name' => $item->name,
                'image' => $combosById->get((int) $item->combo_id)?->combo_image,
                'quantity_sold' => (float) $item->quantity_sold,
                'revenue_amount' => (int) $item->revenue_amount,
            ])
            ->all();
    }

    protected function resolveStatisticDishes(Collection $items): array
    {
        $dishIds = $items
            ->pluck('dish_id')
            ->filter(fn ($dishId) => is_numeric($dishId))
            ->map(fn ($dishId) => (int) $dishId)
            ->unique()
            ->values();

        $dishNames = $items
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->values();

        if ($dishIds->isEmpty() && $dishNames->isEmpty()) {
            return [collect(), collect()];
        }

        $dishes = Dish::query()
            ->where(function ($query) use ($dishIds, $dishNames): void {
                if ($dishIds->isNotEmpty()) {
                    $query->whereIn('id', $dishIds->all());
                }

                if ($dishNames->isNotEmpty()) {
                    $query->orWhereIn('name', $dishNames->all());
                }
            })
            ->get();

        return [
            $dishes->keyBy('id'),
            $dishes->keyBy('name'),
        ];
    }

    protected function resolveStatisticDishImage(object $item, Collection $dishesById, Collection $dishesByName): array|string|null
    {
        $dish = is_numeric($item->dish_id ?? null)
            ? $dishesById->get((int) $item->dish_id)
            : null;

        $dish ??= $dishesByName->get($item->name);

        return $dish?->image;
    }

    protected function resolveStatisticCombos(Collection $items): Collection
    {
        $comboIds = $items
            ->pluck('combo_id')
            ->filter(fn ($comboId) => is_numeric($comboId))
            ->map(fn ($comboId) => (int) $comboId)
            ->unique()
            ->values();

        if ($comboIds->isEmpty()) {
            return collect();
        }

        return Combo::query()
            ->whereIn('id', $comboIds->all())
            ->get()
            ->keyBy('id');
    }
}
