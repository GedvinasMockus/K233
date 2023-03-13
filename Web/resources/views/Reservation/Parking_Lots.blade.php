@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Aikštelės</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="{{ route('Random') }}" class="btn btn-primary">Pridėti parkavimo aikštelę</a>
                    </div>
                </div>
                <div class="d-grid p-2">
                    <table class="table table-hover">
                        <tr>
                            <th>Pavadinimas</th>
                            <th>Adresas</th>
                            <th></th>
                        </tr>
                        <tr data-href="/Parking_Lot/1">
                            <td>Santaka</td>
                            <td>Kaunas</td>
                            <td><a href="/Edit_Parking_Lot/1" class="btn btn-danger">Redaguoti</a></td>
                        </tr>
                        <tr data-href="/Parking_Lot/2">
                            <td>Akropolis</td>
                            <td>Kaunas</td>
                            <td><a href="/Edit_Parking_Lot/2" class="btn btn-danger">Redaguoti</a></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content') @section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const rows = document.querySelectorAll("tr[data-href]");
        rows.forEach((row) => {
            row.addEventListener("click", () => {
                window.location.href = row.dataset.href;
            });
        });
    });
</script>
@endsection('scripts')
