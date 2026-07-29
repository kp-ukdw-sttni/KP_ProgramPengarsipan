<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer: {{ $arsip->judul }} - E-Archive STTNI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #0B0F19;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .viewer-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            border: none;
            border-radius: 0;
            height: 100%;
        }
        .viewer-body {
            flex-grow: 1;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .viewer-frame {
            width: 100%;
            height: 100%;
            border: none;
            background: #1e293b;
        }
        .viewer-image-container {
            max-width: 95%;
            max-height: 95%;
            overflow: auto;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            background: #0f172a;
            padding: 20px;
        }
        .viewer-image {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }
    </style>
</head>
<body>

    @php
        $extension = strtolower(pathinfo($arsip->file_path, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png']);
    @endphp

    <div class="viewer-container">
        <!-- Viewer Header -->
        <header class="viewer-header">
            <div>
                <span class="badge badge-info" style="margin-bottom: 4px;">{{ $arsip->kategori->name }}</span>
                <h1 class="viewer-title" style="color: var(--text-primary); font-size: 1.1rem; font-weight: 600;">
                    {{ $arsip->judul }} 
                    <span style="font-weight: 400; color: var(--text-secondary); margin-left: 8px;">(Kode: {{ $arsip->nomor_arsip }})</span>
                </h1>
            </div>
            <div>
                <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup Jendela</button>
            </div>
        </header>

        <!-- Viewer Body -->
        <div class="viewer-body">
            @if($isImage)
                <div class="viewer-image-container">
                    <img src="{{ route('arsip.stream', $arsip->id) }}" class="viewer-image" alt="Pratinjau Dokumen">
                </div>
            @else
                <iframe src="{{ route('arsip.stream', $arsip->id) }}#toolbar=0" class="viewer-frame">
                    Browsermu tidak mendukung pemutar PDF. Silakan unduh dokumen untuk melihatnya.
                </iframe>
            @endif
        </div>
    </div>

    <script>
        // Simple security prevention: disable right click inside image container
        document.addEventListener('contextmenu', event => event.preventDefault());
    </script>
</body>
</html>
