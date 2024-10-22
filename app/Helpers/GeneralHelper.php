<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('datetime_now')) {
    function datetime_now()
    {
        $utc_time = new DateTime("now", new DateTimeZone("UTC"));
        $utc_time->setTimezone(new DateTimeZone("Asia/Jakarta")); // Zona waktu +7
        return $utc_time->format("Y-m-d H:i:s");
    }
}

if (!function_exists('date_now')) {
    function date_now()
    {
        $utc_time = new DateTime("now", new DateTimeZone("UTC"));
        $utc_time->setTimezone(new DateTimeZone("Asia/Jakarta")); // Zona waktu +7
        return $utc_time->format("Y-m-d");
    }
}

if (!function_exists('base_url')) {
    function base_url($url = ""): string
    {
        $first = "";
        // if (env("APP_ENV") != "local") {
        //     $first = "/public";
        // }

        if (empty($url)) {
            return $first . "/" . Request::segments(1)[0];
        }
        if (!empty(Request::segments(1)[1])) {
            return $first . "/" . Request::segments(1)[0] . "/$url";
        }

        return $first . "/" . Request::segments(1)[0] . "/$url";
    }
}


if (!function_exists('random_str')) {
    function random_str($length = 10, $prefix = '', $suffix = '')
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = $prefix;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        $randomString .= $suffix;

        return $randomString;
    }
}

if (!function_exists('filename')) {
    function filename()
    {
        return random_str(20, date("Ymd") . "-");
    }
}

function rupiah($angka, $desimal = 0, $pemisah_ribuan = ".", $pemisah_desimal = ".")
{
    $angka = number_format($angka, $desimal, $pemisah_desimal, $pemisah_ribuan);
    $angka = "Rp" . $angka;

    return $angka;
}

function image($type, $file): string
{
    $thumbnail = url("files/$type/" . $file);

    if (empty($file)) {
        if (in_array($type, ["tour_packages", "rents"])) {
            $thumbnail = url('files/tour_packages/empty.jpg');
        } else {
            $thumbnail = url('assets/admin/media/empty.png');
        }
    }

    return $thumbnail;
}


function shortRupiah($n, $presisi = 1)
{
    if ($n == 0) {
        return "0 rupiah";
    }

    if ($n < 900) {
        $format_angka = number_format($n, $presisi);
        $simbol = '';
    } elseif ($n < 900000) {
        $format_angka = number_format($n / 1000, $presisi);
        $simbol = ' Ribuan';
    } elseif ($n < 900000000) {
        $format_angka = number_format($n / 1000000, $presisi);
        $simbol = ' Jutaan';
    } elseif ($n < 900000000000) {
        $format_angka = number_format($n / 1000000000, $presisi);
        $simbol = 'M';
    } else {
        $format_angka = number_format($n / 1000000000000, $presisi);
        $simbol = 'T';
    }

    if ($presisi > 0) {
        $pisah = '.' . str_repeat('0', $presisi);
        $format_angka = str_replace($pisah, '', $format_angka);
    }

    return $format_angka . $simbol;
}

function title($str = "", $memberCtg = 2)
{
    if ($memberCtg == 2) {
        return ucwords(strtolower($str));
    }

    return $str;
}

/**
 * Convert Datetime to String
 * Example return: 17 Maret 2024 13:47 WIB
 *
 * @param [string] $tanggal
 * @return string
 */
function getStrDatetime($tanggal)
{
    // Array untuk nama-nama bulan dalam Bahasa Indonesia
    $bulanIndonesia = array(
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    );

    // Mendapatkan tanggal, bulan, tahun, jam, dan zona waktu dari input
    $tanggalUnix = strtotime($tanggal);
    $tanggalOutput = date('d', $tanggalUnix);
    $bulanOutput = $bulanIndonesia[date('m', $tanggalUnix)];
    $tahunOutput = date('Y', $tanggalUnix);
    $jamOutput = date('H:i', $tanggalUnix);

    // Mengembalikan tanggal yang telah dikonversi dengan format yang diinginkan
    return $tanggalOutput . ' ' . $bulanOutput . ' ' . $tahunOutput . ' ' . $jamOutput . '';
}

if (!function_exists('cutText()')) {
    function cutText($text, $length, $end = '')
    {
        if (strlen($text) <= $length) return $text;
        $new = substr($text, 0, $length);

        return $new . $end;
    }
}

function formatedDate($dateString, $withDay = true)
{
    // Konversi string tanggal ke timestamp
    $timestamp = strtotime($dateString);

    // Array untuk nama hari dalam bahasa Indonesia
    $days = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ];

    // Array untuk nama bulan dalam bahasa Indonesia
    $months = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    // Mendapatkan nama hari
    $dayName = $days[date('w', $timestamp)];

    // Mendapatkan hari, bulan, dan tahun
    $day = date('d', $timestamp);
    $monthName = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);

    // Menggabungkan semuanya menjadi format yang diinginkan
    if ($withDay) {
        return "{$dayName}, {$day} {$monthName} {$year}";
    }
    return "{$day} {$monthName} {$year}";
}

function formatedDateTime($dateTimeString)
{
    // Membuat objek DateTime dari string yang diberikan
    $dateTime = new DateTime($dateTimeString);

    // Set timezone ke Asia/Jakarta (WIB)
    $dateTime->setTimezone(new DateTimeZone('UTC'));

    // Array untuk nama hari dalam bahasa Indonesia
    $days = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ];

    // Array untuk nama bulan dalam bahasa Indonesia
    $months = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    // Mendapatkan nama hari
    $dayName = $days[$dateTime->format('w')];

    // Mendapatkan hari, bulan, dan tahun
    $day = $dateTime->format('d');
    $monthName = $months[$dateTime->format('n')];
    $year = $dateTime->format('Y');

    // Mendapatkan waktu (jam dan menit)
    $time = $dateTime->format('H:i');

    // Menggabungkan semuanya menjadi format yang diinginkan
    return "{$dayName}, {$day} {$monthName} {$year} {$time}";
}

function checkLateReturn($returnDateString, $dueDateString)
{
    // Membuat objek DateTime dari tanggal pengembalian dan tanggal jatuh tempo
    $returnDate = new DateTime($returnDateString);
    $dueDate = new DateTime($dueDateString);

    // Menghitung selisih antara tanggal pengembalian dan tanggal jatuh tempo
    $interval = $dueDate->diff($returnDate);

    // Jika tanggal pengembalian lebih besar dari tanggal jatuh tempo
    if ($returnDate > $dueDate) {
        return $interval->days; // Mengembalikan jumlah hari keterlambatan
    } else {
        return 0; // Tidak terlambat
    }
}

function sisaHariPeminjaman($tanggalKembali)
{
    // Mengubah input tanggal menjadi objek DateTime
    $tanggalKembaliObj = new DateTime($tanggalKembali);

    // Mendapatkan tanggal hari ini
    $tanggalHariIni = new DateTime(date_now());

    // Menghitung selisih hari antara tanggal kembali dan hari ini
    $selisihHari = $tanggalKembaliObj->diff($tanggalHariIni);

    // Mengembalikan jumlah hari
    return $selisihHari->days;
}


function getBookSourceName($source)
{
    $sources = [
        "PEMBELIAN"     => 1,
        "HADIAH"        => 2,
        "SUMBANGAN"     => 3,
        "TUKAR MENUKAR" => 4,
        "LAIN - LAIN"   => 5,
        "-"   => 5,
    ];

    if (array_key_exists($source, $sources)) {
        return $sources[$source];
    } else {
        return null; // atau bisa diganti dengan nilai default jika deskripsi tidak ditemukan
    }
}
function getBookSourceById($id)
{
    $sources = [
        1 => "PEMBELIAN",
        2 => "HADIAH",
        3 => "SUMBANGAN",
        4 => "TUKAR MENUKAR",
        5 => "LAIN - LAIN"
    ];

    if (array_key_exists($id, $sources)) {
        return $sources[$id];
    } else {
        return null; // atau bisa diganti dengan nilai default jika ID tidak ditemukan
    }
}

function getMemberCtg($id)
{
    $sources = [
        1 => "Guru",
        2 => "Siswa",
        3 => "Pegawai Operasional",
        4 => "LAIN - LAIN"
    ];

    if (array_key_exists($id, $sources)) {
        return $sources[$id];
    } else {
        return null; // atau bisa diganti dengan nilai default jika ID tidak ditemukan
    }
}


function getMemberIdentityCtg($id)
{
    $sources = [
        1 => "ID Perpus",
        2 => "NISN",
        3 => "ID Perpus",
        4 => "ID"
    ];

    if (array_key_exists($id, $sources)) {
        return $sources[$id];
    } else {
        return null; // atau bisa diganti dengan nilai default jika ID tidak ditemukan
    }
}


function get($data): string {
    return $data ?? "";
}

function render_view($view_name, $data = [])
{
    // Cek apakah request berasal dari AJAX
    if (request()->ajax()) {
        // Hanya kembalikan konten view tanpa layout
        return view($view_name, $data);
    } else {
        // Kembalikan layout utama dengan konten view
        return view('_admin/_layout/app')->with('content', view($view_name, $data)->render());
    }
}

function getUserAccessType($id)
{
    $data = [
        1 => "Admin (Semua Akses)",
        2 => "Pegawai",
    ];

    if (array_key_exists($id, $data)) {
        return $data[$id];
    } else {
        return null; // atau bisa diganti dengan nilai default jika ID tidak ditemukan
    }
}