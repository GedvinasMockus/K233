<div class="collapse col-md-6 p-2 dataEdit" id="EditData">
    <form action="{{ route('Edit_user_data') }}" method="POST" id="form_edit_user_data">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" name="name" class="form-control" placeholder="Vardas" value="{{ old('name') }}" id="floatingName" />
            <label for="floatingName">Vardas</label>
            <span class="text-danger error-text name_error"></span>
        </div>
        <div class="form-floating mb-3">
            <input type="text" name="surname" class="form-control" placeholder="Pavardė" value="{{ old('surname') }}" id="floatingSurname" />
            <label for="floatingSurname">Pavardė</label>
            <span class="text-danger error-text surname_error"></span>
        </div>
        <div class="form-floating mb-3">
            <input type="text" name="phone" class="form-control" placeholder="Telefono numeris" @if(!empty(old("phone"))) value="{{ old("phone") }}" @else value="+370" @endif id="floatingPhone" pattern="(\+370)\d{8}" />
            <label for="floatingEmail">Telefono numeris</label>
            <span class="text-danger error-text phone_error"></span>
        </div>
        <hr class="dropdown-divider" />
        <div class="form-floating mb-3">
            <input type="password" name="oldPassword" class="form-control" placeholder="Slaptažodis" id="floatingOldPassword" />
            <label for="floatingOldPassword">Senas slaptažodis</label>
            <span class="text-danger error-text oldPassword_error"></span>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" placeholder="Slaptažodis" id="floatingPassword" />
            <label for="floatingPassword">Slaptažodis</label>
            <span class="text-danger error-text password_error"></span>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Pakartokite slaptažodį" id="floatingPasswordConfirmation" />
            <label for="floatingPasswordConfirmation">Pakartokite slaptažodį</label>
        </div>
        <div class="row">
            <div class="col-6 d-grid gap-2">
                <button type="submit" class="btn btn-danger text-uppercase pd-2">Saugoti</button>
            </div>
            <div class="col-6 d-grid gap-2">
                <a href="#EditData" class="text-decoration-none link-secondary" data-bs-toggle="collapse"> Atšaukti </a>
            </div>
        </div>
    </form>
</div>
<hr class="dropdown-divider" />
<script type="text/javascript">
    $(document).ready(function () {
        $(".dataEdit").find("input").val("");
        $(".editUserData").click(function (e) {
            e.preventDefault();
            var url = "{{route('GetUserInfo')}}";
            $.ajax({
                url: url,
                dataType: "json",
                success: function (data) {
                    $(document).find("span.success-text").text("");
                    $("span.success-text").prop("hidden", true);
                    $(document).find("span.error-text").text("");
                    $(".dataEdit").find("input").val("");
                    $("#floatingName").val(data.data.name);
                    $("#floatingSurname").val(data.data.surname);
                    $("#floatingPhone").val(data.data.phone_number);
                },
            });
        });
    });
</script>
<script>
    $(function () {
        $("#form_edit_user_data").on("submit", function (e) {
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
                beforeSend: function () {
                    $(document).find("span.error-text").text("");
                },
                success: function (data) {
                    if (data.status == 0) {
                        $.each(data.error, function (prefix, val) {
                            $("span." + prefix + "_error").text(val[0]);
                        });
                    } else {
                        $("span.success-text").prop("hidden", false);
                        $(document).find("span.success-text").text("Paskyros duomenys atnaujinti!");
                        $("#userName").text(data.data.name);
                        $("#userSurname").text(data.data.surname);
                        $("#userPhone").text(data.data.phone);
                        $("#EditData").collapse("toggle");
                    }
                },
            });
        });
    });
</script>
