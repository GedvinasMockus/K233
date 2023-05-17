<?php

namespace App\Rules;

use App\Models\Reservation;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        } else {
            $now = Carbon::now()->setSeconds(0);
            $reservationTime = Reservation::where('id', $value)
                ->where('fk_Userid', $user->id)
                ->first();

            $start = Carbon::createFromFormat('Y-m-d H:i:s', $reservationTime->date_from)->setSeconds(0);
            if ($start->lt($now)) {
                $fail('Rezervacijos pašalinti nebegalima, nes ji jau prasidėjusi!');
            }
        }
    }
}
