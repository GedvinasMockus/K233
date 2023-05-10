<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class ReservationCheckRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        $reservation = Reservation::where('id', $value)
            ->where('fk_Userid', $user->id)
            ->exists();
        if (!$reservation) {
            $fail('Rezervacija nerasta!');
        }
    }
}
