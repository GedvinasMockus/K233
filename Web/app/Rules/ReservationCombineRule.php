<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationCombineRule implements ValidationRule
{

    protected $spaceId;

    public function __construct($spaceId)
    {
        $this->spaceId = $spaceId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $oldData = json_decode($value, true);
        $user = Auth::user();
        for ($i = 0; $i < sizeof($oldData); $i++) {
            $check = Reservation::where('date_from', '=', $oldData[$i]['start'])->where('date_until', '=', $oldData[$i]['end'])->where('fk_Userid', '=', $user->id)
                ->where('fk_Parking_spaceid', '=', $this->spaceId)->exists();
            if (!$check) {
                $fail('Negalima sujungi rezervacijų, nes ji nerasta!');
            }
        }
    }
}
