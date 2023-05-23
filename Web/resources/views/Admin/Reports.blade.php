@extends('main') @section('content')
<div class="alert alert-success alert-dismissible alert-comment-success mt-2" role="alert" hidden>
    <span class="successMessage"></span>
    <button type="button" class="btn-close" id="closePopSuccess"></button>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Pažeidimai</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
            </div>
            <div class="d-grid gap-3 p-2">
                @if(sizeof($tickets)>0)
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Aikštelė</th>
                            <th scope="col">Data</th>
                            <th scope="col">Pranešėjas</th>
                            <th scope="col">Atsakyta</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($tickets as $index => $ticket)
                        <tr data-href="{{$ticket->id}}">
                            <th scope="row">{{ $tickets->firstItem() + $index }}</th>
                            <td>{{ $ticket->parking_info }}</td>
                            <td>{{ $ticket->date }}</td>
                            <td>{{ $ticket->user_name }}</td>
                            <td>{{ $ticket->answered_status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">{!! $tickets->links() !!}</div>
                @else
                <div class="alert alert-danger mt-2">
                    <span>Kol kas pažeidimų nėra!</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="reportInfo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-on-top">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Pranešimo atsakymas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-dismissible alert-comment-danger" id="errorPlace" role="alert" hidden>
                    <span id="errorPlaceSpan"></span>
                    <button type="button" class="btn-close" id="closePop"></button>
                </div>
                <div class="row p-3">
                    <div class="col-12 col-lg-6 p-2">
                        <a id="photo_link">
                            <img id="img_link" data-fancybox class="mx-auto d-block" alt="Photo" style="max-width: 100%; max-height: 100%; object-fit: contain" />
                        </a>
                    </div>
                    <div class="col-12 col-lg-6 p-2 d-lg-block">
                        <ul class="list-group w-100">
                            <li class="list-group-item list-group-item-dark"><span class="fw-bold">Informacija apie pažeidimą</span></li>
                            <li class="list-group-item"><span class="fw-bold">Aikštelė: </span><label id="lot" class="text-end"></label></li>
                            <li class="list-group-item"><span class="fw-bold">Adresas: </span><label id="address" class="text-end"></label></li>
                            <li class="list-group-item"><span class="fw-bold">Pažeidimo laikas: </span><label id="time"></label></li>
                            <li class="list-group-item"><span class="fw-bold">Pranešėjas: </span><label id="user"></label></li>
                            <li class="list-group-item"><span class="fw-bold">Pranešėjo el. paštas: </span><label id="email"></label></li>
                            <li class="list-group-item"><span class="fw-bold">Atsakytas: </span><label id="answered"></label></li>
                        </ul>
                    </div>
                </div>
                <div class="p-2">
                    <blockquote class="blockquote">
                        <p>
                            <b>Pranešimas:</b>
                        </p>
                    </blockquote>
                    <label id="text"></label>
                    <hr class="dropdown-divider" />
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Atsakymas į pranešimą" id="answer" style="height: 100px"></textarea>
                        <label for="answer">Atsakymas į pranešimą</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                <button type="button" id="answerReport" class="btn btn-danger">Atsakyti</button>
            </div>
        </div>
    </div>
</div>

@endsection('content') @section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
<script>
    var data = JSON.parse('{!! json_encode($tickets) !!}');
    var lastClicked;
    document.addEventListener('DOMContentLoaded', () => {
        const rows = document.querySelectorAll('tr[data-href]');
        rows.forEach((row) => {
            row.addEventListener('click', () => {
                lastClicked = row.getAttribute('data-href');
                const ticketData = data.data.find((ticket) => ticket.id === parseInt(lastClicked));

                if (ticketData) {
                    document.getElementById('answer').value = '';
                    $('.alert-comment-danger').prop('hidden', true);
                    $('.alert-comment-success').prop('hidden', true);
                    $('#reportInfo').modal('show');
                    document.getElementById('lot').textContent = ticketData.parking_name;
                    document.getElementById('address').textContent = ticketData.address;
                    document.getElementById('time').textContent = ticketData.date;
                    document.getElementById('user').textContent = ticketData.user_name;
                    document.getElementById('email').textContent = ticketData.email;
                    document.getElementById('answered').textContent = ticketData.answered_status;
                    document.getElementById('img_link').src = "{{ asset('storage') }}" + '/' + ticketData.photo_path;
                    document.getElementById('photo_link').href = "{{ asset('storage') }}" + '/' + ticketData.photo_path;
                    document.getElementById('text').textContent = ticketData.text;
                }
            });
        });
    });
    $('#answerReport').click(function (e) {
        e.preventDefault();
        let formData = new FormData();
        formData.set('answer', document.getElementById('answer').value);
        formData.set('id_report', lastClicked);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        });
        $.ajax({
            url: "{{ route('AnswerToReport') }}",
            method: 'POST',
            data: formData,
            processData: false,
            dataType: 'json',
            contentType: false,
            beforeSend: function () {
                $('.alert-comment-danger').prop('hidden', true);
                $('.alert-comment-success').prop('hidden', true);
            },
            success: function (data) {
                if (data.status == 0) {
                    $('.alert-comment-danger').prop('hidden', false);
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
                    localStorage.setItem('showSuccessMessage', true);
                    location.reload();
                }
            },
        });
    });
    $('#closePop').click(function (e) {
        $('.alert-comment-danger').prop('hidden', true);
    });
    $('#closePopSuccess').click(function (e) {
        $('.alert-comment-success').prop('hidden', true);
    });
    $(document).ready(function () {
        if (localStorage.getItem('showSuccessMessage')) {
            localStorage.removeItem('showSuccessMessage');
            $('.alert-comment-success').prop('hidden', false);
            $('.successMessage').html('Pranešimas į pažeidimą pateiktas!');
        }
    });
</script>

<script>
    Fancybox.bind('[data-fancybox]', {
        Toolbar: {
            display: { left: ['infobar'], middle: ['pan', 'zoomIn', 'zoomOut', 'rotateCCW', 'rotateCW'] },
        },
        fullScreen: {
            autoStart: false,
        },
        initialSize: 'fit',
        focus: 'center',
        closeClickOutside: false,
        trapFocus: false,
        autoFocus: false,
        closeOnEscape: true,
        dragToClose: false,
        dragToPan: 'auto',
    });
</script>

@endsection('scripts')
