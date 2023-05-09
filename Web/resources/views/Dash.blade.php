@extends('main') @section('content')
<div class="justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="row mt-5 mb-5">
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayParkingLots') }}" class="btn btn-primary">Aikštelės</a>
                </div>
                @if(Auth::user()->isAdmin())
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayProfiles') }}" class="btn btn-primary">Vartotojų sąrašas</a>
                </div>
                @endif
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayHistory') }}" class="btn btn-primary">Mano istorija</a>
                </div>
            </div>
            <div class="row mt-5 mb-5">
                <div class="col-6 text-center">
                    <a href="{{ route('DisplayBalance') }}" class="btn btn-primary">Balanso pildymas</a>
                </div>
                <div class="col-6 text-center">
                    <a href="{{ route('DisplayReservations') }}" class="btn btn-primary">Mano rezervacijos</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
