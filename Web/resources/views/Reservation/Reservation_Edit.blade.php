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
                        <b>Rezervacijos redagavimas</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
            </div>
            <div class="row p-3">
                <div class="col-12 col-lg-8 p-2" id="calendar"></div>
                <div class="col-12 col-lg-4 p-2 d-lg-block">
                    <div class="mt-5 justify-content-center h-100 w-100">
                        <ul class="list-group w-100">
                            <li class="list-group-item list-group-item-dark">
                                <span class="fw-bold">Senos rezervacijos informacija</span>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Aikštelė: </span><label id="lot" class="text-end">{{$lot->parking_name}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Adresas: </span><label id="address" class="text-end">{{$lot->city}}, {{$lot->street}} {{$lot->street_number}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Rezervuota vieta: </span><label id="space">{{ $lot->space_number }}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Valandos kaina: </span><label id="tariff">{{$lot->tariff}}</label
                                >€
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Nuo: </span><label id="startDate">{{$lot->date_from}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Iki: </span><label id="endDate">{{$lot->date_until}}</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Rezervuotas laikas: </span><label id="hours">{{$lot->hour_amount}}h</label>
                            </li>
                            <li class="list-group-item">
                                <span class="fw-bold">Sumokėta suma: </span><label id="price">{{number_format($lot->full_price, 2, '.', '')}}€</label>
                            </li>
                        </ul>
                        <ul class="list-group w-100 mt-2" id="reservationUpdate" hidden>
                            <li class="list-group-item"><span class="fw-bold">Rezervacija nuo: </span><label id="startDateReservation">Nežinoma</label></li>
                            <li class="list-group-item"><span class="fw-bold">Rezervacija iki: </span><label id="endDateReservation">Nežinoma</label></li>
                            <li class="list-group-item"><span class="fw-bold">Rezervacijos laikas: </span><label id="hoursReservation">Nežinoma</label></li>
                            <li class="list-group-item"><span class="fw-bold">Grąžinama suma: </span><label id="moneyReturn">0.00€</label></li>
                            <li class="list-group-item"><span class="fw-bold">Mokėtina suma: </span><label id="moneyPay">0.00€</label></li>
                        </ul>
                        @auth
                        <ul class="list-group w-100 mt-2">
                            <li class="list-group-item">
                                <button type="submit" class="btn btn-success" id="update-btn">Atnaujinti rezervaciją</button>
                            </li>
                        </ul>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    var dataEvents = '{!! $events !!}';
    var parsed = JSON.parse(dataEvents);
    var oldest = '{{$lot->date_from}}';
    var newest = '{{$lot->date_until}}';
    var price = '{!! $lot->tariff !!}';
    var hours = '{!! $lot->hour_amount !!}';
    var paidPrice = '{!! $lot->full_price !!}';
    var calendar;
    var reservationUpdate = [];
    var editStart = oldest;
    var editEnd = newest;
    var foundOldest, foundNewest;
    var eventDates = reloadInfo(parsed);

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        const today = new Date();
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            slotMinTime: '0:00:00',
            slotMaxTime: '24:00:00',
            eventOverlap: false,
            allDaySlot: false,
            firstDay: 1,
            events: parsed,
            locale: 'lt',
            dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric', omitCommas: true },
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            buttonText: { today: 'Šiandien' },
            selectable: false,
            selectOverlap: false,
            selectLongPressDelay: 100,
            eventLongPressDelay: 200,
            validRange: {
                start: new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000),
            },
            editable: true,
            eventResize: function (info) {
                var start = moment(info.event.start);
                var end = moment(info.event.end);

                if ((moment(info.event.start).isBefore(moment()) && moment(info.event.end).isBefore(moment())) || info.event.backgroundColor !== 'darkGreen') {
                    info.revert();
                } else if (end.diff(start, 'hours', true) < 0.5) {
                    info.revert();
                } else {
                    combineEvents(info);
                    fillBill(info);
                }
            },

            eventDrop: function (info) {
                if (moment(info.event.start).isBefore(moment()) || info.event.backgroundColor !== 'darkGreen') {
                    info.revert();
                } else if (moment().isAfter(editStart)) {
                    info.revert();
                } else {
                    combineEvents(info);
                    fillBill(info);
                }
            },
        });

        calendar.render();
    });

    function reloadInfo(eventInfos) {
        makeDates = [];
        for (let i = 0; i < eventInfos.length; i++) {
            let start = moment(eventInfos[i].start).format('YYYY-MM-DD HH:mm:ss');
            let end = moment(eventInfos[i].end).format('YYYY-MM-DD HH:mm:ss');
            makeDates.push({ start: start, end: end });
        }
        return makeDates;
    }

    function combineEvents(info) {
        var eventStart = info.event.start;
        var eventEnd = info.event.end;
        var formattedStart = moment(eventStart).format('YYYY-MM-DD HH:mm:ss');
        var formattedEnd = moment(eventEnd).format('YYYY-MM-DD HH:mm:ss');

        calendar.getEvents().forEach(function (calEvent) {
            if (moment(calEvent.start).isSame(moment(formattedEnd)) || (moment(calEvent.end).isSame(moment(formattedStart)) && calEvent.extendedProps.isUserEvent)) {
                if (
                    eventDates.some(function (event) {
                        return moment(event.start).isSame(calEvent.start) && moment(event.end).isSame(calEvent.end) && calEvent.extendedProps.isUserEvent;
                    })
                ) {
                    calEvent.setProp('title', 'Redaguojama rezervacija');
                    calEvent.setProp('backgroundColor', 'limegreen');
                    var index = reservationUpdate.findIndex(function (updateEvent) {
                        return updateEvent._def.defId === calEvent._def.defId && updateEvent._instance.instanceId === calEvent._instance.instanceId;
                    });
                    if (index === -1) {
                        reservationUpdate.push(calEvent);
                    }
                }
            } else {
                if (!moment(calEvent.start).isSame(moment(formattedEnd)) || !(moment(calEvent.end).isSame(moment(formattedStart)) && calEvent.extendedProps.isUserEvent && calEvent.backgroundColor != 'darkGreen')) {
                    if (
                        eventDates.some(function (event) {
                            return moment(event.start).isSame(calEvent.start) && moment(event.end).isSame(calEvent.end) && calEvent.extendedProps.isUserEvent && calEvent.backgroundColor != 'darkGreen';
                        })
                    ) {
                        calEvent.setProp('title', 'Jūsų rezervacija');
                        calEvent.setProp('backgroundColor', 'grey');
                        var index = reservationUpdate.findIndex(function (updateEvent) {
                            return updateEvent._def.defId === calEvent._def.defId && updateEvent._instance.instanceId === calEvent._instance.instanceId;
                        });
                        if (index !== -1) {
                            reservationUpdate.splice(index, 1);
                        }
                    }
                }
            }
        });
        calendar.render();
    }

    function fillBill(info) {
        var formatedOldest = moment(info.event.start).format('YYYY-MM-DD HH:mm:ss');
        var formatedNewest = moment(info.event.end).format('YYYY-MM-DD HH:mm:ss');
        editStart = formatedOldest;
        editEnd = formatedNewest;
        if (!moment(formatedOldest).isSame(oldest) || !moment(formatedNewest).isSame(newest)) {
            var eventDuration = moment.duration(moment(formatedNewest).diff(moment(formatedOldest)));

            if (reservationUpdate.length > 0) {
                const allEvents = reservationUpdate.concat({
                    start: formatedOldest,
                    end: formatedNewest,
                });
                const { earliestStart, latestEnd } = allEvents.reduce(
                    (acc, event) => {
                        const { start, end } = event;
                        if (!acc.earliestStart || moment(start).isBefore(moment(acc.earliestStart))) {
                            acc.earliestStart = start;
                        }
                        if (!acc.latestEnd || moment(end).isAfter(moment(acc.latestEnd))) {
                            acc.latestEnd = end;
                        }
                        return acc;
                    },
                    { earliestStart: null, latestEnd: null }
                );
                formatedOldest = moment(earliestStart).format('YYYY-MM-DD HH:mm:ss');
                formatedNewest = moment(latestEnd).format('YYYY-MM-DD HH:mm:ss');
                foundOldest = formatedOldest;
                foundNewest = formatedNewest;
            }

            var eventHours = eventDuration.asHours();
            var fullPrice = (price * (eventHours - hours)).toFixed(2);
            var labelHours = moment.duration(moment(formatedNewest).diff(moment(formatedOldest))).asHours();
            $('#reservationUpdate').prop('hidden', false);
            $('#startDateReservation').text(formatedOldest);
            $('#endDateReservation').text(formatedNewest);
            $('#hoursReservation').text(labelHours + 'h');
            $('#moneyPay').text(fullPrice + '€');
            if (eventHours <= hours) {
                $('#moneyPay').text('0.00€');
                if (paidPrice <= -fullPrice) {
                    $('#moneyReturn').text((paidPrice * 1).toFixed(2) + '€');
                } else {
                    $('#moneyReturn').text((-1 * fullPrice).toFixed(2) + '€');
                }
            } else {
                $('#moneyPay').text(fullPrice + '€');
                $('#moneyReturn').text('0.00€');
            }
        } else {
            $('#reservationUpdate').prop('hidden', true);
        }
    }
    $('#update-btn').click(function (e) {
        e.preventDefault();
        let formData = new FormData();
        var eventDuration = moment.duration(moment(editEnd).diff(moment(editStart)));
        var eventHours = eventDuration.asHours();
        formData.set('startDate', editStart);
        formData.set('endDate', editEnd);
        formData.set('hours', eventHours);
        formData.set('id', '{!! $id !!}');

        if (reservationUpdate.length > 0) {
            var dataSend = [];
            for (let i = 0; i < reservationUpdate.length; i++) {
                let start = moment(reservationUpdate[i].start).format('YYYY-MM-DD HH:mm:ss');
                let end = moment(reservationUpdate[i].end).format('YYYY-MM-DD HH:mm:ss');
                dataSend.push({ start: start, end: end });
            }
            formData.set('oldData', JSON.stringify(dataSend));
            formData.set('oldest', foundOldest);
            formData.set('newest', foundNewest);
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });
        $.ajax({
            url: "{{ route('UpdateReservation') }}",
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
                    window.location.href = "{{ route('DisplayReservations') }}?success=1";
                }
            },
        });
    });
</script>

@endsection('scripts')
