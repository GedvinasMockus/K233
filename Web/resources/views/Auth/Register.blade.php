@extends('main') @section('content') @if ($message=Session::get('failed'))
<div class="alert alert-danger">
    {{ $message }}
</div>
@endif
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Registracija</div>
            <div class="card-body">
                <form action="{{ route('Validate_Reg') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Vardas" value="{{ old('name') }}" id="floatingName" />
                        <label for="floatingName">Vardas</label>
                        @if($errors->has('name'))
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="surname" class="form-control" placeholder="Pavardė" value="{{ old('surname') }}" id="floatingSurname" />
                        <label for="floatingSurname">Pavardė</label>
                        @if($errors->has('surname'))
                        <span class="text-danger">{{ $errors->first('surname') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" placeholder="El. Pašto adresas" value="{{ old('email') }}" id="floatingEmail" />
                        <label for="floatingEmail">El. Pašto adresas</label>
                        @if($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="phone" class="form-control" placeholder="Telefono numeris" @if(!empty(old("phone"))) value="{{ old("phone") }}" @else value="+370" @endif id="floatingPhone" pattern="(\+370)\d{8}" />
                        <label for="floatingEmail">Telefono numeris</label>
                        @if($errors->has('phone'))
                        <span class="text-danger">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Slaptažodis" id="floatingPassword" />
                        <label for="floatingPassword">Slaptažodis</label>
                        @if($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Pakartokite slaptažodį" id="floatingPasswordConfirmation" />
                        <label for="floatingPasswordConfirmation">Pakartokite slaptažodį</label>
                        @if($errors->has('password_confirmation'))
                        <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                        @endif
                    </div>
                    <div class="d-grid mx-auto">
                        <button type="submit" class="btn btn-dark btn-block">Registruotis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection('content')
