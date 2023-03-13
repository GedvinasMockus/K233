@extends('main') @section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Random</div>
            <div class="card-body">
                <div class="d-grid p-2">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>UUID</th>
                            <th>DATA</th>
                        </tr>
                        @foreach($data as $single)
                        <tr>
                            <td>{{ $single->id }}</td>
                            <td>{{ $single->uuid }}</td>
                            <td>{{ $single->date }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection('content')
