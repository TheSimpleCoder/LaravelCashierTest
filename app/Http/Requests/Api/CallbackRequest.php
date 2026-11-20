<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $secret = config('services.payment.webhook_secret');
        $signature = $this->header('X-Signature');

        if (!$secret || !$signature) {
            return false;
        }

        $expected = hash_hmac(
            'sha256',
            $this->getContent(),
            $secret
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string', 'max:255'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'status' => ['required', 'in:paid,failed,refunded'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'Event ID is required.',
            'order_id.required' => 'Order ID is required.',
            'order_id.exists' => 'Order not found.',
            'status.in' => 'Invalid payment status.',
            'amount.gt' => 'Amount must be greater than zero.',
            'currency.size' => 'Currency must contain 3 characters.',
        ];
    }
}
