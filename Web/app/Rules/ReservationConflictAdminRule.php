<?php

namespace App\Rules;

use App\Models\ParkingSpace;
use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class ReservationConflictAdminRule implements ValidationRule
{
    protected $startDate;
    protected $endDate;
    protected $spaceId;
    protected $userId;

    public function __construct($startDate, $endDate, $spaceId, $userId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->spaceId = $spaceId;
        $this->userId = $userId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->startDate) || empty($this->endDate)) {
            $fail('Prašome pasirinkti rezervuojamą laiką!');
        }
        for ($i = 0; $i < sizeof($this->startDate); $i++) {
            $conflicts = Reservation::where('fk_Parking_spaceid', $this->spaceId)
                ->where(function ($query) use ($i) {
                    $query->where(function ($query) use ($i) {
                        $query->where('date_from', '>=', $this->startDate[$i])
                            ->where('date_from', '<', $this->endDate[$i]);
                    })
                        ->orWhere(function ($query) use ($i) {
                            $query->where('date_until', '>', $this->startDate[$i])
                                ->where('date_until', '<=', $this->endDate[$i]);
                        })
                        ->orWhere(function ($query) use ($i) {
                            $query->where('date_from', '<', $this->startDate[$i])
                                ->where('date_until', '>', $this->endDate[$i]);
                        });
                })
                ->exists();
            if ($conflicts) {
                $fail('Negalima rezervuoti laiko, kuris šioje vietoje jau yra rezervuotas!');
            }

            $parkingLotId = ParkingSpace::find($this->spaceId);
            $reservationCheck = Reservation::join('Parking_space', 'Reservation.fk_Parking_spaceid', '=', 'Parking_space.id')
                ->where('Reservation.fk_Userid', $this->userId)
                ->where('Parking_space.fk_Parking_lotid', $parkingLotId->fk_Parking_lotid)
                ->where(function ($query) use ($i) {
                    $query->where(function ($query) use ($i) {
                        $query->where('Reservation.date_from', '>=', $this->startDate[$i])
                            ->where('Reservation.date_from', '<', $this->endDate[$i]);
                    })
                        ->orWhere(function ($query) use ($i) {
                            $query->where('Reservation.date_until', '>', $this->startDate[$i])
                                ->where('Reservation.date_until', '<=', $this->endDate[$i]);
                        })
                        ->orWhere(function ($query) use ($i) {
                            $query->where('Reservation.date_from', '<', $this->startDate[$i])
                                ->where('Reservation.date_until', '>', $this->endDate[$i]);
                        });
                })
                ->exists();
            if ($reservationCheck) {
                $fail("Negalima užsirezervuoti vietos toje pačioje aikštelėje, nurodytu laiku!");
            }
        }
    }
}
