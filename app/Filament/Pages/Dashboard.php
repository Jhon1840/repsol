<?php

namespace App\Filament\Pages;

use App\Models\Rider;
use App\Models\RiderMovement;
use App\Models\UploadedDocument;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends BaseDashboard
{
    use WithFileUploads;
    use WithPagination;

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected string $view = 'filament.pages.dashboard';

    public $pendingPdf = null;

    public function getTitle(): string
    {
        return 'Dashboard';
    }

    public function cancelPdfSelection(): void
    {
        $this->reset('pendingPdf');
        $this->dispatch('pdf-selection-cancelled');
    }

    public function storePdf(): void
    {
        $this->validate([
            'pendingPdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $path = $this->pendingPdf->store('documents/global', 'public');
        $name = $this->pendingPdf->getClientOriginalName();

        UploadedDocument::query()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $name,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $this->pendingPdf->getMimeType(),
            'size' => $this->pendingPdf->getSize(),
            'status' => 'pending_assignment',
            'uploaded_at' => now(),
            'metadata' => [
                'source' => 'dashboard_upload',
                'notes' => 'Carga visual lista para futura automatizacion.',
            ],
        ]);

        $this->reset('pendingPdf');

        Notification::make()
            ->title('PDF cargado')
            ->body("El archivo {$name} quedó registrado para revisión visual.")
            ->success()
            ->send();

        $this->dispatch('pdf-uploaded');
    }

    protected function getViewData(): array
    {
        return [
            'totalRiders' => Rider::query()->count(),
            'totalPoints' => (int) RiderMovement::query()->sum('points'),
            'riders' => Rider::query()
                ->withPointsBalance()
                ->orderBy('name')
                ->paginate(10),
            'recentDocuments' => UploadedDocument::query()
                ->latest('uploaded_at')
                ->limit(5)
                ->get(),
        ];
    }
}
