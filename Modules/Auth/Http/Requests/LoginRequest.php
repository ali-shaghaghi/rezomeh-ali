<?php

namespace Modules\Auth\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'credential' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get the custom validation messages (Persian).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'ایمیل یا شماره تلفن الزامی است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
        ];
    }

    /**
     * Detect the login type from the credential (email or phone).
     */
    public function loginType(): string
    {
        return filter_var($this->input('credential'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    }

    /**
     * Find the user by email or phone credential.
     */
    public function findUser(): ?User
    {
        return $this->loginType() === 'email'
            ? User::where('email', $this->input('credential'))->first()
            : User::where('phone', $this->input('credential'))->first();
    }
}
