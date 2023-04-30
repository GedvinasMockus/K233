@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelė: {{$lot->parking_name}}</div>
            <div class="card-body">Parkingo vieta nr: {{ $space->space_number }}</div>
            <div id="calendar"></div>
            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <a href="" class="btn btn-primary">Pasirinkti laiką</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            slotMinTime: '0:00:00',
            slotMaxTime: '24:00:00',
            allDaySlot: false,
            firstDay: 1,
            eventColor: '#e00d18',
            events: @json($events),
        });
        calendar.render();
    });

</script>
@endsection('scripts')