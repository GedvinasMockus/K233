@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="d-grid gap-3 p-2">
                <blockquote class="blockquote">
                    <p class="p-2"><b>Mano profilis</b></p>
                    <hr class="dropdown-divider" />
                </blockquote>

                <span class="text-success success-text p-2 fw-bold" hidden></span>
            </div>
            <div class="d-grid gap-3 p-2">
                <div class="col-md-8">
                    <table class="table table-borderless d-flex">
                        <tr>
                            <th>Vartotojo duomenys</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td class="text-uppercase">El. paštas:</td>
                            <td class="fw-bold">
                                {{ Auth::user()->email }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-uppercase">Vardas:</td>
                            <td class="fw-bold" id="userName">
                                {{ Auth::user()->name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-uppercase">Pavardė:</td>
                            <td class="fw-bold" id="userSurname">
                                {{ Auth::user()->surname }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-uppercase">Telefonas:</td>
                            <td class="fw-bold" id="userPhone">
                                {{ Auth::user()->phone_number }}
                            </td>
                        </tr>
                        <tr class="dropdown-divider"></tr>
                        <tr>
                            <td class="text-uppercase">Balansas:</td>
                            <td class="fw-bold">{{ number_format(Auth::user()->balance, 2, '.', '')}}€</td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#EditData" class="text-decoration-none link-secondary editUserData" data-bs-toggle="collapse"><i class="fa-solid fa-pencil"></i> Redaguoti duomenis</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="d-grid gap-3 p-2">@include('Profile.EditUserData')</div>
            <div class="d-grid gap-0 p-2">
                <p class="p-2"><b>Automobiliai</b><br /></p>
                <div class="col-md-8" id="Cars">@include('Profile.Cars.ShowCars')</div>
                <div class="collapse col-md-8 p-2" id="EditSelectedCar">
                    @include('Profile.Cars.EditCar')
                    <hr class="dropdown-divider" />
                </div>
                <p class="p-2">
                    <a href="#AddCar" class="text-decoration-none link-secondary newCar" data-bs-toggle="collapse"><i class="fa-solid fa-user-plus"></i> Pridėti automobilio informaciją</a>
                </p>
                <div class="collapse col-md-8 p-2" id="AddCar">@include('Profile.Cars.AddCar')</div>
                <hr class="dropdown-divider" />
            </div>
            <div class="d-grid gap-3 p-2">
                <p class="p-2">
                    <b>Paskyros trynimas</b><br />
                    Visi duomenys susije su jūsų profiliu bus pašalinti iš sistemos ir nebesugrąžinami!<br />
                </p>
                <div class="col-6 p-2">
                    <a href="#" class="btn btn-danger text-uppercase pd-2 deleteUser"><i class="fa-solid fa-trash-can"></i> Trinti paskyrą</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="route('Delete_User')" method="post" id="form_delete_user">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Vartotojo trinimas</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span>Ar jūs tikrai norite savo paskyrą iš sistemos?</span><br />
                    <span class="text-danger danger-text fw-bold" hidden> </span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                    <button type="submit" class="btn btn-primary">Ištrinti</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $(".deleteUser").click(function (e) {
            e.preventDefault();
            if ($("#EditData").hasClass("show")) {
                $("#EditData").collapse("toggle");
            }
            if ($("#AddCar").hasClass("show")) {
                $("#AddCar").collapse("toggle");
            }
            if ($("#EditSelectedCar").hasClass("show")) {
                $("#EditSelectedCar").collapse("toggle");
            }
            $("span.danger-text").prop("hidden", true);
            var id = $(this).attr("data-id");
            $("#idd").val(id);
            $("#deleteUser").modal("show");
            $("span.success-text").prop("hidden", true);
            $(document).find("span.success-text").text("");
            $(document).find("span.danger-text").text("");
        });
    });
</script>
<script>
    $(function () {
        $("#form_delete_single").on("submit", function (e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            $.ajax({
                url: $(this).attr("action"),
                method: $(this).attr("method"),
                data: new FormData(this),
                processData: false,
                dataType: "json",
                contentType: false,
                success: function (data) {
                    if (data.status == 0) {
                        $("span.danger-text").prop("hidden", false);
                        $(document).find("span.danger-text").text(data.error.idd);
                    } else {
                        $("span.danger-text").prop("hidden", true);
                        $("#deleteCar").modal("hide");
                        $(document).find("span.success-text").text("Automobilio informacija ištrintas!");
                        $("span.success-text").prop("hidden", false);
                        $.ajax({
                            url: "{{route('ShowCarInfo')  }}",
                            success: function (data) {
                                $("#Car").html(data);
                            },
                        });
                    }
                },
            });
        });
    });
</script>

@endsection('scripts')
