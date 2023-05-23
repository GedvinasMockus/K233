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
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2" style="text-align:center">
                        <b>Parkavimo sistema</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
            </div>
            <div class="d-grid gap-3 p-2">
                <div class="text-center" style="padding:25px">
                    <img style="max-width:100%" class="img-responsive" src="https://media.istockphoto.com/id/636444558/photo/empty-parking-lots-aerial-view.jpg?s=612x612&w=0&k=20&c=8C7yFpdy0QlcJglnwbPXGfDRd9KDjp0rLH9EVXWA0ac=">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')