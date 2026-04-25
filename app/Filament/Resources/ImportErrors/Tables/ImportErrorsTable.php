<?php

namespace App\Filament\Resources\ImportErrors\Tables;

use App\Filament\Resources\ImportErrors\ImportErrorResource;
use App\Filament\Resources\Riders\RiderResource;
use App\Models\UploadedDocument;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

class ImportErrorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')
                    ->label('Archivo')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state, UploadedDocument $record): string => $record->statusColor())
                    ->formatStateUsing(fn (string $state, UploadedDocument $record): string => $record->statusLabel())
                    ->sortable(),
                TextColumn::make('branch_summary')
                    ->label('Sucursal')
                    ->state(fn (UploadedDocument $record): string => $record->branchLabel())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->whereHas('rider', fn (Builder $query): Builder => $query->where('branch', 'like', "%{$search}%"))
                                ->orWhere('metadata->branch_scope', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('rider_summary')
                    ->label('Rider')
                    ->state(function (UploadedDocument $record): string {
                        if ($record->rider) {
                            return "{$record->rider->rider_id} - {$record->rider->name}";
                        }

                        return data_get($record->metadata, 'parsed_riders.0.rider_id')
                            ?? data_get($record->metadata, 'skipped_items.0.rider_id')
                            ?? 'Sin rider vinculado';
                    })
                    ->searchable(false)
                    ->wrap(),
                TextColumn::make('uploader.name')
                    ->label('Subido por')
                    ->placeholder('Sistema')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uploaded_at')
                    ->label('Fecha de carga')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('error_count')
                    ->label('Errores')
                    ->state(fn (UploadedDocument $record): int => $record->importErrorCount())
                    ->badge()
                    ->color('danger')
                    ->sortable(false),
                TextColumn::make('metadata.notes')
                    ->label('Detalle')
                    ->placeholder('Sin observaciones')
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'processed_with_errors' => 'Procesado con errores',
                        'processed_without_points' => 'Sin puntos procesables',
                        'failed' => 'Fallido',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver detalle'),
                Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (UploadedDocument $record): string => URL::signedRoute('documents.download', ['document' => $record])),
                Action::make('viewRider')
                    ->label('Ver rider')
                    ->icon('heroicon-o-user')
                    ->visible(fn (UploadedDocument $record): bool => $record->rider !== null)
                    ->url(fn (UploadedDocument $record): string => RiderResource::getUrl('view', ['record' => $record->rider])),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
