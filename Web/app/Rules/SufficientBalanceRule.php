<?php

namespace App\Rules;

use App\Models\ParkingLot;
use App\Models\ParkingSpace;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class SufficientBalanceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $space = ParkingSpace::findOrFail($value);
        $lot = ParkingLot::findOrFail($space->fk_Parking_lotid);
        $hours = request('hours');
        $tariff = $lot->tariff;
        $requiredCost = $hours * $tariff;
        $user = Auth::user();
        if ($user->balance < $requiredCost) {
            $fail('Nepakanka lėšų rezervacijai!');
        }
        if ($user->role == 1) {
            $fail('Negalite rezervuoti vietos, kol jūsų paskyra nėra patvirtinta!');
        }
        if ($user->role == 3) {
            $fail('Negalite rezervuoti vietos, nes esate užblokuotas!');
        }
    }
}
