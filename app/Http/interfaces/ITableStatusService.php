<?php

namespace App\Http\interfaces;

interface ITableStatusService
{
    public function syncTableStatus(string $tableCode): void;
}
