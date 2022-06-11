<?php

namespace App\Http\Requests\User;

use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user();
        return [
            'email' => 'nullable|email',
            'new_password' => [
                'nullable',
                'string',
                'confirmed',
                'min:8',
                'max:28',
                new Password($user->username)
            ]
        ];
    }
}
