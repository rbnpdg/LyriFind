<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cari Penggalan Lirik Lagu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite('resources/css/app.css')
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Cari Penggalan Lirik Lagu</h2>

    <form action="{{ route('lagu-search') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="query" class="form-control" placeholder="Cari penggalan lirik..." value="{{ old('query', $query ?? '') }}" required>
            <button type="submit" class="btn btn-primary">Cari</button>
        </div>
    </form>

    @if(isset($results))
        <h3>Hasil Pencarian:</h3>

        @if(count($results) === 0)
            <div class="alert alert-warning">Tidak ditemukan hasil untuk pencarian Anda.</div>
        @else
            @foreach($results as $lagu)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $lagu['judul'] }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">{{ $lagu['penyanyi'] }}</h6>
                        <p><strong>Relevansi:</strong> {{ number_format($lagu["score"] * 100, 2) }}%</p>
                        <pre class="card-text" style="white-space: pre-wrap;">{{ $lagu['lirik'] }}</pre>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
