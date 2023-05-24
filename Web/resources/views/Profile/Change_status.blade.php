@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2">
                        <b>Vartotojo statuso keitimas</b>
                    </p>
                    <hr class="dropdown-divider" />
                </blockquote>
                <span class="p-2 fw-bold">Vartotojas {{$user->name}} {{$user->surname}}</span>
            </div>
            <div class="d-grid gap-3 p-2">
                <form method="POST" action="{{ route('ChangeStatus') }}">
                    @csrf

                    <input type="hidden" name="userid" value="{{ $user->id }}" />

                    <div class="row p-2">
                        <div class="col-sm-4">
                            <label for="status">Vartotojo statusas:</label>
                            <select class="form-control" name="status" id="status">
                                @foreach($statuses as $key => $status) @if($status->id_User_role == $user->role)
                                <option selected value="{{$status->id_User_role}}">{{ $statuseslt[$key] }}</option>
                                @else
                                <option value="{{$status->id_User_role}}">{{ $statuseslt[$key] }}</option>
                                @endif @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row p-2">
                        <div class="col-12 p-2 text-center">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Pakeisti') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection('content')
