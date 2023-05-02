<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class StartDateRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $now = Carbon::now()->setSeconds(0);
        $now->minute = $now->minute < 30 ? 0 : 30;
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $value)->setSeconds(0);
        if ($start->lt($now)) {
            $fail('Negalima rezervuoti laiko, kuris jau yra praėjęs!');
        }
        if ($start->minute != 0 && $start->minute != 30) {
            $fail('Mažiausias rezervuojamas laikas 30 min!');
        }
    }
}
