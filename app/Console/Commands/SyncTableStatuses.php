<?php

namespace App\Console\Commands;

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
    protected $description = 'Trạng thái bàn hiện được tính động từ reservation và table session.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Không cần sync. Trạng thái bàn đang được suy ra trực tiếp từ table session và reservation.');

        return self::SUCCESS;
    }
}
