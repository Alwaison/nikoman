<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class CreateMemberRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
        ];
    }

    public function name(): string
    {
        return (string) $this->validated('name');
    }

    public function email(): string
    {
        return (string) $this->validated('email');
    }
}
