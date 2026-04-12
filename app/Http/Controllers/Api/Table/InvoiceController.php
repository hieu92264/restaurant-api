<?php

namespace App\Http\Controllers\Api\Table;

use App\Common\Constants\CartOrderStatus;
use App\Common\Constants\InvoicePaymentStatus;
use App\Common\Constants\PaymentMethod;
use App\Common\Constants\TableSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\CartOrder;
use App\Models\Dish;
use App\Models\Invoice;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->with($this->relations())
            ->when(
                $request->filled('cart_order_id'),
                fn (Builder $query) => $query->where('cart_order_id', (int) $request->input('cart_order_id'))
            )
            ->when(
                $request->filled('session_id'),
                fn (Builder $query) => $query->where('session_id', (int) $request->input('session_id'))
            )
            ->when(
                $request->filled('table_id'),
                fn (Builder $query) => $query->where('table_id', (int) $request->input('table_id'))
            )
            ->when(
                $request->filled('payment_status'),
                fn (Builder $query) => $query->where('payment_status', (string) $request->input('payment_status'))
            )
            ->when(
                $request->filled('no'),
                fn (Builder $query) => $query->where('no', (string) $request->input('no'))
            )
            ->when(
                $request->has('is_active'),
                fn (Builder $query) => $query->where('is_active', $request->boolean('is_active'))
            )
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->get();

        return $this->success(
            $invoices->map(fn (Invoice $invoice) => $this->transformInvoice($invoice))->all()
        );
    }

    public function show(int $invoiceId): JsonResponse
    {
        $invoice = $this->findInvoice($invoiceId);

        if (! $invoice) {
            return $this->error(null, 'Khong tim thay hoa don', Response::HTTP_NOT_FOUND);
        }

        return $this->success($this->transformInvoice($invoice));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $cartOrder = $this->resolveCartOrderForInvoice($payload['cart_order_id']);

        if (! $cartOrder) {
            return $this->error(null, 'Don tam tinh khong hop le de tao hoa don', Response::HTTP_BAD_REQUEST);
        }

        if ($this->hasActiveInvoice($cartOrder->id)) {
            return $this->error(null, 'Don tam tinh nay da co hoa don', Response::HTTP_BAD_REQUEST);
        }

        if ($cartOrder->items->where('is_active', true)->isEmpty()) {
            return $this->error(null, 'Don tam tinh chua co mon nao', Response::HTTP_BAD_REQUEST);
        }

        $financials = $this->buildFinancialPayload(
            totalAmount: (int) $cartOrder->total_amount,
            depositAmount: $payload['deposit_amount'] ?? $cartOrder->session->reservation?->deposit_amount ?? 0,
            paidAmount: $payload['paid_amount'] ?? 0
        );

        $invoice = DB::transaction(function () use ($cartOrder, $payload, $financials): Invoice {
            $invoice = Invoice::query()->create([
                'no' => $this->generateInvoiceNo(),
                'cart_order_id' => $cartOrder->id,
                'session_id' => $cartOrder->session_id,
                'table_id' => $cartOrder->table_id,
                'reservation_code' => $cartOrder->session->reservation_code,
                'created_by_employee' => $this->getUserName(),
                'customer_name' => $payload['customer_name'] ?? $cartOrder->session->reservation?->customer_name,
                'customer_phone' => $payload['customer_phone'] ?? $cartOrder->session->reservation?->customer_phone,
                'subtotal_amount' => (int) $cartOrder->subtotal_amount,
                'discount_amount' => (int) $cartOrder->discount_amount,
                'service_charge_amount' => (int) $cartOrder->service_charge_amount,
                'tax_amount' => (int) $cartOrder->tax_amount,
                'deposit_amount' => $financials['deposit_amount'],
                'total_amount' => (int) $cartOrder->total_amount,
                'paid_amount' => $financials['paid_amount'],
                'remaining_amount' => $financials['remaining_amount'],
                'change_amount' => $financials['change_amount'],
                'payment_method' => $payload['payment_method'] ?? null,
                'payment_status' => $financials['payment_status'],
                'issued_at' => $payload['issued_at'] ?? now(),
                'paid_at' => $financials['paid_at'],
                'note' => $payload['note'] ?? null,
                'is_active' => true,
            ]);

            foreach ($cartOrder->items->where('is_active', true) as $item) {
                $invoice->items()->create([
                    'combo_id' => $item->combo_id,
                    'item_name_snapshot' => $item->item_name_snapshot,
                    'variant_name_snapshot' => $item->variant_name_snapshot,
                    'quantity' => $item->quantity,
                    'base_unit_price' => $item->base_unit_price,
                    'option_total_price' => $item->option_total_price,
                    'unit_final_price' => $item->unit_final_price,
                    'line_total' => $item->line_total,
                    'item_note' => $item->item_note,
                    'is_active' => $item->is_active,
                ]);
            }

            $cartOrder->update([
                'status' => CartOrderStatus::CONVERTED_TO_INVOICE,
            ]);

            $this->syncSessionFromInvoice($cartOrder->session, $financials['payment_status']);

            return $invoice;
        });

        return $this->success(
            $this->transformInvoice($invoice->fresh()->load($this->relations())),
            'Tao hoa don thanh cong.',
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateInvoiceRequest $request, int $invoiceId): JsonResponse
    {
        $invoice = Invoice::query()
            ->with(['session', 'cartOrder'])
            ->find($invoiceId);

        if (! $invoice) {
            return $this->error(null, 'Khong tim thay hoa don', Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validated();
        $financials = $this->buildFinancialPayload(
            totalAmount: (int) $invoice->total_amount,
            depositAmount: $payload['deposit_amount'] ?? (int) $invoice->deposit_amount,
            paidAmount: $payload['paid_amount'] ?? (int) $invoice->paid_amount,
            paidAtOverride: $payload['paid_at'] ?? null
        );

        DB::transaction(function () use ($invoice, $payload, $financials): void {
            $invoice->update([
                'customer_name' => $payload['customer_name'] ?? $invoice->customer_name,
                'customer_phone' => $payload['customer_phone'] ?? $invoice->customer_phone,
                'deposit_amount' => $financials['deposit_amount'],
                'paid_amount' => $financials['paid_amount'],
                'remaining_amount' => $financials['remaining_amount'],
                'change_amount' => $financials['change_amount'],
                'payment_method' => array_key_exists('payment_method', $payload)
                    ? $payload['payment_method']
                    : $invoice->payment_method,
                'payment_status' => $financials['payment_status'],
                'issued_at' => $payload['issued_at'] ?? $invoice->issued_at,
                'paid_at' => $financials['paid_at'],
                'note' => array_key_exists('note', $payload) ? $payload['note'] : $invoice->note,
                'is_active' => $payload['is_active'] ?? $invoice->is_active,
            ]);

            if ($invoice->cartOrder) {
                $invoice->cartOrder->update([
                    'status' => CartOrderStatus::CONVERTED_TO_INVOICE,
                ]);
            }

            if ($invoice->session) {
                $this->syncSessionFromInvoice($invoice->session, $financials['payment_status']);
            }
        });

        return $this->success(
            $this->transformInvoice($invoice->fresh()->load($this->relations())),
            'Cap nhat hoa don thanh cong.'
        );
    }

    public function destroy(int $invoiceId): JsonResponse
    {
        $invoice = Invoice::query()->find($invoiceId);

        if (! $invoice) {
            return $this->error(null, 'Khong tim thay hoa don', Response::HTTP_NOT_FOUND);
        }

        $invoice->update([
            'is_active' => false,
        ]);

        return $this->success(null, 'An hoa don thanh cong.');
    }

    protected function relations(): array
    {
        return [
            'cartOrder',
            'session',
            'table',
            'reservation',
            'items.combo.dishes',
        ];
    }

    protected function findInvoice(int $invoiceId): ?Invoice
    {
        return Invoice::query()
            ->with($this->relations())
            ->find($invoiceId);
    }

    protected function resolveCartOrderForInvoice(int $cartOrderId): ?CartOrder
    {
        return CartOrder::query()
            ->with(['session.reservation', 'items'])
            ->where('id', $cartOrderId)
            ->where('is_active', true)
            ->where('status', CartOrderStatus::LOCKED_FOR_PAYMENT)
            ->first();
    }

    protected function hasActiveInvoice(int $cartOrderId): bool
    {
        return Invoice::query()
            ->where('cart_order_id', $cartOrderId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @return array{
     *     deposit_amount:int,
     *     paid_amount:int,
     *     remaining_amount:int,
     *     change_amount:int,
     *     payment_status:string,
     *     paid_at:mixed
     * }
     */
    protected function buildFinancialPayload(
        int $totalAmount,
        int $depositAmount,
        int $paidAmount,
        mixed $paidAtOverride = null
    ): array {
        $depositAmount = max($depositAmount, 0);
        $paidAmount = max($paidAmount, 0);
        $payableAmount = max($totalAmount - $depositAmount, 0);
        $remainingAmount = max($payableAmount - $paidAmount, 0);
        $changeAmount = max($paidAmount - $payableAmount, 0);

        $paymentStatus = match (true) {
            $remainingAmount === 0 => InvoicePaymentStatus::PAID,
            $paidAmount > 0 => InvoicePaymentStatus::PARTIAL,
            default => InvoicePaymentStatus::UNPAID,
        };

        $paidAt = $paymentStatus === InvoicePaymentStatus::PAID
            ? ($paidAtOverride ?? now())
            : null;

        return [
            'deposit_amount' => $depositAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'change_amount' => $changeAmount,
            'payment_status' => $paymentStatus,
            'paid_at' => $paidAt,
        ];
    }

    protected function syncSessionFromInvoice(TableSession $session, string $paymentStatus): void
    {
        $reservation = $session->reservation;

        if ($paymentStatus === InvoicePaymentStatus::PAID) {
            $session->update([
                'status' => TableSessionStatus::PAID,
                'closed_at' => $session->closed_at ?? now(),
                'closed_by_employee' => $session->closed_by_employee ?? $this->getUserName(),
            ]);

            $this->completeReservation($reservation);

            return;
        }

        $session->update([
            'status' => TableSessionStatus::PAYMENT_PENDING,
            'closed_at' => null,
            'closed_by_employee' => null,
        ]);
    }

    protected function completeReservation(?\App\Models\Reservation $reservation): void
    {
        if (! $reservation || ! $reservation->is_active) {
            return;
        }

        if (in_array($reservation->status, [\App\Common\Constants\ReservationStatus::COMPLETED, \App\Common\Constants\ReservationStatus::CANCELED], true)) {
            return;
        }

        $reservation->update([
            'status' => \App\Common\Constants\ReservationStatus::COMPLETED,
            'is_active' => false,
        ]);
    }

    protected function generateInvoiceNo(): string
    {
        do {
            $invoiceNo = 'INV-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Invoice::query()->where('no', $invoiceNo)->exists());

        return $invoiceNo;
    }

    protected function transformInvoice(Invoice $invoice): array
    {
        $dishMap = $this->resolveDishMap($invoice->items);

        return [
            'id' => $invoice->id,
            'no' => $invoice->no,
            'cart_order_id' => $invoice->cart_order_id,
            'session_id' => $invoice->session_id,
            'table_id' => $invoice->table_id,
            'table_name' => $invoice->table?->name,
            'reservation_code' => $invoice->reservation_code,
            'customer_name' => $invoice->customer_name,
            'customer_phone' => $invoice->customer_phone,
            'created_by_employee' => $invoice->created_by_employee,
            'payment_method' => PaymentMethod::display($invoice->payment_method),
            'payment_status' => $invoice->payment_status,
            'issued_at' => $invoice->issued_at,
            'paid_at' => $invoice->paid_at,
            'deposit_amount' => (int) $invoice->deposit_amount,
            'vat_amount' => (int) $invoice->tax_amount,
            'subtotal_amount' => (int) $invoice->subtotal_amount,
            'discount_amount' => (int) $invoice->discount_amount,
            'total_amount' => (int) $invoice->total_amount,
            'paid_amount' => (int) $invoice->paid_amount,
            'remaining_amount' => (int) $invoice->remaining_amount,
            'change_amount' => (int) $invoice->change_amount,
            'remark' => $invoice->note,
            'item_list' => $invoice->items
                ->where('is_active', true)
                ->values()
                ->map(fn ($item) => $this->transformInvoiceItem($item, $dishMap))
                ->all(),
        ];
    }

    protected function transformInvoiceItem(object $item, Collection $dishMap): array
    {
        $dish = $item->combo_id === null
            ? $dishMap->get($item->item_name_snapshot)
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
}
