<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Helpers\Helper;
use Illuminate\Contracts\Validation\Validator;

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
            "username" => "required",
            "phone_number" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:4",
            "roles" => "required",
            "permissions" => "required|array",
        ];
    }

    public function failedValidation(Validator $validator)
    {
        // send error message
        Helper::sendError('validation error', $validator->errors());
    }
}
