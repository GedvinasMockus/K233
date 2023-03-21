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
                        @foreach($users as $user)
                            <tr data-href="/Profile/{{$user->id}}">
                                <td>{{$user->name}}</td>
                                <td>{{$user->surname}}</td>
                                <td>{{$user->email}}</td>
                            </tr>
                        @endforeach
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
