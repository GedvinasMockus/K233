<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class ReservationConflictRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $startDate;
    protected $endDate;
    protected $spaceId;

    public function __construct($startDate, $endDate, $spaceId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->spaceId = $spaceId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $conflicts = Reservation::where('fk_Parking_spaceid', $this->spaceId)
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
    }
}
