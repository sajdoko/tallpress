<?php

namespace Sajdoko\TallPress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by PostPolicy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tallpress_posts', 'slug')],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'featured_image' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Accept either an uploaded file or a URL string
                    if (is_string($value)) {
                        // It's a URL - validate it
                        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                            $fail('The featured image must be a valid URL.');

                            return;
                        }
                        // Check if it ends with a valid image extension
                        if (! preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $value)) {
                            $fail('The featured image URL must point to a valid image file (jpg, jpeg, png, gif, webp, svg).');

                            return;
                        }
                    } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                        // It's an uploaded file - validate it
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            [$attribute => $value],
                            [
                                $attribute => [
                                    'image',
                                    'max:'.tallpress_setting('images_max_size', 2048),
                                    'mimes:'.implode(',', config('tallpress.images.allowed_mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp'])),
                                ],
                            ]
                        );
                        if ($validator->fails()) {
                            $fail($validator->errors()->first($attribute));

                            return;
                        }
                    } elseif ($value !== null) {
                        $fail('The featured image must be either an uploaded file or a URL.');
                    }
                },
            ],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:tallpress_categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tallpress_tags,id'],
            'meta' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('tallpress::validation.title.required'),
            'body.required' => __('tallpress::validation.body.required'),
            'status.required' => __('tallpress::validation.status.required'),
        ];
    }
}
