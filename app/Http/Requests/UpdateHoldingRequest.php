<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHoldingRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cryptocurrency_id' => ['required', 'integer', 'exists:cryptocurrencies,id'],
            'platform_id' => [
                'required',
                'integer',
                'exists:platforms,id',
                Rule::unique('holdings')
                    ->where('cryptocurrency_id', $this->input('cryptocurrency_id'))
                    ->ignore($this->route('holding')),
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'platform_id.unique' => 'This cryptocurrency already has a holding on that platform. Edit it instead.',
        ];
    }
}
