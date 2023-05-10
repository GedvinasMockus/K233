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
                        <b>Darbuotojų registracija aikštelėje</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
                <span class="px-2 fw-bold">Aikštelė: {{$lot->parking_name}}</span>
                <span class="px-2 fw-bold">Stovėjimo vietos numeris: {{ $space->space_number }}</span>
            </div>
            <div class="row p-3">
                <div class="col-12 col-lg-8 p-2" id="calendar"></div>
                <div class="col-12 col-lg-4 p-2 d-lg-block">
                    <div class="mt-5 d-flex justify-content-center h-100 w-100">
                        <ul class="list-group w-100">
                            <li class="list-group-item">
                                <span class="fw-bold">Darbuotojas: </span>
                            </li>
                            <li class="list-group-item">
                                <div class="form-floating">
                                    <select id="user-select" class="form-select">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Aikštelė: </span><label id="lot" class="text-end">{{$lot->parking_name}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Adresas: </span><label id="address" class="text-end">{{$lot->city}}, {{$lot->street}} {{$lot->street_number}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Rezervuojama vieta: </span><label id="space">{{ $space->space_number }}</label>
                            </li>

                            @auth
                            <li class="list-group-item">
                                <button type="submit" class="btn btn-success" id="reserve-btn">Rezevuoti</button>
                            </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    var selectedUser;
    $(document).ready(function () {
        $('#user-select').select2({
            ajax: {
                url: "{{ route('UserSearch') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        input: params.term,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: params.page * 30 < data.total_count,
                        },
                    };
                },
                cache: true,
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            language: {
                inputTooShort: function (args) {
                    var remainingChars = args.minimum - args.input.length;
                    return 'Prašome įvesti dar ' + remainingChars + ' simbolius';
                },
                noResults: function () {
                    return 'Darbuotojų nebuvo rasta!';
                },
                searching: function () {
                    return 'Ieškoma...';
                },
            },
            minimumInputLength: 3,
            templateResult: formatUser,
            templateSelection: formatUserSelection,
        });
        function formatUser(user) {
            if (user.loading) {
                return user.text;
            }
            var $user = $('<span class="d-flex align-items-center">' + user.name.charAt(0) + '. ' + user.surname + ' - ' + user.email + '</span>');
            return $user;
        }

        function formatUserSelection(user) {
            return user.email || user.text;
        }
    });
    $('#user-select').on('select2:select', function (e) {
        selectedUser = e.params.data.id;
    });
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
                var eventDuration = moment.duration(moment(eventEnd).diff(moment(eventStart)));
                var eventHours = eventDuration.asHours();
                var color = 'blue';
                newStart = moment(eventStart).format('YYYY-MM-DDTHH:mm:ss');
                newEnd = moment(eventEnd).format('YYYY-MM-DDTHH:mm:ss');
                if (moment(eventEnd).isAfter(now) && eventHours >= 0.5) {
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
                    formattedStart = moment(newStart).format('YYYY-MM-DD HH:mm:ss');
                    formattedEnd = moment(newEnd).format('YYYY-MM-DD HH:mm:ss');
                    selectedEvents.push({ start: formattedStart, end: formattedEnd, startUnformatted: newStart, endUnformatted: newEnd });
                }
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
                info.event.remove();
            },
            eventDidMount: function (info) {
                if (info.event.extendedProps.isNewEvent) {
                    $(info.el).popover({
                        title: 'Rezervacija',
                        placement: 'top',
                        trigger: 'hover',
                        content: 'Vardas: ' + info.event.extendedProps.name + '<br>Pavardė: ' + info.event.extendedProps.surname + '<br>El. paštas: ' + info.event.extendedProps.email,
                        container: 'body',
                        html: true,
                    });
                }
            },
        });
        $('#reserve-btn').click(function (e) {
            e.preventDefault();
            let formData = new FormData();
            for (let i = 0; i < selectedEvents.length; i++) {
                formData.append('start[]', selectedEvents[i].start);
                formData.append('end[]', selectedEvents[i].end);
            }
            formData.set('id', '{!! $space->id !!}');
            formData.set('user', selectedUser);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            });
            $.ajax({
                url: "{{ route('MakeUserReservation') }}",
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
                        $('#successPlace').prop('hidden', false);
                        $('#successPlaceSpan').prop('hidden', false);
                        $('#successPlaceSpan').text('Rezervacija sėkminga!');
                        calendar.removeAllEvents();
                        calendar.addEventSource(JSON.parse(data.events));
                        selectedEvents = [];
                    }
                },
            });
        });
        calendar.render();
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
