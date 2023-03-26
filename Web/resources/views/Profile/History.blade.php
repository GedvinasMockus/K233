@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            @if(count($pastreservations) < 1)
                <div class="card-header">Neturi jokių praėjusių rezervacijų</div>
            @else
                <div class="card-header">Istorija</div>
                    <div class="card-body">
                        <div class="d-grid p-2">
                            <table class="table table-hover">
                                @foreach($pastreservations as $reservation)
                                    <tr>
                                        <th>{{$reservation->parking_name}}</th>
                                        <th>{{$reservation->date_from}}</th>
                                        <th>{{$reservation->date_until}}</th>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
            @endif
        </div>
    </div>
</div>
@endsection('content')
