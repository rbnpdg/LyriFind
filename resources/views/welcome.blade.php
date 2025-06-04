<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Upload PDF Lagu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @vite('resources/css/app.css')
</head>
<body>
<div class="container my-5" style="max-width: 600px;">
    <h2 class="mb-4">Upload PDF Berisi Lagu</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lagu-upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Flex container for inline input and button -->
        <div class="mb-3 d-flex align-items-end">
            <div class="flex-grow-1 rounded-left"> <!-- Input takes available space -->
                <label for="pdf" class="form-label">Pilih file PDF</label>
                <input class="form-control" type="file" id="pdf" name="pdf" accept=".pdf" required>
            </div>
            <div> <!-- Button remains its natural size -->
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
