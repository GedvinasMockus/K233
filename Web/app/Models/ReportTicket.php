<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'fk_Parking_lotid',
        'answered'
    ];

    public static function getReports($pages)
    {
        $tickets = ReportTicket::leftJoin('user', 'report_ticket.fk_Userid', '=', 'user.id')
            ->leftJoin('parking_lot', 'report_ticket.fk_Parking_lotid', '=', 'parking_lot.id')
            ->leftJoin('report_photo', 'report_ticket.id', '=', 'report_photo.fk_Report_ticketid')
            ->select(
                'report_ticket.*',
                'parking_lot.parking_name',
                DB::raw("CONCAT(SUBSTRING(user.name, 1, 1), '. ', user.surname) AS user_name"),
                'user.email',
                'report_photo.photo_path',
                DB::raw("CONCAT(parking_lot.parking_name, ' - ', parking_lot.city, ', ', parking_lot.street, ' ', parking_lot.street_number) AS parking_info"),
                DB::raw("CONCAT(parking_lot.city, ', ', parking_lot.street, ' ', parking_lot.street_number) AS address"),
                DB::raw("'Ne' AS answered_status")
            )
            ->where('answered', 0)
            ->unionAll(
                ReportTicket::leftJoin('user', 'report_ticket.fk_Userid', '=', 'user.id')
                    ->leftJoin('parking_lot', 'report_ticket.fk_Parking_lotid', '=', 'parking_lot.id')
                    ->leftJoin('report_photo', 'report_ticket.id', '=', 'report_photo.fk_Report_ticketid')
                    ->select(
                        'report_ticket.*',
                        'parking_lot.parking_name',
                        DB::raw("CONCAT(SUBSTRING(user.name, 1, 1), '. ', user.surname) AS user_name"),
                        'user.email',
                        'report_photo.photo_path',
                        DB::raw("CONCAT(parking_lot.parking_name, ' - ', parking_lot.city, ', ', parking_lot.street, ' ', parking_lot.street_number) AS parking_info"),
                        DB::raw("CONCAT(parking_lot.city, ', ', parking_lot.street, ' ', parking_lot.street_number) AS address"),
                        DB::raw("'Taip' AS answered_status")
                    )
                    ->where('answered', 1)
            )->orderBy('answered', 'asc')
            ->orderBy('date', 'asc')
            ->paginate($pages);

        return $tickets;
    }
    public $timestamps = false;
}
