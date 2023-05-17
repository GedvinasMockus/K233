<?php

namespace App\Rules;

use App\Models\ParkingSpace;
use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationUpdateConflictRule implements ValidationRule
{
    protected $startDate;
    protected $endDate;
    protected $reservationId;

    public function __construct($startDate, $endDate, $reservationId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reservationId = $reservationId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $spaceId = Reservation::where('id', '=', $this->reservationId)->firstOrFail();

        $conflicts = Reservation::where('fk_Parking_spaceid', $spaceId->fk_Parking_spaceid)
            ->where('id', '!=', $this->reservationId)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('date_from', '>=', $this->startDate)
                        ->where('date_from', '<', $this->endDate);
                })
                    ->orWhere(function ($query) {
                        $query->where('date_until', '>', $this->startDate)
                            ->where('date_until', '<=', $this->endDate);
                    })
                    ->orWhere(function ($query) {
                        $query->where('date_from', '<', $this->startDate)
                            ->where('date_until', '>', $this->endDate);
                    });
            })
            ->exists();
        if ($conflicts) {
            $fail('Negalima rezervuoti laiko, kuris šioje vietoje jau yra rezervuotas!');
        }
        $user = Auth::user();
        $parkingLotId = ParkingSpace::find($spaceId->fk_Parking_spaceid);
        $reservationCheck = Reservation::join('Parking_space', 'Reservation.fk_Parking_spaceid', '=', 'Parking_space.id')
            ->where('Reservation.fk_Userid', $user->id)
            ->where('Parking_space.fk_Parking_lotid', $parkingLotId->fk_Parking_lotid)
            ->where('Reservation.id', '!=', $this->reservationId)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('Reservation.date_from', '>=', $this->startDate)
                        ->where('Reservation.date_from', '<', $this->endDate);
                })
                    ->orWhere(function ($query) {
                        $query->where('Reservation.date_until', '>', $this->startDate)
                            ->where('Reservation.date_until', '<=', $this->endDate);
                    })
                    ->orWhere(function ($query) {
                        $query->where('Reservation.date_from', '<', $this->startDate)
                            ->where('Reservation.date_until', '>', $this->endDate);
                    });
            })
            ->exists();
        if ($reservationCheck) {
            $fail("Negalima užsirezervuoti vietos toje pačioje aikštelėje, nurodytu laiku!");
        }
    }
}
