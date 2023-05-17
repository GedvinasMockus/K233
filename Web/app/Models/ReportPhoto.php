<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPhoto extends Model
{
    use HasFactory;

    protected $table = 'report_photo';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'photo_path',
        'fk_Report_ticketid',
    ];

    public $timestamps = false;
}
