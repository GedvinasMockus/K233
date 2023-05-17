<!DOCTYPE html>
<html>
    <head>
        <title>Pažeidėjo pranešimas</title>
    </head>
    <body>
        <h1>Dėkojame už praneštą pažeidėją mūsų aikštelėje! Informacija, kuri buvo pateikta:</h1>
        <p><b>Aikštelė: </b>{{ $name }}</p>
        <p><b>Adresas: </b>{{ $address }}</p>
        <p><b>Laikas: </b>{{ $time }}</p>
        <p><b>Aprašymas: </b>{{ $description }}</p>
        <img src="{{ $message->embed(public_path('storage/' . $image)) }}" alt="img" style="display: block; border: 1px; max-width: 100%; height: auto" />
    </body>
</html>
