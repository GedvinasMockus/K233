@extends('main') @section('content')
<div class="justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="row mt-5 mb-5">
                <div class="col-6 text-center">
                    <a href="{{ route('Register') }}" class="btn btn-primary">Registracija</a>
                </div>
                <div class="col-6 text-center">
                    <a href="{{ route('Login') }}" class="btn btn-primary">Prisijungimas</a>
                </div>
            </div>
            <div class="row mt-5 mb-5">
                <div class="col-3 text-center">
                    <a href="{{ route('DisplayParkingLots') }}" class="btn btn-primary">Aikštelės</a>
                </div>
                <div class="col-3 text-center">
                    <a href="{{ route('DisplayUserProfile') }}" class="btn btn-primary">Vartotojo profilis</a>
                </div>
                <div class="col-3 text-center">
                    <a href="{{ route('DisplayProfiles') }}" class="btn btn-primary">Vartotojų sąrašas</a>
                </div>
                <div class="col-3 text-center">
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
            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <a href="{{ route('Random') }}" class="btn btn-primary">Test nuoroda</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
