<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CateogryRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:categories,name',
            'photo' => 'nullable|image|max:2048', // Required, must be an image, max size 2MB
        ];
    }
}
