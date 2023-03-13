@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelės redagavimas</div>
            <div class="card-body">Parkingas nr {{ $id }}</div>
            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <a href="" class="btn btn-primary">Redaguoti</a>
                </div>
            </div>
            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <a href="{{ route('DisplayParkingLots') }}" class="btn btn-primary">Aikštelių sąrašas</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
