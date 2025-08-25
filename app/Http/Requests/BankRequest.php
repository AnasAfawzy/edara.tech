<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
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
            'name'            => 'required|string|max:255',
            'account_number'  => 'required|string|max:255',
            'currency_id'     => 'required|exists:currencies,id',
            'balance'         => 'required|numeric',
            'status'          => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => __('name.required'),
            'account_number.required' => __('account_number.required'),
            'currency_id.required'    => __('currency_id.required'),
            'currency_id.exists'      => __('currency_id.exists'),
            'balance.required'        => __('balance.required'),
            'balance.numeric'         => __('balance.numeric'),
            'status.required'         => __('status.required'),
            'status.boolean'          => __('status.boolean'),
        ];
    }
}
