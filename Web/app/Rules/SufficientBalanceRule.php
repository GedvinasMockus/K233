<?php

namespace App\Rules;

use App\Models\ParkingLot;
use App\Models\ParkingSpace;
use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SufficientBalanceRule implements ValidationRule
{
    protected $spaceId;

    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->spaceId = Reservation::findOrFail($id)->fk_Parking_spaceid;
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = is_null($this->spaceId) ? $value : $this->spaceId;

        $space = ParkingSpace::findOrFail($id);
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
