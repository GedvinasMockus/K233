@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Rezervacijos</div>
            <div class="card-body">
                <div class="d-grid p-2">
                    <table class="table table-hover">
                        <tr>
                            <th>Aikštelė</th>
                            <th>Nuo kada</th>
                            <th>Iki kada</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td>Santaka</td>
                            <td>2022-10-15 15:00</td>
                            <td>2022-10-15 17:30</td>
                            <td><a href="" class="btn btn-danger">Atšaukti</a></td>
                        </tr>
                        <tr>
                            <td>Santaka</td>
                            <td>2023-02-18 15:00</td>
                            <td>2023-02-18 17:30</td>
                            <td><a href="" class="btn btn-danger">Atšaukti</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
