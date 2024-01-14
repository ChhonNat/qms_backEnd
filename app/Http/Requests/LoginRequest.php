<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Helpers\Helper;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
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
            "email"=> "required|email|exists:users,email",
            "password"=> "required|min:4",
        ];
    }

    public function failedValidation(Validator $validator){
        // send error message
        Helper::sendError('Email or Password is invalid!', $validator->errors());
    }
}
