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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="http://code.jquery.com/jquery-latest.min.js"></script>
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
                            <a href="" class="nav-link text-uppercase">Test</a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link text-uppercase">Test</a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link text-uppercase">Test</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link text-uppercase dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false"> Test </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <li><a class="dropdown-item" href="">Test</a></li>
                                <li><a class="dropdown-item" href="">Test</a></li>
                                <li><a class="dropdown-item" href="">Test</a></li>
                                <li><a class="dropdown-item" href="">Test</a></li>
                            </ul>
                        </li>
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
                            <a class="nav-link text-uppercase" href=""> Test</a>
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
        @yield('scripts')
    </body>
</html>
