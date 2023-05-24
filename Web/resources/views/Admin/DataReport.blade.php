@extends('main') @section('content')
<div class="alert alert-danger mt-2" id="errorPlace" hidden>
    <span class="message" id="errorPlaceSpan" hidden></span>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Duomenų ataskaitos generavimas</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
            </div>
            <div class="row p-2">
                <div class="col-6 p-2"><span for="from">Nuo:</span> <input type="date" id="from" name="from" /></div>
                <div class="col-6 p-2"><span for="to">Iki:</span> <input type="date" id="to" name="to" /></div>
            </div>
            <div class="d-grid gap-3 p-2">
                <button onclick="onSave(this)" id="">Generuoti ataskaitą</button>
                <div class="alert alert-secondary mt-2" id="ReservationCountPlace" hidden>
                    <span class="message" id="ReservationCountSpan"></span>
                </div>
            </div>
            <div class="d-grid p-2">
                <table class="table table-hover" id="list" hidden>
                    <tr>
                        <th>ID</th>
                        <th>Nuo</th>
                        <th>Iki</th>
                        <th>Kaina</th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    function onSave() {
        var from = document.getElementById('from').value;
        var to = document.getElementById('to').value;

        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            });
            $.ajax({
                method: 'post',
                url: '/generatedatareport',
                data: {
                    from: from,
                    to: to,
                },
                success: function (data) {
                    var table = document.getElementById('list');

                    $('#list').find('tr:not(:first)').remove();

                    $('#ReservationCountPlace').prop('hidden', true);
                    $('#list').prop('hidden', true);

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
                        $('#ReservationCountPlace').prop('hidden', false);
                        $('#ReservationCountSpan').html('Rezervacijų kiekis šiame laiko tarpe: ' + data.count + '!');

                        if (data.count > 0) {
                            $('#list').prop('hidden', false);
                            var tableBody = document.createElement('tbody');

                            data.reservations.forEach(function (rowData) {
                                var row = document.createElement('tr');

                                var cell = document.createElement('td');
                                cell.appendChild(document.createTextNode(rowData['id']));
                                row.appendChild(cell);

                                cell = document.createElement('td');
                                cell.appendChild(document.createTextNode(rowData['date_from']));
                                row.appendChild(cell);

                                cell = document.createElement('td');
                                cell.appendChild(document.createTextNode(rowData['date_until']));
                                row.appendChild(cell);

                                cell = document.createElement('td');
                                cell.appendChild(document.createTextNode(rowData['full_price']));
                                row.appendChild(cell);

                                tableBody.appendChild(row);
                            });

                            table.appendChild(tableBody);

                            var tableFoot = document.createElement('tfoot');
                            row = document.createElement('tr');

                            cell = document.createElement('td');
                            cell.appendChild(document.createTextNode('Suma:'));
                            row.appendChild(cell);

                            cell = document.createElement('td');
                            cell.appendChild(document.createTextNode(''));
                            row.appendChild(cell);

                            cell = document.createElement('td');
                            cell.appendChild(document.createTextNode(''));
                            row.appendChild(cell);

                            cell = document.createElement('td');
                            cell.appendChild(document.createTextNode(data.sum));
                            row.appendChild(cell);

                            tableFoot.appendChild(row);

                            table.appendChild(tableFoot);
                        }
                    }
                },
            });
        });
    }
</script>
@endsection('scripts')
