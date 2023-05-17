<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTicket extends Model
{
    use HasFactory;

    protected $table = 'report_ticket';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'date',
        'text',
        'fk_Userid',
        'fk_Parking_lotid'
    ];

    public $timestamps = false;
}
