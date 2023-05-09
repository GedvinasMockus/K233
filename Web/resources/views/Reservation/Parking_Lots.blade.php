@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelės</div>
            <div class="card-body">
                @auth
                    @if(Auth::user()->isAdmin())
                    <div class="row">
                        <div class="col-12 text-center">
                            <a href="{{ route('DisplayNewParkingLot') }}" class="btn btn-primary">Pridėti parkavimo aikštelę</a>
                        </div>
                    </div>
                    @endif
                @endauth
                <div class="d-grid p-2">
                    <table class="table table-hover">
                        <tr>
                            <th>Pavadinimas</th>
                            <th>Adresas</th>
                            <th></th>
                        </tr>
                        @foreach($lots as $lot)
                        <tr data-href="/Parking_Lot/{{$lot->id}}">
                            <td>{{$lot->parking_name}}</td>
                            <td>{{$lot->city}} {{$lot->street}} {{$lot->street_number}}</td>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <td><a href="/Edit_Parking_Lot/1" class="btn btn-danger">Redaguoti</a></td>
                                @endif
                            @endauth
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll("tr[data-href]");
        rows.forEach((row) => {
            row.addEventListener("click", () => {
                window.location.href = row.dataset.href;
            });
        });
    });
</script>
@endsection('scripts')
