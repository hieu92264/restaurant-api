<?php

namespace App\Http\services;

use App\Http\interfaces\ITableStatusService;

class TableStatusService implements ITableStatusService
{
    public function syncTableStatus(string $tableCode): void
    {
        // Table status is now derived dynamically from live sessions and holding reservations.
        // This method remains as a no-op for backward compatibility.
    }
}
