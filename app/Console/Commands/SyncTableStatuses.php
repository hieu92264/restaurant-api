<?php

namespace App\Console\Commands;

use App\Http\interfaces\ITableStatusService;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Console\Command;

class SyncTableStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-table-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ trạng thái bàn nhà hàng từ đặt chỗ và phiên đặt bàn.';

    public function __construct(protected ITableStatusService $tableStatusService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reservationTableCodes = Reservation::query()
            ->whereNotNull('table_code')
            ->pluck('table_code');

        $sessionTableCodes = TableSession::query()
            ->whereNotNull('table_id')
            ->with('table:id,slug')
            ->get()
            ->map(fn($session) => $session->table?->slug)
            ->filter();

        $allTableCodes = $reservationTableCodes
            ->merge($sessionTableCodes)
            ->merge(
                RestaurantTable::query()->pluck('slug')
            )
            ->filter()
            ->unique()
            ->values();

        foreach ($allTableCodes as $tableCode) {
            $this->tableStatusService->syncTableStatus($tableCode);
        }

        $this->info('Đã đồng bộ trạng thái bàn thành công!');

        return self::SUCCESS;
    }
}
