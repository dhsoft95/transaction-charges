<?php

namespace App\Filament\Imports;

use App\Models\ChargeRange;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ChargeRangeImporter extends Importer
{
    protected static ?string $model = ChargeRange::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('transactionType')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('min_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('max_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('charge_type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('flat_charge_amount')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('percentage_charge_amount')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('tax_type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('flat_tax_amount')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('percentage_tax_amount')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('is_active')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?ChargeRange
    {
        // return ChargeRange::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new ChargeRange();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your charge range import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
