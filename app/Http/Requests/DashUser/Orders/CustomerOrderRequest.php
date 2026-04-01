<?php

namespace App\Http\Requests\DashUser\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'customer_id' => ['required', 'exists:customers,id'],

            'address_id' => [
                'nullable',
                Rule::exists('customer_addresses', 'address_id')
                    ->where(function ($query) {
                        $query->where('customer_id', $this->customer_id);
                    })
            ],
            'address_details' => ['nullable', 'string'],

            'customer_mobile' => ['required', 'string', 'max:20'],

            'app_user_id' => ['nullable', Rule::exists('app_users', 'id')],
            'warehouse_man_id' => ['nullable', Rule::exists('warehouses', 'keeper_id')
                ->where(fn($q) => $q->where('id', $this->warehouse_id)),],
            'warehouse_man_additional_cost' => ['nullable', 'numeric', 'min:0'],

            'warehouse_id' => ['required', 'exists:warehouses,id'],

            'additional_tips' => ['nullable', 'numeric', 'min:0'],

            'deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'deduction_type' => ['nullable', 'in:fixed,percentage'],

            'notes' => ['nullable', 'string'],

            // PRODUCTS
            'products' => ['nullable', 'array'],
            'products.*.product_id' => [
                'required',
                Rule::exists('product_warehouses', 'product_id')
                    ->where(fn($q) => $q->where('warehouse_id', $this->warehouse_id)),
            ],
            'products.*.quantity' => ['required', 'integer', 'min:1'],

            // OFFERS
            'offers' => ['nullable', 'array'],
            'offers.*.offer_id' => [
                'required',
                Rule::exists('offer_warehouses', 'offer_id')
                    ->where(fn($q) => $q->where('warehouse_id', $this->warehouse_id)),
            ],
            'offers.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (!$this->address_id && !$this->address_details) {
                $validator->errors()->add('address', 'يجب إدخال عنوان أو تفاصيل العنوان.');
            }

            if (!$this->warehouse_id) {
                return;
            }

            $productIds = collect($this->products ?? [])
                ->pluck('product_id')
                ->filter()
                ->unique();

            $offerIds = collect($this->offers ?? [])
                ->pluck('offer_id')
                ->filter()
                ->unique();

            // fetch warehouse stock
            $warehouseProducts = \App\Models\ProductWarehouse::where('warehouse_id', $this->warehouse_id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            $warehouseOffers = \App\Models\OfferWarehouse::where('warehouse_id', $this->warehouse_id)
                ->whereIn('offer_id', $offerIds)
                ->get()
                ->keyBy('offer_id');

            // If updating, fetch current order
            $currentOrder = $this->route('customer_order'); // make sure route model binding
            $currentProducts = $currentOrder?->products?->keyBy('product_id') ?? collect();
            $currentOffers = $currentOrder?->offers?->keyBy('offer_id') ?? collect();

            // -----------------------------
            // PRODUCTS VALIDATION
            // -----------------------------
            foreach ($this->products ?? [] as $index => $product) {

                $warehouseProduct = $warehouseProducts->get($product['product_id']);
                if (!$warehouseProduct) continue;

                // include current order reserved quantity for update
                $currentQty = $currentProducts->get($product['product_id'])->quantity ?? 0;

                $available = $warehouseProduct->quantity - $warehouseProduct->reserved_quantity + $currentQty;

                if ($product['quantity'] > $available) {
                    $validator->errors()->add(
                        "products.$index.quantity",
                        "الكمية المطلوبة غير متوفرة. المتاح: {$available}"
                    );
                }
            }

            // -----------------------------
            // OFFERS VALIDATION
            // -----------------------------
            // foreach ($this->offers ?? [] as $index => $offer) {

            //     $warehouseOffer = $warehouseOffers->get($offer['offer_id']);
            //     if (!$warehouseOffer) continue;

            //     $currentQty = $currentOffers->get($offer['offer_id'])->quantity ?? 0;

            //     $available = $warehouseOffer->quantity - $warehouseOffer->reserved_quantity + $currentQty;


            //     if ($offer['quantity'] > $available) {
            //         $validator->errors()->add(
            //             "offers.$index.quantity",
            //             "الكمية المطلوبة من العرض غير متوفرة. المتاح: {$available}"
            //         );
            //     }
            // }
        });
    }
  
    public function attributes(): array
    {
        return [
            'order_number' => 'رقم الطلب',
            'customer_id' => 'الزبون',
            'address_id' => 'العنوان',
            'address_details' => 'تفاصيل العنوان',
            'customer_mobile' => 'رقم موبايل الزبون',
            'app_user_id' => 'المسوق',
            'warehouse_man_id' => 'الموزع',
            'delivery_cost' => 'تكلفة التوصيل',
            'delivery_additional_cost' => 'تكلفة توصيل إضافية',
            'teamleader_id' => 'قائد الفريق',
            'manager_id' => 'المدير',
            'warehouse_id' => 'المستودع',
            'total_base_price' => 'السعر قبل الخصم أو الإضافة',
            'total_price' => 'السعر الكلي',
            'additional_tips' => 'الإضافة',
            'deduction_amount' => 'الخصم',
            'deduction_type' => 'نوع الخصم',
            'notes' => 'ملاحظات',


            'products' => 'المنتجات',
            'products.*.product_id' => 'المنتج',
            'products.*.quantity' => 'الكمية',

            // OFFERS
            'offers' => 'العروض',
            'offers.*.offer_id' => 'العرض',
            'offers.*.quantity' => 'الكمية',


        ];
    }
}
