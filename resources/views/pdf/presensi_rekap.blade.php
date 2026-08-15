<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi UKM</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { margin: 0; padding: 0; color: #1e293b; font-size: 12px; }
        .kop { width: 100%; border-bottom: 3px solid #1A2B4C; padding-bottom: 8px; margin-bottom: 14px; }
        .kop table { width: 100%; border-collapse: collapse; }
        .kop img { width: 70px; height: 70px; object-fit: contain; }
        .kop .org { font-size: 18px; font-weight: bold; color: #1A2B4C; }
        .kop .addr { font-size: 10px; color: #64748B; margin-top: 2px; }
        .kop .tgl { font-size: 10px; color: #64748B; text-align: right; }
        .title { text-align: center; font-size: 14px; font-weight: bold; color: #1A2B4C; margin: 4px 0 2px 0; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { text-align: center; font-size: 11px; color: #64748B; margin-bottom: 14px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11px; }
        .info td { padding: 2px 4px; }
        .info .label { width: 130px; font-weight: bold; color: #334155; }
        table.data { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        table.data th, table.data td { border: 1px solid #94a3b8; padding: 5px 6px; text-align: left; }
        table.data th { background: #E2E8F0; color: #1A2B4C; font-weight: bold; text-align: center; }
        table.data td.c, table.data th.c { text-align: center; }
        .badge { font-weight: bold; padding: 1px 8px; border-radius: 10px; font-size: 9.5px; }
        .b-hadir { color: #15803d; } .b-izin { color: #b45309; }
        .b-sakit { color: #dc2626; } .b-alpha { color: #64748B; }
        .rekap { width: 100%; margin-top: 14px; border-collapse: collapse; font-size: 10.5px; }
        .rekap th, .rekap td { border: 1px solid #94a3b8; padding: 4px 8px; text-align: center; }
        .rekap th { background: #E2E8F0; color: #1A2B4C; }
        .catatan { margin-top: 10px; font-size: 10.5px; color: #334155; }
        .ttd { width: 100%; margin-top: 34px; font-size: 10.5px; }
        .ttd table { width: 100%; border-collapse: collapse; }
        .ttd td { text-align: center; vertical-align: top; padding: 0 8px; }
        .ttd .jab { font-weight: bold; color: #334155; }
        .ttd .nama { margin-top: 66px; font-weight: bold; text-decoration: underline; }
        .ttd .nim { font-size: 10px; color: #64748B; }
        .footer { margin-top: 20px; border-top: 1px solid #E2E8F0; padding-top: 6px; font-size: 9px; color: #94a3b8; text-align: center; }
        .status-strip { text-align: right; font-size: 10px; color: #1A2B4C; font-weight: bold; }
    </style>
</head>
<body>
    <div class="kop">
        <table>
            <tr>
                <td style="width: 76px;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo STTNI">
                </td>
                <td>
                    <div class="org">SEKOLAH TINGGI TEOLOGI NAZARENE INDONESIA</div>
                    <div class="addr">Jl. Kaliurang Km. 4,5, Yogyakarta &mdash; Sistem Pengarsipan Dokumen Digital</div>
                    <div class="addr">Email: info@sttni.ac.id &bull; Telp: (0274) 497045</div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <div class="tgl">Nomor Arsip: <b>{{ $rekap['nomor_arsip'] ?? '-' }}</b></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">Rekap Presensi Kegiatan UKM</div>
    <div class="subtitle">{{ $rekap['ukm']['name'] }}</div>

    <table class="info">
        <tr>
            <td class="label">Nama UKM</td>
            <td>: {{ $rekap['ukm']['name'] }}</td>
            <td class="label">Kode UKM</td>
            <td>: {{ $rekap['ukm']['kode'] }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Kegiatan</td>
            <td>: {{ $rekap['tanggal_kegiatan'] }}</td>
            <td class="label">Pertemuan Ke</td>
            <td>: {{ $rekap['pertemuan_ke'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Judul Kegiatan</td>
            <td>: {{ $rekap['judul_kegiatan'] ?: '-' }}</td>
            <td class="label">Pembina UKM</td>
            <td>: {{ $rekap['ukm']['pembina'] ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Diisi Oleh</td>
            <td>: {{ $rekap['pengisi'] }}</td>
            <td class="label">Ketua UKM</td>
            <td>: {{ $rekap['ukm']['ketua'] ?: '-' }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="c" style="width: 30px;">No</th>
                <th style="width: 90px;">NIM</th>
                <th>Nama</th>
                <th class="c" style="width: 80px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap['details'] as $d)
                <tr>
                    <td class="c">{{ $d['no'] }}</td>
                    <td>{{ $d['nim'] }}</td>
                    <td>{{ $d['nama'] }}</td>
                    <td class="c">
                        @php $cls = strtolower($d['status_kehadiran']); @endphp
                        <span class="badge b-{{ $cls === 'hadir' ? 'hadir' : ($cls === 'izin' ? 'izin' : ($cls === 'sakit' ? 'sakit' : 'alpha')) }}">{{ $d['status_kehadiran'] }}</span>
                    </td>
                    <td>{{ $d['keterangan'] ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="c">Tidak ada data anggota.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="rekap">
        <tr>
            <th>Hadir</th>
            <th>Izin</th>
            <th>Sakit</th>
            <th>Alpha</th>
            <th>Total Anggota</th>
        </tr>
        <tr>
            <td>{{ $rekap['rekap']['Hadir'] }}</td>
            <td>{{ $rekap['rekap']['Izin'] }}</td>
            <td>{{ $rekap['rekap']['Sakit'] }}</td>
            <td>{{ $rekap['rekap']['Alpha'] }}</td>
            <td><b>{{ $rekap['rekap']['total'] }}</b></td>
        </tr>
    </table>

    @if ($rekap['catatan'])
        <div class="catatan"><b>Catatan Pengisi:</b> {{ $rekap['catatan'] }}</div>
    @endif

    <div class="ttd">
        <table>
            <tr>
                <td style="width: 33%;">
                    <div class="jab">Pengisi Presensi,</div>
                    <div class="nama">{{ $rekap['pengisi'] }}</div>
                    <div class="nim">Sie Kesiswaan</div>
                </td>
                <td style="width: 33%;">
                    <div class="jab">Mengetahui,</div>
                    <div class="nama">{{ $rekap['ukm']['ketua'] ?: 'Ketua '.$rekap['ukm']['name'] }}</div>
                    <div class="nim">Ketua {{ $rekap['ukm']['name'] }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="jab">Admin Pengarsipan,</div>
                    <div class="nama">{{ $rekap['status_arsip'] === 'Terverifikasi' ? 'Super Admin STT' : '(................)' }}</div>
                    <div class="nim">{{ $rekap['status_arsip'] === 'Terverifikasi' ? 'Terverifikasi' : 'Menunggu Verifikasi' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dokumen dihasilkan otomatis oleh Sistem Pengarsipan E-Archive STTNI &mdash; {{ $rekap['tanggal_kegiatan'] }}
    </div>
</body>
</html>
