<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchRequest extends FormRequest
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
            'term' => 'nullable|string|max:100',
            'type_id' => 'nullable|array',
            'type_id.*' => 'exists:mission_types,id',

            'priority' => 'nullable|array',
            'priority.*' => 'in:baja,media,alta,critica',

            'statement' => 'nullable|array',
            'statement.*' => 'in:created,waiting_start,in_progress,aborted,waiting_validation,completed',
            
            'result' => 'nullable|array',
            'result.*' => 'in:success,failed',
        ];
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
