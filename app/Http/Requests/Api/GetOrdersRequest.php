<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetOrdersRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'merchant_id' => ['required', 'integer', 'exists:merchants,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'merchant_id.required' => 'Merchant ID обов’язковий.',
            'merchant_id.exists' => 'Merchant не знайдено.',
            'amount.required' => 'Сума обов’язкова.',
            'amount.numeric' => 'Сума повинна бути числом.',
            'amount.gt' => 'Сума повинна бути більшою за 0.',
            'currency.required' => 'Валюта обов’язкова.',
            'currency.size' => 'Валюта повинна містити 3 символи.',
        ];
    }
}
