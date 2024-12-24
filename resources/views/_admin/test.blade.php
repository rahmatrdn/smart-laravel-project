<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label PDF</title>

    <style>
        @page {
            margin: 5px;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }

        .p-1 {
            padding: 0.25rem !important;
        }

        .header {
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .item-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .item-detail {
            font-size: 8px;
            margin-bottom: 1px;
        }

        .img-fluid {
            max-width: 100%;
            height: auto;
        }

        .label-container {
            display: inline-block;
            width: 150px;
            /* Sesuaikan ukuran kartu */
            height: 120px;
            border: 1px solid #000;
            /* Hanya untuk contoh */
            margin: 5px;
            /* Spasi antar kartu */
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($items as $item)
        <div class="" style="text-align: center;">
            <div class="">
                <img src="{{ $item['qr_code_base64'] }}" class="img-fluid" alt="QR Code" width="40">
                <p style="margin-bottom: 3px; margin-top: 3px">ID {{ $item['id'] }}</p>
                <div class="">
                    <div class="item-name">{{ $item['nama_item'] }}</div>
                    <div class="item-detail">{{ $item['merk_item'] }}</div>
                    <div class="item-detail">{{ $item['kode'] }}</div>
                    <div class="header mb-1">{{ $item['lokasi'] }}</div>
                </div>
            </div>
        </div>
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>

</html>
