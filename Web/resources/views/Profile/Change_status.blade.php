@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Vartotojo statuso keitimas</div>
            <div class="card-body">Vartotojas {{$user->name}} {{$user->surname}}
                <form method="POST" action="{{ route('ChangeStatus') }}">
                    @csrf

                    <input type="hidden" name="userid" value="{{ $user->id }}">

                    <div class="row mt-5 mb-5">
                        <div class="col-sm-4">
                            <label for="status">Vartotojo statusas:</label>
                            <select class="form-control" name="status" id="status">
                                @foreach($statuses as $key => $status)
                                    @if($status->id_User_role == $user->role)
                                        <option selected> {{ $statuseslt[$key] }} </option>
                                    @else
                                        <option> {{ $statuseslt[$key] }} </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">
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
