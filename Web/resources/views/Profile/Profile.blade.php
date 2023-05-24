@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Vartotojo informacija</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
                <span class="p-2 fw-bold">Vartotojas nr {{ $id }}</span>
            </div>
            <div class="row p-2 pb-5">
                <div class="col-4 text-center">
                    <a href="" class="btn btn-primary">Redaguoti</a>
                </div>
                <div class="col-4 text-center">
                    @if ($isblocked === False)
                    <a href="/Profile/{{ $id }}/ban" class="btn btn-primary">Blokuoti</a>
                    @else
                    <a href="/Profile/{{ $id }}/unban" class="btn btn-primary">Atblokuoti</a>
                    @endif
                </div>
                <div class="col-4">
                    <a href="/Profile/{{ $id }}/change_status" class="btn btn-primary">Keisti statusą</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection('content')
