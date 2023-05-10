@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <label><input type="checkbox" id="checkbox" checked="true" /><strong style="font-size: 2en">Enabled</strong></label>
        <button id="changeCanvasPosition">Change Canvas Position</button>
        <label><input type="checkbox" id="drawRect" checked="true" />Draw rectangle bounds</label>
        <label><input type="checkbox" id="onlyOne" checked="true" />Only one</label>
        <div class="controls">
            <p>
                <button id="copy" onclick="Copy()">Copy Selected Objects</button>
            </p>
            <p>
                <button id="paste" onclick="Paste()">Paste Selected Objects</button>
            </p>
        </div>
        <div class="wrapper" style="margin-left: 200px; margin-top: 200px">
            <canvas id="canvas" width="500" height="500" style="border: 2px solid black"></canvas>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.4.0/fabric.min.js"></script>
<script>
    const canvas = document.querySelector("#canvas");
    var fabricCanvas = new fabric.Canvas(canvas);
    const checkbox = document.querySelector("#checkbox");
    let initialPos,
        bounds,
        rect,
        dragging = false,
        freeDrawing = checkbox.checked;
    const options = {
        drawRect: drawRect.checked,
        onlyOne: onlyOne.checked,
        rectProps: {
            stroke: "red",
            strokeWidth: 1,
            fill: "",
        },
    };
    function onMouseDown(e) {
        dragging = true;
        if (!freeDrawing) {
            return;
        }
        initialPos = { ...e.pointer };
        bounds = {};
        if (options.drawRect) {
            rect = new fabric.Rect({
                left: initialPos.x,
                top: initialPos.y,
                width: 0,
                height: 0,
                ...options.rectProps,
            });
            fabricCanvas.add(rect);
        }
    }
    function update(pointer) {
        if (initialPos.x > pointer.x) {
            bounds.x = Math.max(0, pointer.x);
            bounds.width = initialPos.x - bounds.x;
        } else {
            bounds.x = initialPos.x;
            bounds.width = pointer.x - initialPos.x;
        }
        if (initialPos.y > pointer.y) {
            bounds.y = Math.max(0, pointer.y);
            bounds.height = initialPos.y - bounds.y;
        } else {
            bounds.height = pointer.y - initialPos.y;
            bounds.y = initialPos.y;
        }
        if (options.drawRect) {
            rect.left = bounds.x;
            rect.top = bounds.y;
            rect.width = bounds.width;
            rect.height = bounds.height;
            rect.dirty = true;
            fabricCanvas.requestRenderAllBound();
        }
    }
    function onMouseMove(e) {
        if (!dragging || !freeDrawing) {
            return;
        }
        requestAnimationFrame(() => update(e.pointer));
    }
    function onMouseUp(e) {
        dragging = false;
        if (!freeDrawing) {
            return;
        }
        if (options.drawRect && rect && (rect.width == 0 || rect.height === 0)) {
            fabricCanvas.remove(rect);
        }
        if (!options.drawRect || !rect) {
            rect = new fabric.Rect({
                ...bounds,
                left: bounds.x,
                top: bounds.y,
                ...options.rectProps,
            });
            fabricCanvas.add(rect);
            rect.dirty = true;
            fabricCanvas.requestRenderAllBound();
        }
        rect.setCoords(); // important!
        console.log(rect.left, rect.top, rect.width, rect.height);
        options.onlyOne && uninstall();
    }
    function install() {
        freeDrawing = true;
        dragging = false;
        rect = null;
        checkbox.checked = true;
        fabricCanvas.on("mouse:down", onMouseDown);
        fabricCanvas.on("mouse:move", onMouseMove);
        fabricCanvas.on("mouse:up", onMouseUp);
    }
    function uninstall() {
        freeDrawing = false;
        dragging = false;
        rect = null;
        checkbox.checked = false;
        fabricCanvas.off("mouse:down", onMouseDown);
        fabricCanvas.off("mouse:move", onMouseMove);
        fabricCanvas.off("mouse:up", onMouseUp);
    }
    function Copy() {
        // clone what are you copying since you
        // may want copy and paste on different moment.
        // and you do not want the changes happened
        // later to reflect on the copy.
        canvas.getActiveObject().clone(function (cloned) {
            _clipboard = cloned;
        });
    }

    function Paste() {
        // clone again, so you can do multiple copies.
        _clipboard.clone(function (clonedObj) {
            canvas.discardActiveObject();
            clonedObj.set({
                left: clonedObj.left + 10,
                top: clonedObj.top + 10,
                evented: true,
            });
            if (clonedObj.type === "activeSelection") {
                // active selection needs a reference to the canvas.
                clonedObj.canvas = canvas;
                clonedObj.forEachObject(function (obj) {
                    canvas.add(obj);
                });
                // this should solve the unselectability
                clonedObj.setCoords();
            } else {
                canvas.add(clonedObj);
            }
            _clipboard.top += 10;
            _clipboard.left += 10;
            canvas.setActiveObject(clonedObj);
            canvas.requestRenderAll();
        });
    }

    // the following is OOT - it's just for the controls above
    checkbox.addEventListener("change", (e) => (e.currentTarget.checked ? install() : uninstall()));
    document.querySelector("#drawRect").addEventListener("change", (e) => {
        options.drawRect = e.currentTarget.checked;
    });
    document.querySelector("#onlyOne").addEventListener("change", (e) => {
        options.onlyOne = e.currentTarget.checked;
    });
    freeDrawing && install();
    document.querySelector("#changeCanvasPosition").addEventListener("click", () => {
        const el = document.querySelector(`.wrapper`);
        el.style.marginTop = Math.trunc(Math.random() * 300) + "px";
        el.style.marginLeft = Math.trunc(Math.random() * 200) + "px";
    });
</script>
@endsection('scripts')
