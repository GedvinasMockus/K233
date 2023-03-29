<form action="{{ route('Add_Car') }}" method="POST" id="form_add_car">
    @csrf
    <span class="text-danger error-text id_error"></span>
    <div class="form-floating mb-3">
        <input type="text" name="manufacturer" class="form-control" placeholder="Automobilio gamintojas" id="floatingManufacturer" />
        <label for="floatingCity">Automobilio gamintojas</label>
        <span class="text-danger error-text manufacturer_error"></span>
    </div>
    <div class="form-floating mb-3">
        <input type="text" name="model" class="form-control" placeholder="Automobilio modelis" id="floatingModel" />
        <label for="floatingModel">Automobilio modelis</label>
        <span class="text-danger error-text model_error"></span>
    </div>
    <div class="form-floating mb-3">
        <input type="number" name="year" class="form-control" placeholder="Automobilio pagaminimo metai" id="floatingYear" min="1950" max="{{ date('Y') }}" />
        <label for="floatingYear">Automobilio pagaminimo metai</label>
        <span class="text-danger error-text year_error"></span>
    </div>
    <div class="form-floating mb-3">
        <input type="text" name="number" class="form-control" placeholder="Valstybinis numeris" id="floatingNumber" />
        <label for="floatingNumber">Valstybinis numeris</label>
        <span class="text-danger error-text number_error"></span>
    </div>
    <div class="row">
        <div class="col-6 d-grid gap-2">
            <button type="submit" class="btn btn-danger text-uppercase pd-2">Pridėti</button>
        </div>
        <div class="col-6 d-grid gap-2">
            <a href="#AddCar" class="text-decoration-none link-secondary" data-bs-toggle="collapse"> Atšaukti </a>
        </div>
    </div>
</form>
<script>
    $(function () {
        $("#form_add_car").on("submit", function (e) {
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
                        $(document).find("span.success-text").text("Nauja automobilio informacija pridėta!");
                        $("#AddCar").collapse("toggle");
                        $.ajax({
                            url: "{{route('ShowCarInfo')}}",
                            success: function (data) {
                                $("#Cars").html(data);
                            },
                        });
                    }
                },
            });
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".newCar").click(function (e) {
            e.preventDefault();
            if ($("#EditSelectedCar").hasClass("show")) {
                $("#EditSelectedCar").collapse("toggle");
            }
            $(document).find("span.error-text").text("");
            $(document).find("span.success-text").text("");
            $("span.success-text").prop("hidden", true);
            $("#AddCar").find("input").val("");
        });
    });
</script>
