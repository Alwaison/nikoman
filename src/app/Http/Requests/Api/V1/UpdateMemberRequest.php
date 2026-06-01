<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateMemberRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower((string) $this->input('email'))]);
        }
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        /** @var string $memberId */
        $memberId = $this->route('memberId');

        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:members,email,{$memberId},id"],
        ];
    }
}
