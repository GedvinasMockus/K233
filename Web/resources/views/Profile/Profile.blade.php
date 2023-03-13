@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Vartotojo informacija</div>
            <div class="card-body">Vartotojas nr {{ $id }}</div>
            <div class="row mt-5 mb-5">
                <div class="col-4 text-center">
                    <a href="" class="btn btn-primary">Redaguoti</a>
                </div>
                <div class="col-4 text-center">
                    <a href="" class="btn btn-primary">Blokuoti</a>
                </div>
                <div class="col-4 text-center">
                    <a href="" class="btn btn-primary">Keisti statusą</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
