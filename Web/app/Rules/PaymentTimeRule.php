<?php

namespace App\Rules;

use App\Models\Payment;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentTimeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        $latestPayment = Payment::where('fk_Userid', $user->id)
            ->orderBy('date', 'desc')
            ->first();
        if ($latestPayment) {
            $created = Carbon::createFromFormat('Y-m-d H:i:s', $latestPayment->date);
            if ($created->diffInMinutes(Carbon::now()) < 1) {
                $fail('Negalima atlikti mokėjimo, kai nėra praėję šiek tiek laiko!');
            }
        }
    }
}
