<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;
    protected $table = 'user';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone_number',
        'uuid',
        'balance',
        'role'
    ];
    protected $hidden = [
        'password',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function generateEmailVerificationToken()
    {
        $token = Str::random(60);
        $cacheKey = 'email_verification_' . $token;
        Cache::put($cacheKey, $this->id, now()->addMinutes(60));
        return $token;
    }


    public $timestamps = false;
}
