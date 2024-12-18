<?php

namespace App\Filament\Resources;

use App\Filament\Imports\ChargeRangeImporter;
use App\Filament\Resources\ChargeRangeResource\Pages;
use App\Filament\Resources\ChargeRangeResource\RelationManagers;
use App\Models\ChargeRange;
use App\Models\TransactionType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ChargeRangeResource extends Resource
{
    protected static ?string $model = ChargeRange::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Transaction Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['transactionType.name', 'min_amount', 'max_amount'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('approval_status', 'pending_finance')
            ->orWhere('approval_status', 'pending_ceo')
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('approval_status', 'pending_finance')
            ->orWhere('approval_status', 'pending_ceo')
            ->exists() ? 'warning' : 'primary';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('transaction_type_id')
                            ->label('Transaction Type')
                            ->options(TransactionType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Section::make('Amount Range')
                            ->schema([
                                Forms\Components\TextInput::make('min_amount')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                                Forms\Components\TextInput::make('max_amount')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01),
                            ])->columns(2),

                        Forms\Components\Section::make('Service Charge')
                            ->schema([
                                Forms\Components\Select::make('charge_type')
                                    ->label('Charge Type')
                                    ->options([
                                        'flat' => 'Flat Rate Only',
                                        'percentage' => 'Percentage Only',
                                        'both' => 'Both Flat and Percentage'
                                    ])
                                    ->required()
                                    ->reactive(),

                                Forms\Components\TextInput::make('flat_charge_amount')
                                    ->label('Flat Charge Amount')
                                    ->prefix('TZS')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(fn (callable $get) =>
                                    in_array($get('charge_type'), ['flat', 'both']))
                                    ->hidden(fn (callable $get) =>
                                        $get('charge_type') === 'percentage'),

                                Forms\Components\TextInput::make('percentage_charge_amount')
                                    ->label('Percentage Charge (%)')
                                    ->suffix('%')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->required(fn (callable $get) =>
                                    in_array($get('charge_type'), ['percentage', 'both']))
                                    ->hidden(fn (callable $get) =>
                                        $get('charge_type') === 'flat'),
                            ])->columns(2),

                        Forms\Components\Section::make('Government Tax')
                            ->schema([
                                Forms\Components\Select::make('tax_type')
                                    ->label('Tax Type')
                                    ->options([
                                        'flat' => 'Flat Rate Only',
                                        'percentage' => 'Percentage Only',
                                        'both' => 'Both Flat and Percentage'
                                    ])
                                    ->required()
                                    ->reactive(),

                                Forms\Components\TextInput::make('flat_tax_amount')
                                    ->label('Flat Tax Amount')
                                    ->prefix('TZS')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(fn (callable $get) =>
                                    in_array($get('tax_type'), ['flat', 'both']))
                                    ->hidden(fn (callable $get) =>
                                        $get('tax_type') === 'percentage'),

                                Forms\Components\TextInput::make('percentage_tax_amount')
                                    ->label('Percentage Tax (%)')
                                    ->suffix('%')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->required(fn (callable $get) =>
                                    in_array($get('tax_type'), ['percentage', 'both']))
                                    ->hidden(fn (callable $get) =>
                                        $get('tax_type') === 'flat'),
                            ])->columns(2),
                    ])
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
//                    ->sortDescending(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->disabled(fn (ChargeRange $record): bool => !$record->isApproved()),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->colors([
                        'success' => 'approved',
                        'primary' => ['pending_finance', 'pending_ceo'],
                        'warning' => 'draft',
                        'danger'  => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('tax_type')
                    ->label('Government Tax')
                    ->formatStateUsing(function (ChargeRange $record): string {
                        switch ($record->tax_type) {
                            case 'both':
                                return sprintf(
                                    "TZS %s + %s%%",
                                    number_format($record->flat_tax_amount, 2),
                                    number_format($record->percentage_tax_amount, 2)
                                );
                            case 'flat':
                                return 'TZS ' . number_format($record->flat_tax_amount, 2);
                            case 'percentage':
                                return number_format($record->percentage_tax_amount, 2) . '%';
                            default:
                                return '';
                        }
                    }),

                Tables\Columns\TextColumn::make('charge_type')
                    ->label('Service Charge')
                    ->formatStateUsing(function (ChargeRange $record): string {
                        switch ($record->charge_type) {
                            case 'both':
                                return sprintf(
                                    "TZS %s + %s%%",
                                    number_format($record->flat_charge_amount, 2),
                                    number_format($record->percentage_charge_amount, 2)
                                );
                            case 'flat':
                                return 'TZS ' . number_format($record->flat_charge_amount, 2);
                            case 'percentage':
                                return number_format($record->percentage_charge_amount, 2) . '%';
                            default:
                                return '';
                        }
                    }),

                Tables\Columns\TextColumn::make('min_amount')
                    ->label('Amount Range')
                    ->formatStateUsing(fn (ChargeRange $record): string =>
                    sprintf(
                        "%s - %s",
                        number_format($record->min_amount, 2),
                        number_format($record->max_amount, 2)
                    )
                    )
                    ->prefix('TZS '),

                Tables\Columns\TextColumn::make('transactionType.name')
                    ->label('Transaction Type')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (ChargeRange $record) => $record->isDraft()),

                    Tables\Actions\Action::make('submit')
                        ->label('Submit for Approval')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(fn (ChargeRange $record) => $record->submitForFinanceApproval(Auth::id()))
                        ->requiresConfirmation()
                        ->visible(fn (ChargeRange $record) =>
                            Auth::user()->can('submit_for_approval') &&
                            $record->isDraft()
                        ),

                    Tables\Actions\Action::make('finance_approve')
                        ->label('Approve (Finance)')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn (ChargeRange $record) => $record->approveByFinance(Auth::id()))
                        ->requiresConfirmation()
                        ->visible(fn (ChargeRange $record) =>
                            Auth::user()->can('approve_finance') &&
                            $record->isPendingFinance()
                        ),

                    Tables\Actions\Action::make('ceo_approve')
                        ->label('Approve (CEO)')
                        ->icon('heroicon-o-shield-check')
                        ->color('success')
                        ->action(fn (ChargeRange $record) => $record->approveByCEO(Auth::id()))
                        ->requiresConfirmation()
                        ->visible(fn (ChargeRange $record) =>
                            Auth::user()->can('approve_ceo') &&
                            $record->isPendingCEO()
                        ),

                    Tables\Actions\Action::make('reject')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->required()
                                ->label('Reason for Rejection')
                        ])
                        ->action(function (ChargeRange $record, array $data) {
                            $record->reject($data['rejection_reason'], Auth::id());
                        })
                        ->requiresConfirmation()
                        ->visible(fn (ChargeRange $record) =>
                            (Auth::user()->can('approve_finance') || Auth::user()->can('approve_ceo')) &&
                            in_array($record->approval_status, ['pending_finance', 'pending_ceo'])
                        ),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'approved'       => 'Approved',
                        'pending_ceo'    => 'Pending CEO',
                        'pending_finance' => 'Pending Finance',
                        'draft'          => 'Draft',
                        'rejected'       => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('transaction_type_id')
                    ->label('Transaction Type')
                    ->options(TransactionType::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => Auth::user()->can('delete_charge_range')),
            ]);
    }

    public static function infolist( $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('transactionType.name')
                            ->label('Transaction Type')
                            ->weight('bold')
                            ->columnSpan(['sm' => 2]),

                        \Filament\Infolists\Components\TextEntry::make('amount_range')
                            ->label('Amount Range')
                            ->state(function (ChargeRange $record): string {
                                return sprintf(
                                    'TZS %s - TZS %s',
                                    number_format($record->min_amount, 2),
                                    number_format($record->max_amount, 2)
                                );
                            })
                            ->columnSpan(['sm' => 2]),
                    ])
                    ->columns(['sm' => 4]),

                \Filament\Infolists\Components\Section::make('Service Charge Details')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('charge_type')
                            ->label('Charge Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => ucwords($state))
                            ->color('success'),

                        \Filament\Infolists\Components\Grid::make(['default' => 2])
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('flat_charge_amount')
                                    ->label('Flat Charge')
                                    ->formatStateUsing(fn ($state) => 'TZS ' . number_format($state, 2))
                                    ->visible(fn (ChargeRange $record) =>
                                    in_array($record->charge_type, ['flat', 'both'])),

                                \Filament\Infolists\Components\TextEntry::make('percentage_charge_amount')
                                    ->label('Percentage Charge')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->visible(fn (ChargeRange $record) =>
                                    in_array($record->charge_type, ['percentage', 'both'])),
                            ]),

                        \Filament\Infolists\Components\TextEntry::make('charge_calculation_example')
                            ->label('Example Calculation')
                            ->state(function (ChargeRange $record): string {
                                $sampleAmount = ($record->min_amount + $record->max_amount) / 2;
                                $charges = $record->calculateCharges($sampleAmount);
                                return sprintf(
                                    "For amount TZS %s:\nService Charge: TZS %s\nTotal: TZS %s",
                                    number_format($sampleAmount, 2),
                                    number_format($charges['service_charge']['total'], 2),
                                    number_format($charges['amount'] + $charges['service_charge']['total'], 2)
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                \Filament\Infolists\Components\Section::make('Government Tax Details')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('tax_type')
                            ->label('Tax Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => ucwords($state))
                            ->color('warning'),

                        \Filament\Infolists\Components\Grid::make(['default' => 2])
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('flat_tax_amount')
                                    ->label('Flat Tax')
                                    ->formatStateUsing(fn ($state) => 'TZS ' . number_format($state, 2))
                                    ->visible(fn (ChargeRange $record) =>
                                    in_array($record->tax_type, ['flat', 'both'])),

                                \Filament\Infolists\Components\TextEntry::make('percentage_tax_amount')
                                    ->label('Percentage Tax')
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->visible(fn (ChargeRange $record) =>
                                    in_array($record->tax_type, ['percentage', 'both'])),
                            ]),

                        \Filament\Infolists\Components\TextEntry::make('tax_calculation_example')
                            ->label('Example Calculation')
                            ->state(function (ChargeRange $record): string {
                                $sampleAmount = ($record->min_amount + $record->max_amount) / 2;
                                $charges = $record->calculateCharges($sampleAmount);
                                return sprintf(
                                    "For amount TZS %s:\nGovernment Tax: TZS %s\nTotal with Tax: TZS %s",
                                    number_format($sampleAmount, 2),
                                    number_format($charges['government_tax']['total'], 2),
                                    number_format($charges['total_amount'], 2)
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                \Filament\Infolists\Components\Section::make('Status Information')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('approval_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'draft' => 'gray',
                                default => 'warning',
                            }),

                        \Filament\Infolists\Components\TextEntry::make('rejection_reason')
                            ->visible(fn (ChargeRange $record) => $record->isRejected())
                            ->columnSpanFull(),

                        \Filament\Infolists\Components\IconEntry::make('is_active')
                            ->label('Active Status')
                            ->boolean(),
                    ])
                    ->columnSpan(1),

                \Filament\Infolists\Components\Section::make('Audit Trail')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('creator.name')
                            ->label('Created By'),

                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y H:i:s'),

                        \Filament\Infolists\Components\TextEntry::make('financeApprover.name')
                            ->label('Finance Approved By')
                            ->visible(fn (ChargeRange $record) => $record->finance_approved_by !== null),

                        \Filament\Infolists\Components\TextEntry::make('finance_approved_at')
                            ->label('Finance Approved At')
                            ->dateTime('M d, Y H:i:s')
                            ->visible(fn (ChargeRange $record) => $record->finance_approved_at !== null),

                        \Filament\Infolists\Components\TextEntry::make('ceoApprover.name')
                            ->label('CEO Approved By')
                            ->visible(fn (ChargeRange $record) => $record->ceo_approved_by !== null),

                        \Filament\Infolists\Components\TextEntry::make('ceo_approved_at')
                            ->label('CEO Approved At')
                            ->dateTime('M d, Y H:i:s')
                            ->visible(fn (ChargeRange $record) => $record->ceo_approved_at !== null),
                    ])
                    ->columns(2),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChargeRanges::route('/'),
            'create' => Pages\CreateChargeRange::route('/create'),
            'edit' => Pages\EditChargeRange::route('/{record}/edit'),
        ];
    }


}
