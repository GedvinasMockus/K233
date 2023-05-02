<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidHoursRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $hours = round(floatval($value), 1) * 10;

        if ($hours < 5) {
            $fail('Galimas mažiausias rezervuojamas laikas yra 30 minučių!');
        }
        if ($hours % 5 !== 0) {
            $fail('Rezervuojamas laikas gali būti tik po 30 minučių');
        }
    }
}
