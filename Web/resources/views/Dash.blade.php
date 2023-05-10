@extends('main') @section('content') @if ($message=Session::get('successMes'))
<div class="alert alert-success">
    {{ $message }}
</div>
@elseif ($message=Session::get('errorMes'))
<div class="alert alert-danger">{{ $message }}</div>
@endif
<div class="justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="row mt-5 mb-5">
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayParkingLots') }}" class="btn btn-primary">Aikštelės</a>
                </div>
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayProfiles') }}" class="btn btn-primary">Vartotojų sąrašas</a>
                </div>
                <div class="col-4 text-center">
                    <a href="{{ route('DisplayHistory') }}" class="btn btn-primary">Mano istorija</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
