<?php

namespace App\Http\Requests\Profile;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountInfoRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pseudo' => $this->pseudoRules($this->user()->id),
            'email' => $this->emailRules($this->user()->id),
        ];
    }

    public function messages(): array
    {
        return $this->profileValidationMessages();
    }
}
