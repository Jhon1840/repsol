<?php

namespace App\Http\Controllers;

use App\Models\Rider;
use App\Models\RiderMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderLookupController extends Controller
{
    public function index(): View
    {
        return view('portal.index');
    }

    public function discountForm(): View
    {
        return view('portal.discount');
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

    public function discount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:1'],
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

        $availablePoints = (int) $rider->points_balance;
        $pointsToDiscount = (int) $validated['points'];

        if ($pointsToDiscount > $availablePoints) {
            return back()
                ->withInput()
                ->withErrors([
                    'points' => 'El rider no tiene suficientes puntos para realizar este descuento.',
                ]);
        }

        RiderMovement::create([
            'rider_id' => $rider->getKey(),
            'movement_type' => 'points_redemption',
            'reference' => 'PORTAL-DISCOUNT',
            'description' => 'Descuento manual de puntos desde el portal.',
            'points' => $pointsToDiscount * -1,
            'occurred_at' => now(),
            'metadata' => [
                'source' => 'portal_points_discount',
                'discounted_points' => $pointsToDiscount,
                'previous_points_balance' => $availablePoints,
                'remaining_points_balance' => $availablePoints - $pointsToDiscount,
            ],
        ]);

        return redirect()
            ->route('portal.discount.form')
            ->with('success', "Se descontaron {$pointsToDiscount} punto(s) al rider {$rider->rider_id}. Saldo restante: " . number_format($availablePoints - $pointsToDiscount) . '.');
    }
}
