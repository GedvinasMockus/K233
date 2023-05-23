<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>Išmanioji parkavimo sistema</title>
        <link rel="icon" href="{{ URL::asset('css/favicon.ico') }}" type="image/x-icon" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <link type="text/css" rel="stylesheet" href="{{ url('css/css.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-uWxY/CJNBR+1zjPWmfnSnVxwRheevXITnMqoEIeG1LJrdI0GlVs/9cVSyPYXdcSF" crossorigin="anonymous" />
        <script src="https://kit.fontawesome.com/e9c05e7fa6.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-kQtW33rZJAHjgefvhyyzcGF3C5TFyBQBA13V1RKPf4uH+bwyzQxZ6CmMZHmNBEfJ" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.6/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
        <script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="http://code.jquery.com/jquery-latest.min.js"></script>
        <meta name="verify-paysera" content="4a7db98344b067a32fe159ea1a883ae1" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        <!-- <link rel="stylesheet" href="/css/app.css" /> -->
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-white sticky-top navbar-light p-3 shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="/"><i class="fa-solid fa-square-parking"></i> <strong>K233</strong></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav ms-start">
                        <li class="nav-item">
                            <a href="{{ route('DisplayParkingLots') }}" class="nav-link text-uppercase">Aikštelės</a>
                        </li>
                        @auth
                        <li class="nav-item">
                            <a href="{{ route('DisplayReservations') }}" class="nav-link text-uppercase">Rezervacijos</a>
                        </li>
                        @endauth @auth @if(Auth::user()->isAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link text-uppercase dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false"> Valdymo skydas </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <li><a class="dropdown-item" href="{{ route('DisplayDataReport') }}">Duomenų ataskaitos generavimas</a></li>
                                <li><a class="dropdown-item" href="{{ route('DisplayReports') }}">Pažeidimai</a></li>
                            </ul>
                        </li>
                        @endif @endauth
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" aria-current="page" href="{{ route('Login') }}"><i class="fa-solid fa-arrow-right-to-bracket"></i> Prisijungimas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" aria-current="page" href="{{ route('Register') }}"><i class="fa-solid fa-plus"></i> Registracija</a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-link text-uppercase addBalance" href=""><i class="fa-regular fa-money-bill-1"></i> {{ number_format(Auth::user()->balance, 2) }} € Papildyti</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-uppercase dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-circle-user me-1"></i>
                                {{ Auth::user()->name }}
                                {{ Auth::user()->surname }}
                            </a>

                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <li>
                                    <a class="dropdown-item" href="{{ route('DisplayUserProfile') }}">Mano profilis</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('DisplayHistory') }}">Mano istorija</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider" />
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('Logout') }}">Atsijungti</a>
                                </li>
                            </ul>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container card navbar-light shadow-sm" style="background-color: rgb(180, 180, 180)">@yield('content')</div>
        @auth
        <div class="modal fade" id="addBalance" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('Add_balance') }}" method="post" id="balance">
                        @csrf
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Balanso pildymas</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <span>Įrašykite sumą, kokia norėtumėte papildyti savo paskyros balansą.</span><br />
                            <div class="form-floating mt-3 col-6">
                                <input type="number" name="sum" class="form-control" placeholder="Suma" id="floatingSum" min="1" step="0.01" />
                                <label for="floatingSum">Suma</label>
                            </div>
                            <span class="text-danger error-text sum_error"></span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                            <button type="submit" class="btn btn-primary">Papildyti</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.addBalance').click(function (e) {
                    e.preventDefault();
                    $('#addBalance').find('input').val('');
                    $('#addBalance').modal('show');
                });
            });
            $(function () {
                $('#balance').on('submit', function (e) {
                    e.preventDefault();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        },
                    });
                    $.ajax({
                        url: $(this).attr('action'),
                        method: $(this).attr('method'),
                        data: new FormData(this),
                        processData: false,
                        dataType: 'json',
                        contentType: false,
                        beforeSend: function () {
                            $(document).find('span.error-text').text('');
                        },
                        success: function (data) {
                            if (data.status == 0) {
                                $.each(data.error, function (prefix, val) {
                                    $('span.' + prefix + '_error').text(val[0]);
                                });
                            } else {
                                window.location.href = data.data;
                            }
                        },
                    });
                });
            });
        </script>
        @endauth @yield('scripts')
    </body>
</html>
