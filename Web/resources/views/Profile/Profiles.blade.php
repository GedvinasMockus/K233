@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Vartotojų sąrašas</div>
            <div class="card-body">
                <div class="d-grid p-2">
                    <table class="table table-hover">
                        <tr>
                            <th>Vardas</th>
                            <th>Pavarde</th>
                            <th>Email</th>
                        </tr>
                        <tr data-href="/Profile/1">
                            <td>Jonas</td>
                            <td>Pavarde</td>
                            <td>Email@email.com</td>
                        </tr>
                        <tr data-href="/Profile/2">
                            <td>Povilas</td>
                            <td>Petraitis</td>
                            <td>Hmm@email.com</td>
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
