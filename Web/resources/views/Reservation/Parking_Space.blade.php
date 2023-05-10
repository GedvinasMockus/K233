@extends('main') @section('content')
<div class="alert alert-danger mt-2" id="errorPlace" hidden>
    <span class="message" id="errorPlaceSpan" hidden></span>
</div>
<div class="alert alert-success mt-2" id="successPlace" hidden>
    <span class="message" id="successPlaceSpan" hidden></span>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Aikštelė: {{$lot->parking_name}}</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>

                <span class="p-2 fw-bold">Stovėjimo vietos numeris: {{ $space->space_number }}</span>
            </div>
            <div class="row p-3">
                <div class="col-12 col-lg-8 p-2" id="calendar"></div>
                <div class="col-12 col-lg-4 p-2 d-lg-block">
                    <div class="d-flex mt-5 justify-content-center h-100 w-100">
                        <ul class="list-group w-100">
                            <li class="list-group-item">
                                <span class="fw-bold">Aikštelė: </span><label id="lot" class="text-end">{{$lot->parking_name}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Adresas: </span><label id="address" class="text-end">{{$lot->city}}, {{$lot->street}} {{$lot->street_number}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Rezervuojama vieta: </span><label id="space">{{ $space->space_number }}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Valandos kaina: </span><label id="tariff">{{$lot->tariff}}</label
                                >€
                            </li>
                            <li class="list-group-item"><span class="fw-bold">Nuo: </span><label id="startDate">Nepasirinkta</label></li>
                            <li class="list-group-item"><span class="fw-bold">Iki: </span><label id="endDate">Nepasirinkta</label></li>
                            <li class="list-group-item"><span class="fw-bold">Rezervuotas laikas: </span><label id="hours">Nepasirinkta</label></li>
                            <li class="list-group-item"><span class="fw-bold">Galutinė suma: </span><label id="price">Nežinoma</label></li>
                            @auth
                            <li class="list-group-item">
                                <button type="submit" class="btn btn-success" id="reserve-btn">Rezevuoti</button>
                            </li>
                            @if (Auth::user()->role==4)
                            <li class="list-group-item">
                                <a href="{{ route('UserReservation', ['id' => $id]) }}" class="btn btn-warning">Darbuotojų rezervacija</a>
                            </li>
                            @endif @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    var selectedEvents = [];
    var dataEvents = '{!! $events !!}';
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            slotMinTime: '0:00:00',
            slotMaxTime: '24:00:00',
            allDaySlot: false,
            firstDay: 1,
            events: JSON.parse(dataEvents),
            locale: 'lt',
            dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric', omitCommas: true },
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            buttonText: { today: 'Šiandien' },
            selectable: true,
            selectOverlap: false,
            selectLongPressDelay: 100,
            eventLongPressDelay: 200,

            select: function (info) {
                var now = moment();
                var eventStart = info.start;
                var eventEnd = info.end;
                var formattedStart = moment(eventStart).format('YYYY-MM-DD HH:mm:ss');
                var formattedEnd = moment(eventEnd).format('YYYY-MM-DD HH:mm:ss');
                var eventDuration = moment.duration(moment(eventEnd).diff(moment(eventStart)));
                var eventHours = eventDuration.asHours();
                var isFound = selectedEvents.some(function (event) {
                    return event.start === formattedEnd || event.end === formattedStart;
                });
                var isBefore = selectedEvents.some(function (event) {
                    return moment(eventStart).isBefore(event.startUnformatted);
                });
                var isAfter = selectedEvents.some(function (event) {
                    return moment(eventEnd).isAfter(event.endUnformatted);
                });
                var firstAfter = selectedEvents.find(function (event) {
                    return moment(event.startUnformatted).isAfter(eventStart);
                });
                var lastBefore = selectedEvents.find(function (event) {
                    return moment(event.endUnformatted).isBefore(eventEnd);
                });
                if (moment(eventEnd).isAfter(now) && eventHours >= 0.5 && (selectedEvents.length == 0 || isFound)) {
                    var newStart, newEnd;
                    if (firstAfter) {
                        newEnd = moment(firstAfter.endUnformatted);
                    } else {
                        newEnd = eventEnd;
                    }
                    if (lastBefore) {
                        newStart = moment(lastBefore.startUnformatted);
                    } else {
                        newStart = eventStart;
                    }
                    selectedEvents.forEach(function (event) {
                        calendar
                            .getEvents()
                            .filter(function (calEvent) {
                                return moment(calEvent.start).format() === moment(event.startUnformatted).format() && moment(calEvent.end).format() === moment(event.endUnformatted).format();
                            })
                            .forEach(function (calEvent) {
                                calEvent.remove();
                            });
                    });
                    var color = 'blue';
                    newStart = moment(newStart).format('YYYY-MM-DDTHH:mm:ss');
                    newEnd = moment(newEnd).format('YYYY-MM-DDTHH:mm:ss');
                    calendar.addEvent({
                        start: newStart,
                        end: newEnd,
                        backgroundColor: color,
                        borderColor: color,
                        selectable: true,
                        editable: false,
                        durationEditable: false,
                    });
                    calendar.render();
                    selectedEvents = [];
                    formattedStart = moment(newStart).format('YYYY-MM-DD HH:mm:ss');
                    formattedEnd = moment(newEnd).format('YYYY-MM-DD HH:mm:ss');
                    selectedEvents.push({ start: formattedStart, end: formattedEnd, startUnformatted: newStart, endUnformatted: newEnd });

                    fillBill();
                }

                calendar.unselect();
            },
            eventClick: function (info) {
                if (info.event.backgroundColor === 'red' || info.event.backgroundColor === 'darkGreen') {
                    return false;
                }
                var eventStart = moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');
                var eventEnd = moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');
                selectedEvents = selectedEvents.filter(function (event) {
                    return !(event.start === eventStart && event.end === eventEnd);
                });
                fillBill();
                info.event.remove();
            },
        });
        calendar.render();
    });
    function fillBill() {
        var price = '{!! $lot->tariff !!}';
        if (selectedEvents.length != 0) {
            var eventDuration = moment.duration(moment(selectedEvents[0].endUnformatted).diff(moment(selectedEvents[0].startUnformatted)));
            var eventHours = eventDuration.asHours();
            var fullPrice = (price * eventHours).toFixed(2);
            $('#startDate').text(selectedEvents[0].start);
            $('#endDate').text(selectedEvents[0].end);
            $('#hours').text(eventHours + 'h');
            $('#price').text(fullPrice + '€');
        } else {
            $('#startDate').text('Nepasirinkta');
            $('#endDate').text('Nepasirinkta');
            $('#hours').text('Nepasirinkta');
            $('#price').text('Nežinoma');
        }
    }
    $('#reserve-btn').click(function (e) {
        e.preventDefault();
        let formData = new FormData();
        if (selectedEvents.length != 0) {
            var eventDuration = moment.duration(moment(selectedEvents[0].endUnformatted).diff(moment(selectedEvents[0].startUnformatted)));
            var eventHours = eventDuration.asHours();
            formData.set('startDate', selectedEvents[0].start);
            formData.set('endDate', selectedEvents[0].end);
            formData.set('id', '{!! $space->id !!}');
            formData.set('hours', eventHours);
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });
        $.ajax({
            url: "{{ route('MakeReservation') }}",
            method: 'POST',
            data: formData,
            processData: false,
            dataType: 'json',
            contentType: false,
            beforeSend: function () {
                $('#errorPlace').prop('hidden', true);
                $('#errorPlaceSpan').prop('hidden', true);
                $('#successPlace').prop('hidden', true);
                $('#successPlaceSpan').prop('hidden', true);
            },
            success: function (data) {
                if (data.status == 0) {
                    $('#errorPlace').prop('hidden', false);
                    $('#errorPlaceSpan').prop('hidden', false);
                    let errorArray = [];

                    $.each(data.error, function (prefix, val) {
                        let errorMsg = val[0];
                        if (!errorArray.includes(errorMsg)) {
                            errorArray.push(errorMsg);
                        }
                    });

                    let errorMessage = errorArray.join('<br>');

                    $('#errorPlaceSpan').html(errorMessage);
                } else {
                    localStorage.setItem('reservationSuccess', true);
                    setTimeout(function () {
                        location.reload();
                    }, 0);
                }
            },
        });
    });
    $(document).ready(function () {
        if (localStorage.getItem('reservationSuccess')) {
            $('#successPlace').prop('hidden', false);
            $('#successPlaceSpan').prop('hidden', false);
            $('#successPlaceSpan').text('Rezervacija sėkminga!');

            localStorage.removeItem('reservationSuccess');
        }
    });
</script>
@endsection('scripts')
