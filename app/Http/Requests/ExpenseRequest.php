<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            'amount'           => "required|regex:/^\d+(\.\d{1,2})?$/|not_in:0",
            'description'      => 'required|min:2|max:100',
            'date'             => 'required|date_format:Y-m-d',
        ];
    }
}
