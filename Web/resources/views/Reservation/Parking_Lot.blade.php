@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelės</div>
            <div class="card-body">Parkingas nr {{ $id }}</div>
            {{-- <div class="containerofmap" id="image" style="background: url(<?php echo $photo; ?>) no-repeat;max-width:100%"></div> --}}

            <div class="containerofmap"> 
                <img id="image" style="max-width:100%" class="image" src="<?php echo $photo; ?>">
            </div>

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
    var img = document.createElement('img');

    var oheight;
    var owidth;

    var sizediffx;
    var sizediffy;

    var path = {!! json_encode($photo, JSON_HEX_TAG) !!}


    img.onload = function () {
        spaces.forEach(createSvg);
    }
    
    $(document).ready(function () {
        img.src = path;
        oheight = img.height;
        owidth = img.width;

        let box = document.getElementById('image');
        let width = box.clientWidth;
        let height = box.clientHeight;

        sizediffx = owidth / width;
        sizediffy = oheight / height;

        console.log({sizediffx, sizediffy});

        $('<svg class="hover" id="svg">' + "</svg>").appendTo(".containerofmap");

        document.getElementById("svg").setAttribute("style", "height: " + height + "px; width:" + width + "px; margin-left: -" + width + "px;");
    }); 

    // window.addEventListener('resize', function(event){
    //     let box = document.getElementById('image');
    //     let width = box.clientWidth;
    //     let height = box.clientHeight;

    //     sizediffx = owidth / width;
    //     sizediffy = oheight / height;

    // });

    function createSvg(space) {
        let pts = `${space['x1'] / sizediffx},${space['y1'] / sizediffy} ${space['x2'] / sizediffx},${space['y2'] / sizediffy} ${space['x3'] / sizediffx},${space['y3'] / sizediffy} ${space['x4'] / sizediffx},${space['y4'] / sizediffy} `;

        var polyLine = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
        polyLine.setAttribute("onclick", "onClick(this);");
        polyLine.setAttribute("class", "poly");
        polyLine.setAttribute("id", "polygon" + space['id']);
        polyLine.setAttribute("points", pts);
        polyLine.setAttribute("href", "/Parking_Space/" + space['id']);
        $("#svg").append(polyLine);
    }

    function onClick(elem) {
        window.location.href = elem.getAttribute("href");
    }
</script>
@endsection('scripts')