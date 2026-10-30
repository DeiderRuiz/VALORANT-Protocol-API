<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class MissionRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:100',
            'objective' => 'required|string|min:10|max:1000',
            'description' => 'required|string|min:20|max:3000',
            'deadline' => 'required|date|after:' . now()->addDay(),
            'type_id' => 'required|integer|exists:mission_types,id',
            'priority' => 'required|in:baja,media,alta,critica',
            'map_id' => 'required|integer|exists:maps,id',

            // 5 que no se (repitan el lider debe estar entre ellos)
            'agents' => 'required|array|size:5',
            'agents.*' => 'exists:agents,id|distinct',
            'leader_id' => [
                'required', 
                'integer',
                'exists:agents,id', 
                Rule::in($this->input('agents', [])),
            ]
        ];
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
