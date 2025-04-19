<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
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
            'title' => [
                'required',
                Rule::unique('posts')->ignore($this->post), 
            ],
            'slug' => [
                'required',
                Rule::unique('posts')->ignore($this->post), 
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'description' => 'required',
            'content' => 'required',
            'category_id' => 'required',
        ];
    }
}
