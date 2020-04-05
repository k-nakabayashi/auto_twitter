<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            //アプリアカウント系
            // 'name' => '',
            // 'email' => '',
            // 'company' => '',
            // 'password' => '',
        
        ];
    }
    public function messages()
{
    return [
        // 'name.required' => '名前を記入してください。',
        // 'email' => '',
        // 'company' => '',
        // 'password' => '',
    ];
}
}
