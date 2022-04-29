<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class CheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (auth('api')->check()) {
            return true;
        }

        return false;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'               => 'sometimes|required|numeric|exists:balances,id,deleted_at,NULL',
            'description'      => 'required|min:2|max:100',
            'amount'           => "required|regex:/^\d+(\.\d{1,2})?$/|not_in:0",
            'image_path'       => 'required'
        ];
    }
}
