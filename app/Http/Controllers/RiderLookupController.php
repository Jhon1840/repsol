<?php

namespace App\Http\Controllers;

use App\Models\Rider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderLookupController extends Controller
{
    public function index(): View
    {
        return view('portal.index');
    }

    public function search(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'string', 'max:255'],
        ]);

        $rider = Rider::query()
            ->withPointsBalance()
            ->where('rider_id', strtoupper(trim($validated['rider_id'])))
            ->first();

        if (! $rider) {
            return back()
                ->withInput()
                ->withErrors([
                    'rider_id' => 'No se encontró un rider con ese ID.',
                ]);
        }

        return redirect()->route('portal.show', $rider);
    }

    public function show(Rider $rider): View
    {
        $rider->load([
            'movements' => fn ($query) => $query->latest('occurred_at')->limit(8),
            'documents' => fn ($query) => $query->latest('uploaded_at')->limit(6),
        ])->loadSum('movements as points_balance', 'points');

        return view('portal.show', [
            'rider' => $rider,
        ]);
    }
}
