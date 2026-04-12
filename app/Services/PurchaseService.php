<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Rider;
use App\Models\RiderMovement;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function createFromCodes(
        string $riderCode,
        string $productCode,
        int $quantity = 1,
        array $attributes = [],
    ): RiderMovement {
        $rider = Rider::query()
            ->where('rider_id', $riderCode)
            ->first();

        $product = Product::query()
            ->where('code', $productCode)
            ->first();

        $errors = [];

        if (! $rider) {
            $errors['rider_code'] = ['El rider indicado no existe.'];
        }

        if (! $product) {
            $errors['product_code'] = ['El producto indicado no existe.'];
        }

        if ($quantity < 1) {
            $errors['quantity'] = ['La cantidad debe ser un entero mayor o igual a 1.'];
        }

        if ($errors !== []) {
            Log::warning('Purchase creation rejected by domain validation.', [
                'rider_code' => $riderCode,
                'product_code' => $productCode,
                'quantity' => $quantity,
                'errors' => $errors,
            ]);

            throw ValidationException::withMessages($errors);
        }

        $pointsPerUnit = (int) round((float) $product->points_per_box);
        $points = $pointsPerUnit * $quantity;
        $description = $attributes['description']
            ?? ($quantity > 1 ? "{$product->code} {$product->name} x {$quantity}" : "{$product->code} {$product->name}");

        $metadata = array_merge([
            'source' => 'api_purchase',
            'rider_code' => $riderCode,
            'product_code' => $productCode,
            'quantity' => $quantity,
            'points_per_unit' => $pointsPerUnit,
            'points_per_box' => (float) $product->points_per_box,
            'points_per_liter' => (float) $product->points_per_liter,
            'liters_per_unit' => (float) $product->liters,
        ], $attributes['metadata'] ?? []);

        return RiderMovement::query()->create([
            'rider_id' => $rider->getKey(),
            'product_id' => $product->getKey(),
            'uploaded_document_id' => $attributes['uploaded_document_id'] ?? null,
            'movement_type' => $attributes['movement_type'] ?? 'purchase',
            'reference' => $attributes['reference'] ?? null,
            'description' => $description,
            'amount' => $attributes['amount'] ?? null,
            'points' => $points,
            'occurred_at' => $attributes['occurred_at'] ?? now(),
            'metadata' => $metadata,
        ]);
    }
}
