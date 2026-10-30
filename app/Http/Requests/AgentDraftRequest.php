<?php

namespace App\Http\Requests;

use App\Rules\validCountryCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AgentDraftRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->route('agent')->user_id),
            ],
            'password' => 'required|string|min:6|confirmed',
            'name' => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('agents', 'name')->ignore($this->route('agent')->id),
            ],
            'bio' => 'required|string|min:10|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'agent_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'country_code' => [
                'nullable',
                'string',
                'size:2',
                new validCountryCode,
            ],
            'class' => 'required|integer|exists:agent_classes,id',
        ];
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
