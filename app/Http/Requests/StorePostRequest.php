<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    public static array $extensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'mp3', 'wav', 'mp4',
        "doc", "docx", "pdf", "csv", "xls", "xlsx",
        "zip"
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'roles' => ['nullable', 'array'],
            'roles.*.name' => ['required_with:roles', 'string', 'max:255'],
            'roles.*.quantity' => ['required_with:roles', 'integer', 'min:1'],

            'body' => ['nullable', 'string'],
            'preview' => ['nullable', 'array'],
            'preview_url' => ['nullable', 'string'],

            'attachments' => [
                'array',
                'max:50',
                function ($attribute, $value, $fail) {
                    $totalSize = collect($value)->sum(fn(UploadedFile $file) => $file->getSize());
                    if ($totalSize > 1 * 1024 * 1024 * 1024) {
                        $fail('The total size of all files must not exceed 1GB.');
                    }
                },
            ],
            'attachments.*' => [
                'file',
                File::types(self::$extensions),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $body = $this->input('body') ?: '';
        $previewUrl = $this->input('preview_url') ?: '';

        $trimmedBody = trim(strip_tags($body));
        if ($trimmedBody === $previewUrl) {
            $body = '';
        }

        $this->merge([
            'user_id' => auth()->id(),
            'body' => $body,
        ]);
    }

    public function messages()
    {
        return [
            'attachments.*.file' => 'Each attachment must be a file.',
            'attachments.*.mimes' => 'Invalid file type for attachments.',
        ];
    }
}
