@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelės</div>
            <div class="card-body">Parkingas nr {{ $id }}</div>
            <div class="containerofmap" id="image" style="background: url(<?php echo $photo; ?>) no-repeat" onload="mapLoaded()"></div>
            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <a href="{{ route('DisplayParkingLots') }}" class="btn btn-primary">Aikštelių sąrašas</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    var spaces = {!! json_encode($spaces->toArray(), JSON_HEX_TAG) !!}
    var slots = 0;
    // var img = new Image();
    var img = document.createElement('img');

    var path = {!! json_encode($photo, JSON_HEX_TAG) !!}


    img.onload = function () {
        // console.log("True");
        spaces.forEach(createSvg);
    }
    
    $(document).ready(function () {
        img.src = path;
        height = img.height;
        width = img.width;

        $('<svg class="hover" id="svg">' + "</svg>").appendTo(".containerofmap");
        document.getElementById("svg").setAttribute("style", "height: " + height + "px; width:" + width + "px;");
    }); 
    

    function createSvg(space) {
        // console.log(space['id']);

        let pts = `${space['x1']},${space['y1']} ${space['x2']},${space['y2']} ${space['x3']},${space['y3']} ${space['x4']},${space['y4']} `;

        var polyLine = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
        polyLine.setAttribute("onclick", "onClick(this);");
        polyLine.setAttribute("class", "poly");
        polyLine.setAttribute("id", "polygon" + space['id']);
        polyLine.setAttribute("points", pts);
        polyLine.setAttribute("href", "/Parking_Space/" + space['id']);
        $("#svg").append(polyLine);
        // console.log("small load");
    }

    function onClick(elem) {
        // console.log("Clicked: " + elem.getAttribute("id"));

        window.location.href = elem.getAttribute("href");
    }
</script>
@endsection('scripts')