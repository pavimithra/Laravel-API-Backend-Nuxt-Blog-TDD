<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'name' => [
                'nullable',
                Rule::unique('categories')->ignore($this->category), 
            ],
            'slug' => [
                'nullable',
                Rule::unique('categories')->ignore($this->category), 
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ];
    }
}
