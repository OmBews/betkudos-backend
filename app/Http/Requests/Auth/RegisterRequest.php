<?php

namespace App\Http\Requests\Auth;

use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'bail|required|alpha_num|unique:users|min:6|max:14',
            'password' => ['required', 'string', 'min:8', 'max:28', new Password($this->username)],
            'email' => 'required|email|unique:users',
            'product' => 'required|string|in:mobile,desktop'
        ];
    }
}
