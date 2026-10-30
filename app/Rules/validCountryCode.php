<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;

class validCountryCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Obtener la lsita de paises del json
        $countries = json_decode(
            Storage::disk('public')->get('countries.json'),
            true
        );

        $success = Storage::disk('public')->exists('countries.json');
        if (!$success) {
            $fail('Nada.');
        }
        

        $exists = collect($countries)->contains('code', $value);

        if (!$exists) {
            $fail('El código de país no es válido.');
        }
    }
}
