<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['post_office', 'male_address', 'shop'])],
            'destination_island_id' => ['required', 'integer', 'exists:islands,id'],
            'transport_provider_id' => ['nullable', 'integer', 'exists:transport_providers,id'],
            'boat_schedule_id' => ['nullable', 'integer', 'exists:boat_schedules,id'],
            'weight_kg' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'order_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'address' => ['nullable', 'array'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'shop_address' => ['nullable', 'array'],
            'quote_copy' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'items' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $type = $this->input('type');

            if ($type === 'post_office') {
                if (! $this->filled('tracking_number')) {
                    $validator->errors()->add('tracking_number', 'A tracking number is required for post office pickups.');
                }

                if (! $this->hasFile('order_image')) {
                    $validator->errors()->add('order_image', 'An order image is required for post office pickups.');
                }
            }

            if ($type === 'male_address') {
                foreach (['address', 'contact_name', 'contact_phone'] as $field) {
                    if (! $this->filled($field) && ! is_array($this->input($field))) {
                        $validator->errors()->add($field, 'This field is required for Malé address pickups.');
                    }
                }
            }

            if ($type === 'shop') {
                foreach (['shop_address', 'contact_name', 'contact_phone'] as $field) {
                    if (! $this->filled($field) && ! is_array($this->input($field))) {
                        $validator->errors()->add($field, 'This field is required for shop pickups.');
                    }
                }

                $hasQuote = $this->hasFile('quote_copy');
                $hasItems = is_array($this->input('items')) && count($this->input('items')) > 0;

                if ($hasQuote === $hasItems) {
                    $validator->errors()->add('quote_copy', 'Provide either a quote copy or a list of items for shop pickups.');
                }
            }
        }];
    }
}
