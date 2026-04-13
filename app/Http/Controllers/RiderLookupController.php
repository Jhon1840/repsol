<?php

namespace App\Http\Controllers;

use App\Models\Articulos;
use App\Models\Rider;
use App\Models\RiderMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RiderLookupController extends Controller
{
    public function discountForm(Request $request): View|RedirectResponse
    {
        $rider = null;
        $riderId = trim($request->string('rider_id')->toString());

        if (filled($riderId)) {
            $rider = Rider::query()
                ->visibleTo(auth()->user())
                ->withPointsBalance(auth()->user())
                ->where('rider_id', strtoupper($riderId))
                ->first();

            if (! $rider) {
                return redirect()
                    ->route('portal.discount.form')
                    ->withInput($request->only('rider_id'))
                    ->withErrors([
                        'rider_id' => 'No se encontró un rider con ese ID.',
                    ]);
            }
        }

        return view('portal.discount', [
            'articulos' => Articulos::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'rider' => $rider,
        ]);
    }

    public function discount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rider_id' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:1'],
            'articulos' => ['required', 'array', 'min:1'],
            'articulos.*' => ['integer', 'min:1'],
        ]);

        $rider = Rider::query()
            ->visibleTo(auth()->user())
            ->withPointsBalance(auth()->user())
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
        $selectedArticleQuantities = collect($validated['articulos'])
            ->mapWithKeys(fn (mixed $quantity, string | int $id): array => [(int) $id => (int) $quantity])
            ->filter(fn (int $quantity, int $id): bool => $id > 0 && $quantity > 0);

        $selectedArticleIds = $selectedArticleQuantities
            ->keys()
            ->values();

        $selectedArticles = Articulos::query()
            ->whereIn('id', $selectedArticleIds)
            ->get(['id', 'nombre']);

        $selectedArticleDetails = $selectedArticleIds
            ->map(function (int $id) use ($selectedArticles, $selectedArticleQuantities): ?array {
                $article = $selectedArticles->firstWhere('id', $id);

                if (! $article) {
                    return null;
                }

                return [
                    'id' => $id,
                    'nombre' => $article->nombre,
                    'quantity' => $selectedArticleQuantities->get($id),
                ];
            })
            ->filter()
            ->values();

        if ($selectedArticleDetails->isEmpty() || $selectedArticleDetails->count() !== $selectedArticleIds->count()) {
            return back()
                ->withInput()
                ->withErrors([
                    'articulos' => 'Debes seleccionar al menos un artículo válido.',
                ]);
        }

        $redemptionDescription = $selectedArticleDetails
            ->map(fn (array $article): string => "{$article['quantity']} x {$article['nombre']}")
            ->implode(', ');

        if ($pointsToDiscount > $availablePoints) {
            return back()
                ->withInput()
                ->withErrors([
                    'points' => 'El rider no tiene suficientes puntos para realizar este descuento.',
                ]);
        }

        RiderMovement::create([
            'rider_id' => $rider->getKey(),
            'user_id' => auth()->id(),
            'branch' => auth()->user()?->branchScope() ?? $rider->branch,
            'movement_type' => 'points_redemption',
            'reference' => 'PORTAL-DISCOUNT',
            'description' => "Canje de puntos: {$redemptionDescription}",
            'points' => $pointsToDiscount * -1,
            'occurred_at' => now(),
            'metadata' => [
                'source' => 'portal_points_discount',
                'actor_type' => 'user',
                'discounted_points' => $pointsToDiscount,
                'selected_article_ids' => $selectedArticleIds->all(),
                'selected_article_names' => $selectedArticleDetails->pluck('nombre')->all(),
                'selected_article_quantities' => $selectedArticleDetails
                    ->mapWithKeys(fn (array $article): array => [$article['id'] => $article['quantity']])
                    ->all(),
                'selected_articles' => $selectedArticleDetails->all(),
                'previous_points_balance' => $availablePoints,
                'remaining_points_balance' => $availablePoints - $pointsToDiscount,
            ],
        ]);

        return redirect()
            ->to($this->discountRedirectUrl($request))
            ->with('success', "Se descontaron {$pointsToDiscount} punto(s) al rider {$rider->rider_id} por: {$redemptionDescription}. Saldo restante: " . number_format($availablePoints - $pointsToDiscount) . '.');
    }

    protected function discountRedirectUrl(Request $request): string
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if (blank($redirectTo)) {
            return route('portal.discount.form', [
                'rider_id' => $request->string('rider_id')->toString(),
            ]);
        }

        if (Str::startsWith($redirectTo, ['/'])) {
            return $redirectTo;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $targetHost = parse_url($redirectTo, PHP_URL_HOST);

        if ($targetHost !== null && $targetHost === $appHost) {
            return $redirectTo;
        }

        return route('portal.discount.form');
    }
}
