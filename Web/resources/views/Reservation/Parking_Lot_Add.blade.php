@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"></div>
            {{--
            <form method="POST" action="{{ route('SaveLots') }}">
                @csrf --}}
                <label for="lotName">Nurodyk aikštelės pavadinimą</label>
                <input name="lotName" id="lotName" value="" />
                <label for="mapPath">Nurodyk nuotrauką</label>
                <input type="text" name="mapPath" id="mapPath" value="" />
                <label for="mapCity">Nurodyk miestą</label>
                <input type="text" name="mapCity" id="mapCity" value="" />
                <label for="mapStreet">Nurodyk gatvę</label>
                <input type="text" name="mapStreet" id="mapStreet" value="" />
                <label for="mapNumber">Nurodyk gatvės numerį</label>
                <input type="text" name="mapNumber" id="mapNumber" value="" />
                <label for="mapTariff">Nurodyk tarifą</label>
                <input type="number" step=".01" name="mapTariff" id="mapTariff" value="" />
                <button onclick="loadMap(this)" id="lm">Užkrauti nuotrauką</button>

                <div class="row mb-0">
                    <div class="col-md-8 offset-md-4">
                        {{--
                        <button type="submit" class="btn btn-primary">
                            {{ __("Išsaugoti") }}
                        </button>
                        --}}
                    </div>
                </div>
                {{--
            </form>
            --}}
            <div class="card-body">
                <h1 id="test">Test</h1>
                <button onclick="onSave(this)" id="c">Išsaugoti vietas</button>
                <button onclick="onKeypress(this)" id="b">create mode (A): false</button>
                <div>Paspausk B, kad ištrintum paskutinę nupieštą vietą</div>
                <div class="containerofmap" id="image"></div>
                {{-- <div>
                    <img class="containerofmap" id="image">
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    let lastPt;
    var listener;
    var slots = 0;
    var clicks = 0;
    var init = false;
    var create = false;
    var toggle = false;
    var points = [[], [], [], []];
    var allPoints = [];
    var img = new Image();
    var height = 0;
    var width = 0;

    document.addEventListener("click", onClickListener);
    document.addEventListener("keypress", onKeypress);

    document.addEventListener("DOMContentLoaded", () => {
        $('<svg class="hover" id="svg"> ' + '<polyline onclick="onClick(this)" class="poly" id="polyline' + slots + '" /> ' + '<line id="templine" style="fill:white;stroke:black;stroke-width:1" /> ' + "</svg>").appendTo(".containerofmap");
        createListener();
    });

    function onKeypress(e) {
        if (e.key == "a" || e.tagName == "BUTTON") {
            create = !create;
            document.getElementById("b").innerHTML = "create mode (A): " + create;
        }

        else if (e.key == "b" || e.tagName == "BUTTON") {
            allPoints.pop();

            console.log("trint");

            slots--;

            var element = document.getElementById("polyline" + slots);
            element.parentNode.removeChild(element);

            slotsplus = slots + 1;

            element = document.getElementById("polyline" + slotsplus);
            element.setAttribute("id", "polyline" + slots);

            element = document.getElementById("templine");
            element.setAttribute("x1", 0);
            element.setAttribute("y1", 0);
            element.setAttribute("x2", 0);
            element.setAttribute("y2", 0);
        }
    }

    function loadMap() {
        var path = document.getElementById("mapPath").value;
        img.src = path;
        height = img.height;
        width = img.width;

        document.getElementById("image").setAttribute("style", "background: url(" + path + ") no-repeat; height: " + height + "px; width:" + width + "px;");
        document.getElementById("svg").setAttribute("style", "height: " + height + "px; width:" + width + "px;");
    }

    function onSave() {
        // console.log(allPoints);
        var lotName = document.getElementById("lotName").value;
        var lotPath = document.getElementById("mapPath").value;
        var lotCity = document.getElementById("mapCity").value;
        var lotStreet = document.getElementById("mapStreet").value;
        var lotNumber = document.getElementById("mapNumber").value;
        var lotTariff = document.getElementById("mapTariff").value;

        $(function () {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            $.ajax({
                method: "post",
                url: "/savelots",
                data: {
                    points: allPoints,
                    name: lotName,
                    path: lotPath,
                    city: lotCity,
                    street: lotStreet,
                    number: lotNumber,
                    tariff: lotTariff,
                },
                success: function (response) {
                    alert(JSON.stringify(response));
                },
            });
        });
    }

    function onClickListener(ev) {
        if (!create) return;

        clicks++;

        if (clicks == 4) {
            clicks = 0;
            createSvg();
        }
        console.log("clicks: " + clicks);
    }

    function createListener() {
        var elem = document.querySelector(".containerofmap");
        const svg = document.querySelector("#svg");
        const polyline = document.querySelector("#polyline" + slots);
        const templine = document.querySelector("#templine");
        let firstPt;

        $("#svg").on("click", (e) => {
            if (!create) return;

            var clientRect = elem.getBoundingClientRect();

            let offsLeft = clientRect.left + document.documentElement.scrollLeft;
            let offsTop = clientRect.top + document.documentElement.scrollTop;

            let pts = polyline.getAttribute("points") || "";
            const newPoint = `${e.pageX - offsLeft},${e.pageY - offsTop} `;
            console.log(newPoint);

            pts += newPoint;
            polyline.setAttribute("points", pts);
            lastPt = [e.pageX - offsLeft, e.pageY - offsTop];

            if (clicks == 3) {
                console.log(pts);
                allPoints.push(pts);
                $("#svg").unbind();
                console.log("Removed onClick event");
                return;
            }
        });

        svg.addEventListener("mousemove", (e) => {
            if (!(lastPt === undefined) && clicks > 0 && create) {
                var clientRect = elem.getBoundingClientRect();
                let offsLeft = clientRect.left + document.documentElement.scrollLeft;
                let offsTop = clientRect.top + document.documentElement.scrollTop;

                templine.setAttribute("x1", lastPt[0]);
                templine.setAttribute("y1", lastPt[1]);
                templine.setAttribute("x2", e.pageX - offsLeft);
                templine.setAttribute("y2", e.pageY - offsTop);
            }
        });
    }

    function createSvg() {
        slots++;
        var polyLine = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
        polyLine.setAttribute("onclick", "onClick(this);");
        polyLine.setAttribute("class", "poly");
        polyLine.setAttribute("id", "polyline" + slots);
        $("#svg").append(polyLine);
        createListener();
    }

    function onClick(elem) {
        console.log("Clicked: " + elem.getAttribute("id"));
    }
</script>
@endsection('scripts')
