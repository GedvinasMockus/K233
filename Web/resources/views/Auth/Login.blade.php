@extends('main') @section('content') @if ($message=Session::get('success'))
<div class="alert alert-info">
    {{ $message }}
</div>
@endif @if ($message=Session::get('error'))
<div class="alert alert-danger">
    {{ $message }}
</div>
@endif @if ($message=Session::get('errorNotConfirmed'))
<div class="alert alert-danger">
    {{ $message }}<br />
    Naujas patvirtinimo laiškas išsiųstas jūsų nurodytu elektroninių paštu!
</div>
@endif
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Prisijungimas</div>
            <div class="card-body">
                <form action="{{ route('Validate_Login') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" placeholder="El. Pašto adresas" value="{{ old('email') }}" id="floatingEmail" />
                        <label for="floatingEmail">El. Pašto adresas</label>
                        @if($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Slaptažodis" id="floatingPassword" />
                        <label for="floatingEmail">Slaptažodis</label>
                        @if($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-dark">Prisijungti</button>
                        </div>
                        <div class="col-6 text-end">
                            <a href="/PasswordRemember" class="btn btn-primary"> Pamiršote slaptažodį? </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection('content')
