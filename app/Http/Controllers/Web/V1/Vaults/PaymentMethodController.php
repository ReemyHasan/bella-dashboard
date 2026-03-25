<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Routing\Controllers\HasMiddleware;


class PaymentMethodController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [];
    }

    public function selectAvailable()
    {
        $paymentMethods = PaymentMethod::all();
        $returnedData = $paymentMethods->map(fn($paymentMethod) => [
            'key' => $paymentMethod?->id,
            'value' => $paymentMethod?->name_ar . '-' . $paymentMethod?->name_en
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
