@extends('main') @section('content')
<div class="alert alert-danger mt-2" id="errorReservation" hidden>
    <span class="message" id="errorReservationSpan" hidden></span>
</div>
<div class="alert alert-success mt-2" id="successReservation" hidden>
    <span class="message" id="successReservationSpan" hidden></span>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Rezervacijos</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
            </div>
            <div class="d-grid gap-3 p-2">
                <div class="p-2" id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="removeReservation" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Rezervacijos naikinimas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group w-100">
                    <li class="list-group-item list-group-item-warning"><span class="fw-bold">Ar tikrai norite panaikinti šią rezervaciją?</span></li>
                    <li class="list-group-item"><span class="fw-bold">Aikštelė: </span><label id="lot" class="text-end"></label></li>
                    <li class="list-group-item"><span class="fw-bold">Adresas: </span><label id="address" class="text-end"></label></li>
                    <li class="list-group-item"><span class="fw-bold">Rezervuota vieta: </span><label id="space"></label></li>
                    <li class="list-group-item"><span class="fw-bold">Nuo: </span><label id="startDate"></label></li>
                    <li class="list-group-item"><span class="fw-bold">Iki: </span><label id="endDate"></label></li>
                    <li class="list-group-item"><span class="fw-bold">Grąžinama suma: </span><label id="sum"></label>€</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                <button type="submit" id="remove" class="btn btn-danger">Pašalinti</button>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    var dataEvents = '{!! $events !!}';
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var today = new Date().toISOString().slice(0, 10);
        var calendar = new FullCalendar.Calendar(calendarEl, {
            validRange: {
                start: today,
            },
            headerToolbar: {
                start: 'prev,next today',
                center: 'title',
                end: 'dayGridMonth,timeGridWeek',
            },
            buttonText: { today: 'Šiandien', month: 'Mėnesiais', week: 'Savaitėmis' },
            initialView: 'timeGridWeek',
            locale: 'lt',
            allDaySlot: false,
            slotMinTime: '0:00:00',
            slotMaxTime: '24:00:00',
            dayHeaderFormat: { weekday: 'short', month: 'numeric', day: 'numeric', omitCommas: true },
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            firstDay: 1,
            events: JSON.parse(dataEvents),
            dateClick: function (info) {
                calendar.changeView('timeGridWeek', info.dateStr);
            },
            eventDidMount: function (info) {
                info.el.style.backgroundColor = 'darkGreen';
                info.el.style.color = 'white';
                $(info.el).popover({
                    title: info.event.extendedProps.parking_name,
                    placement: 'top',
                    trigger: 'hover',
                    content:
                        'Adresas: ' +
                        info.event.extendedProps.address +
                        '<br>Stovėjimo vieta: ' +
                        info.event.extendedProps.space_number +
                        '<br>Nuo: ' +
                        moment(info.event.start).format('YYYY-MM-DD HH:mm:ss') +
                        '<br>Iki: ' +
                        moment(info.event.end).format('YYYY-MM-DD HH:mm:ss'),
                    container: 'body',
                    html: true,
                });
            },
            eventContent: function (info) {
                var start = moment(info.event.start).format('HH:mm');
                var end = moment(info.event.end).format('HH:mm');

                if (info.view.type === 'timeGridWeek') {
                    return {
                        html: '<div>' + start + ' - ' + end + '</div>' + '<div>' + info.event.title + '</div>',
                    };
                } else {
                    return {
                        html: '<div>' + start + ' - ' + end + '</div>',
                    };
                }
            },
            eventClick: function (info) {
                $('#removeReservation').modal('show');
                $('#lot').text(info.event.extendedProps.parking_name);
                $('#address').text(info.event.extendedProps.address);
                $('#space').text(info.event.extendedProps.space_number);
                $('#sum').text(info.event.extendedProps.price.toFixed(2));
                $('#startDate').text(moment(info.event.start).format('YYYY-MM-DD HH:mm:ss'));
                $('#endDate').text(moment(info.event.end).format('YYYY-MM-DD HH:mm:ss'));
                $('#remove').on('click', function (e) {
                    e.preventDefault();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                    });
                    $.ajax({
                        url: "{{ route('RemoveReservation') }}",
                        method: 'POST',
                        data: { id: info.event.extendedProps.id },
                        beforeSend: function () {
                            $('#errorReservationSpan').prop('hidden', true);
                            $('#errorReservation').prop('hidden', true);
                            $('#successReservationSpan').prop('hidden', true);
                            $('#successReservation').prop('hidden', true);
                        },
                        success: function (data) {
                            if (data.status == 0) {
                                $('#errorReservation').prop('hidden', false);
                                $('#errorReservationSpan').prop('hidden', false);
                                let errorArray = [];
                                $.each(data.error, function (prefix, val) {
                                    let errorMsg = val[0];
                                    if (!errorArray.includes(errorMsg)) {
                                        errorArray.push(errorMsg);
                                    }
                                });
                                let errorMessage = errorArray.join('<br>');
                                $('#errorReservationSpan').html(errorMessage);
                                $('#removeReservation').modal('hide');
                            } else {
                                $('#successReservationSpan').prop('hidden', false);
                                $('#successReservation').prop('hidden', false);
                                $('#successReservationSpan').html('Rezervacija sėkmingai panaikinta!');
                                calendar.removeAllEvents();
                                calendar.addEventSource(JSON.parse(data.events));
                                $('#removeReservation').modal('hide');
                            }
                        },
                    });
                });
            },
        });
        calendar.render();
        $('#removeReservation').on('hidden.bs.modal', function () {
            $('#remove').off('click');
        });
    });
</script>
@endsection('scripts')
