<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:chat_users,username',
                'regex:/^[a-zA-Z0-9_]+$/', // Only alphanumeric and underscore
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                'unique:chat_users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:chat_users,phone',
                'regex:/^[0-9+\-\s()]+$/', // Phone number format
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed', // Requires password_confirmation field
            ],
            'full_name' => [
                'required',
                'string',
                'max:100',
            ],
            'gender' => [
                'nullable',
                'in:male,female,other',
            ],
            'birthday' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01',
            ],
            'country_code' => [
                'nullable',
                'string',
                'size:2',
            ],
            'language' => [
                'nullable',
                'string',
                'in:vi,en',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'username.regex' => 'Username can only contain letters, numbers, and underscores',
            'username.min' => 'Username must be at least 3 characters',
            'username.max' => 'Username cannot exceed 50 characters',

            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',

            'phone.unique' => 'This phone number is already registered',
            'phone.regex' => 'Please provide a valid phone number',

            'password.required' => 'Password is required',
            'password.confirmed' => 'Password confirmation does not match',

            'full_name.required' => 'Full name is required',

            'gender.in' => 'Gender must be male, female, or other',

            'birthday.date' => 'Please provide a valid date',
            'birthday.before' => 'Birthday must be before today',

            'language.in' => 'Language must be vi or en',
        ];
    }

    /**
     * Custom validation logic
     */
    protected function prepareForValidation(): void
    {
        // Ensure at least email or phone is provided
        $this->merge([
            'username' => strtolower($this->username),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Ensure at least email or phone is provided
            if (!$this->email && !$this->phone) {
                $validator->errors()->add(
                    'contact',
                    'Either email or phone number is required'
                );
            }
        });
    }
}
