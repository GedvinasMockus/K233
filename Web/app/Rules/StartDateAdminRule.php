<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StartDateAdminRule implements ValidationRule
{
    protected $startDate;

    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        for ($i = 0; $i < sizeof($this->startDate); $i++) {
            $now = Carbon::now()->setSeconds(0);
            $now->minute = $now->minute < 30 ? 0 : 30;
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $this->startDate[$i])->setSeconds(0);
            if ($start->lt($now)) {
                $fail('Negalima rezervuoti laiko, kuris jau yra praėjęs!');
            }
            if ($start->minute != 0 && $start->minute != 30) {
                $fail('Mažiausias rezervuojamas laikas 30 min!');
            }
        }
    }
}
