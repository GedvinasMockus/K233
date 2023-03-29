<div class="modal fade" id="deleteCar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('Delete_Car') }}" method="post" id="form_delete_single">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Automobilio trinimas</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span>Ar jūs tikrai norite ištrinti automobilio informaciją iš sistemos?</span><br />
                    <span class="text-danger danger-text fw-bold" hidden> </span>
                    <input type="hidden" id="idd" name="idd" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Uždaryti</button>
                    <button type="submit" class="btn btn-primary">Ištrinti</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(count($cars) > 0) @foreach($cars as $single)
<div class="carInfo">
    <table class="table table-borderless d-flex">
        <tr>
            <td class="text-uppercase">Pavadinimas:</td>
            <td class="fw-bold">
                {{ $single->car_name }}
            </td>
        </tr>
        <tr>
            <td class="text-uppercase">Valstybinis numeris:</td>
            <td class="fw-bold text-uppercase">
                {{ $single->license_plate }}
            </td>
        </tr>
        <tr></tr>
    </table>
    <div class="row">
        <div class="col-6">
            <a href="#EditSelectedCar" class="text-decoration-none p-2 link-secondary EditSingle" data-bs-toggle="collapse" data-id="{{$single->id}}"> <i class="fa-solid fa-pencil"></i> Redaguoti</a>
        </div>
        <div class="col-6">
            <a href="#" class="text-decoration-none p-2 link-secondary delete" data-id="{{ $single->id}}"><i class="fa-solid fa-trash-can"></i> Šalinti</a>
        </div>
    </div>
    <hr class="dropdown-divider" />
</div>
@endforeach @else
<p class="fst-italic p-2"><b>Nėra informacijos apie turimus automobilius!</b><br /></p>
@endif

<script type="text/javascript">
    $(document).ready(function () {
        $(".EditSingle").click(function (e) {
            if ($("#AddCar").hasClass("show")) {
                $("#AddCar").collapse("toggle");
            }
            $(".EditSelectedCar").find("input").val("");
            $(document).find("span.error-text").text("");
            $("span.success-text").prop("hidden", true);
            if (!$("#EditSelectedCar").hasClass("show")) {
                e.preventDefault();
                var id = $(this).attr("data-id");
                $("#id").val(id);
                if (id > 0) {
                    var url = "{{ route('GetUserCarInfoSingleSeparate',[':id']) }}";
                    url = url.replace(":id", id);
                    $.ajax({
                        url: url,
                        dataType: "json",
                        success: function (data) {
                            $(".EditSelectedCar").find("input").val("");
                            $(document).find("span.error-text").text("");
                            $("#floatingManufacturer").val(data.singleCar.make);
                            $("#floatingModel").val(data.singleCar.model);
                            $("#floatingYear").val(data.singleCar.year);
                            $("#floatingNumber").val(data.singleCar.license_plate);
                        },
                    });
                }
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $(".delete").click(function (e) {
            e.preventDefault();
            if ($("#EditSelectedCar").hasClass("show")) {
                $("#EditSelectedCar").collapse("toggle");
            }
            if ($("#AddCar").hasClass("show")) {
                $("#AddCar").collapse("toggle");
            }
            $("span.danger-text").prop("hidden", true);
            var id = $(this).attr("data-id");
            $("#idd").val(id);
            $("#deleteCar").modal("show");
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
                        $(document).find("span.success-text").text("Automobilis ištrintas!");
                        $("span.success-text").prop("hidden", false);
                        $.ajax({
                            url: "{{ route('ShowCarInfo') }}",
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
