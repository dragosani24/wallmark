<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize comma-separated tags from forms into an array.
     */
    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');

        if (is_string($tags)) {
            $names = array_values(array_filter(array_map(trim(...), explode(',', $tags)), fn (string $name): bool => $name !== ''));
            $this->merge(['tags' => $names]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:2', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['string', 'max:40'],
        ];
    }
}
