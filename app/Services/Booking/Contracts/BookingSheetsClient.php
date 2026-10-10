<?php

declare(strict_types=1);

namespace App\Services\Booking\Contracts;

interface BookingSheetsClient
{
    /**
     * @return list<string>
     */
    public function readHeader(string $spreadsheetId, string $range): array;

    /**
     * @return list<string>
     */
    public function readReferences(string $spreadsheetId, string $range): array;

    /**
     * @param  list<string>  $row
     */
    public function appendRow(string $spreadsheetId, string $range, array $row): void;
}
