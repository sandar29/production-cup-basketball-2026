<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'koneksi.php';

// =====================================================
// AMBIL DATA PERTANDINGAN
// =====================================================
$matches_res = $conn->query("
    SELECT *
    FROM matches
    ORDER BY id ASC
");

$matches = [];
$total_done = 0;
$total_matches = 0;

while ($row = $matches_res->fetch_assoc()) {

    $matches[] = $row;
    $total_matches++;

    if (
        $row['status'] === 'Selesai' &&
        strpos($row['fase'], 'Penyisihan') !== false
    ) {
        $total_done++;
    }
}


// =====================================================
// AMBIL DATA TIM
// =====================================================
$teams_res = $conn->query("
    SELECT *
    FROM teams
    ORDER BY id ASC
");

$standings = [];

while ($t = $teams_res->fetch_assoc()) {

    $standings[$t['code']] = [
        'code' => $t['code'],
        'name' => $t['name'],

        // Statistik pertandingan
        'played' => 0,
        'won' => 0,
        'lost' => 0,

        // Statistik poin basket
        'pf' => 0,
        'pa' => 0,
        'diff' => 0,

        // Poin klasemen
        'points' => 0
    ];
}


// =====================================================
// HITUNG KLASEMEN
// HANYA PERTANDINGAN PENYISIHAN YANG SELESAI
//
// MENANG = 2 POIN
// KALAH  = 1 POIN
// =====================================================
foreach ($matches as $m) {

    if (
        $m['status'] === 'Selesai' &&
        strpos($m['fase'], 'Penyisihan') !== false
    ) {

        $teamA = $m['team_a'];
        $teamB = $m['team_b'];

        $scoreA = max(0, intval($m['score_a']));
        $scoreB = max(0, intval($m['score_b']));

        // Pastikan kedua tim ada di tabel teams
        if (
            isset($standings[$teamA]) &&
            isset($standings[$teamB])
        ) {

            // =================================================
            // JUMLAH PERTANDINGAN
            // =================================================
            $standings[$teamA]['played']++;
            $standings[$teamB]['played']++;

            // =================================================
            // POIN MASUK (PF)
            // =================================================
            $standings[$teamA]['pf'] += $scoreA;
            $standings[$teamB]['pf'] += $scoreB;

            // =================================================
            // POIN KEMASUKAN (PA)
            // =================================================
            $standings[$teamA]['pa'] += $scoreB;
            $standings[$teamB]['pa'] += $scoreA;

            // =================================================
            // HASIL PERTANDINGAN
            // MENANG = 2
            // KALAH  = 1
            // =================================================
            if ($scoreA > $scoreB) {

                // Team A MENANG
                $standings[$teamA]['won']++;
                $standings[$teamA]['points'] += 2;

                // Team B KALAH
                $standings[$teamB]['lost']++;
                $standings[$teamB]['points'] += 1;

            } elseif ($scoreB > $scoreA) {

                // Team B MENANG
                $standings[$teamB]['won']++;
                $standings[$teamB]['points'] += 2;

                // Team A KALAH
                $standings[$teamA]['lost']++;
                $standings[$teamA]['points'] += 1;
            }
        }
    }
}


// =====================================================
// HITUNG DIFF
// DIFF = PF - PA
// =====================================================
foreach ($standings as &$team) {

    $team['diff'] = $team['pf'] - $team['pa'];
}

unset($team);


// =====================================================
// URUTKAN KLASEMEN
//
// PRIORITAS:
// 1. Poin Klasemen
// 2. DIFF
// 3. PF
// 4. Urutan kode tim
// =====================================================
usort($standings, function ($a, $b) {

    // 1. POIN KLASemen
    if ($b['points'] !== $a['points']) {
        return $b['points'] <=> $a['points'];
    }

    // 2. DIFFERENCE
    if ($b['diff'] !== $a['diff']) {
        return $b['diff'] <=> $a['diff'];
    }

    // 3. POIN MASUK
    if ($b['pf'] !== $a['pf']) {
        return $b['pf'] <=> $a['pf'];
    }

    // 4. KODE TIM
    return strcmp($a['code'], $b['code']);
});


// =====================================================
// CARI PERTANDINGAN AKTIF / TERDEKAT
// =====================================================
$next_match = null;

foreach ($matches as $m) {

    if (
        $m['status'] === 'Berlangsung' ||
        $m['status'] === 'Belum Dimulai'
    ) {

        $next_match = $m;
        break;
    }
}

if (!$next_match && count($matches) > 0) {
    $next_match = $matches[0];
}


// =====================================================
// HITUNG STATUS LIVE
// =====================================================
$live_match = null;

foreach ($matches as $m) {

    if ($m['status'] === 'Berlangsung') {

        $live_match = $m;
        break;
    }
}


// =====================================================
// HELPER ESCAPE HTML
// =====================================================
function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Basketball Production Cup 2026
    </title>

    <link
        rel="icon"
        type="image/png"
        href="assets/logo.png"
    >

    <!-- =================================================
    FONT
    ================================================== -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- =================================================
    FONT AWESOME
    ================================================== -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- =================================================
    TAILWIND
    ================================================== -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>

        tailwind.config = {

            theme: {

                extend: {

                    colors: {

                        navy: '#0B1F3A',
                        navy2: '#132B4F',
                        gold: '#C89B3C',
                        goldLight: '#E6CB82'

                    }

                }

            }

        }

    </script>


    <!-- =================================================
    CUSTOM CSS
    ================================================== -->
    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            margin: 0;

            font-family: 'Inter', sans-serif;

            background: #F7F8FA;

            color: #182235;

        }

        .heading {

            font-family: 'Montserrat', sans-serif;

        }


        /* =================================================
        NAVBAR
        ================================================= */

        .navbar {

            background: rgba(255,255,255,.96);

            border-bottom: 1px solid #E5E8ED;

            backdrop-filter: blur(12px);

        }

        .brand-icon {

            width: 42px;
            height: 42px;

            border-radius: 11px;

            background: #0B1F3A;

            color: #C89B3C;

            display: flex;

            align-items: center;

            justify-content: center;

            box-shadow:
                0 7px 18px rgba(11,31,58,.13);

        }

        .brand-name {

            font-family: 'Montserrat', sans-serif;

            font-weight: 800;

            color: #0B1F3A;

            line-height: 1;

        }

        .brand-sub {

            font-size: 9px;

            color: #8B94A3;

            font-weight: 700;

            letter-spacing: 1.4px;

            text-transform: uppercase;

            margin-top: 5px;

        }

        .nav-link {

            color: #687386;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;

        }

        .nav-link:hover {

            color: #0B1F3A;

        }

        .nav-active {

            color: #0B1F3A;

        }

        .login-btn {

            background: #0B1F3A;

            color: #FFFFFF;

            padding: 9px 15px;

            border-radius: 9px;

            font-size: 12px;

            font-weight: 700;

            transition: .2s;

        }

        .login-btn:hover {

            background: #132B4F;

            transform: translateY(-1px);

        }


        /* =================================================
        MOBILE NAVBAR
        ================================================= */

        .mobile-menu-button {

            width: 40px;

            height: 40px;

            border-radius: 10px;

            border: 1px solid #E1E5EA;

            background: #FFFFFF;

            color: #0B1F3A;

            display: none;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            transition: .2s;

        }

        .mobile-menu-button:hover {

            background: #F5F7F9;

        }

        .mobile-menu {

            display: none;

            border-top: 1px solid #E5E8ED;

            background: rgba(255,255,255,.98);

        }

        .mobile-menu.active {

            display: block;

        }

        .mobile-menu-inner {

            padding: 10px 20px 16px;

        }

        .mobile-nav-link {

            display: flex;

            align-items: center;

            gap: 12px;

            width: 100%;

            padding: 12px 13px;

            margin-top: 3px;

            border-radius: 9px;

            color: #687386;

            font-size: 13px;

            font-weight: 600;

            text-decoration: none;

            transition: .2s;

        }

        .mobile-nav-link i {

            width: 18px;

            text-align: center;

            color: #8B94A3;

        }

        .mobile-nav-link:hover {

            background: #F5F7F9;

            color: #0B1F3A;

        }

        .mobile-nav-link:hover i {

            color: #C89B3C;

        }

        .mobile-nav-link.active {

            background: #F5F7F9;

            color: #0B1F3A;

        }

        .mobile-nav-link.active i {

            color: #C89B3C;

        }

        .mobile-admin {

            margin-top: 10px;

            background: #0B1F3A;

            color: #FFFFFF;

            justify-content: center;

        }

        .mobile-admin i {

            color: #C89B3C;

        }

        .mobile-admin:hover {

            background: #132B4F;

            color: #FFFFFF;

        }


        /* =================================================
        HERO
        ================================================= */

        .hero {

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #FFFFFF 0%,
                    #F7F9FC 60%,
                    #F1F4F8 100%
                );

            border-bottom: 1px solid #E5E8ED;

        }

        .hero-grid {

            position: absolute;

            inset: 0;

            opacity: .45;

            background-image:

                linear-gradient(
                    rgba(11,31,58,.025) 1px,
                    transparent 1px
                ),

                linear-gradient(
                    90deg,
                    rgba(11,31,58,.025) 1px,
                    transparent 1px
                );

            background-size: 40px 40px;

        }

        .hero-content {

            position: relative;

            z-index: 2;

        }

        .hero-badge {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 7px 12px;

            border-radius: 999px;

            border: 1px solid #E5D3A5;

            background: #FFFDF7;

            color: #87671E;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .7px;

            text-transform: uppercase;

        }

        .hero-title {

            font-family: 'Montserrat', sans-serif;

            font-weight: 800;

            color: #0B1F3A;

            letter-spacing: -1.8px;

            line-height: 1.05;

        }

        .hero-title span {

            color: #C89B3C;

        }

        .hero-description {

            color: #687386;

            line-height: 1.8;

            max-width: 650px;

        }


        /* =================================================
        COURT VISUAL
        ================================================= */

        .court-container {

            position: relative;

            height: 320px;

            display: flex;

            align-items: center;

            justify-content: center;

        }

        .court {

            position: relative;

            width: 100%;

            max-width: 510px;

            height: 275px;

            background: #FFFFFF;

            border: 1px solid #DDE2E8;

            border-radius: 22px;

            box-shadow:
                0 22px 50px rgba(11,31,58,.08);

            overflow: hidden;

        }

        .court-border {

            position: absolute;

            inset: 27px;

            border: 2px solid #DDE2E8;

            border-radius: 8px;

        }

        .court-center {

            position: absolute;

            top: 27px;

            bottom: 27px;

            left: 50%;

            width: 2px;

            background: #DDE2E8;

        }

        .center-circle {

            position: absolute;

            width: 82px;

            height: 82px;

            border: 2px solid #DDE2E8;

            border-radius: 50%;

            left: 50%;

            top: 50%;

            transform: translate(-50%,-50%);

        }

        .key {

            position: absolute;

            top: 50%;

            transform: translateY(-50%);

            width: 85px;

            height: 125px;

            border: 2px solid #DDE2E8;

        }

        .key-left {

            left: 27px;

            border-left: 0;

            border-radius: 0 65px 65px 0;

        }

        .key-right {

            right: 27px;

            border-right: 0;

            border-radius: 65px 0 0 65px;

        }

        .basketball-icon {

            position: absolute;

            width: 82px;

            height: 82px;

            left: 50%;

            top: 50%;

            transform: translate(-50%,-50%);

            background: #0B1F3A;

            color: #C89B3C;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 31px;

            box-shadow:
                0 15px 30px rgba(11,31,58,.17);

            z-index: 5;

        }

        .court-label {

            position: absolute;

            left: 30px;

            top: 18px;

            font-size: 8px;

            color: #929AA6;

            font-weight: 800;

            letter-spacing: 1.5px;

            text-transform: uppercase;

        }

        .court-label-right {

            position: absolute;

            right: 30px;

            bottom: 18px;

            font-size: 8px;

            color: #C89B3C;

            font-weight: 800;

            letter-spacing: 1.5px;

            text-transform: uppercase;

        }


        /* =================================================
        STAT CARDS
        ================================================= */

        .stat-card {

            background: #FFFFFF;

            border: 1px solid #E3E7EC;

            border-radius: 13px;

            padding: 16px;

            box-shadow:
                0 7px 20px rgba(11,31,58,.035);

            transition: .2s;

        }

        .stat-card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 28px rgba(11,31,58,.065);

        }

        .stat-icon {

            width: 34px;

            height: 34px;

            border-radius: 9px;

            background: #F1F4F7;

            color: #0B1F3A;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 13px;

        }


        /* =================================================
        SECTION
        ================================================= */

        .section-line {

            width: 36px;

            height: 3px;

            border-radius: 10px;

            background: #C89B3C;

        }

        .section-title {

            font-family: 'Montserrat', sans-serif;

            font-weight: 800;

            color: #0B1F3A;

        }

        .section-sub {

            color: #87909F;

            font-size: 12px;

        }


        /* =================================================
        PREMIUM CARD
        ================================================= */

        .premium-card {

            background: #FFFFFF;

            border: 1px solid #E2E6EB;

            border-radius: 17px;

            box-shadow:
                0 8px 28px rgba(11,31,58,.045);

        }


        /* =================================================
        NEXT MATCH
        ================================================= */

        .match-card {

            overflow: hidden;

        }

        .match-header {

            background: #F6F8FA;

            border-bottom: 1px solid #E7EAEF;

        }

        .status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 6px 10px;

            border-radius: 999px;

            font-size: 9px;

            font-weight: 800;

            letter-spacing: .3px;

        }

        .status-live {

            color: #B42318;

            background: #FEF3F2;

            border: 1px solid #FECACA;

        }

        .status-upcoming {

            color: #86651B;

            background: #FFF9E7;

            border: 1px solid #F1DE9D;

        }

        .status-done {

            color: #087443;

            background: #ECFDF3;

            border: 1px solid #BBE8CF;

        }

        .team-box {

            width: 64px;

            height: 64px;

            border-radius: 15px;

            background: #F1F4F7;

            border: 1px solid #DDE2E8;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #0B1F3A;

            font-family: 'Montserrat', sans-serif;

            font-size: 13px;

            font-weight: 800;

        }

        .vs {

            font-family: 'Montserrat', sans-serif;

            color: #A0A8B4;

            font-size: 11px;

            font-weight: 800;

        }

        .big-score {

            font-family: 'Montserrat', sans-serif;

            color: #0B1F3A;

            font-size: 30px;

            font-weight: 800;

        }


        /* =================================================
        TABLE
        ================================================= */

        .table-wrap {

            overflow-x: auto;

        }

        .standings {

            width: 100%;

            border-collapse: collapse;

            min-width: 900px;

        }

        .standings thead {

            background: #F6F8FA;

        }

        .standings th {

            padding: 13px 15px;

            border-bottom: 1px solid #E5E8ED;

            color: #8A93A1;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: .7px;

            font-weight: 800;

            text-align: center;

            white-space: nowrap;

        }

        .standings th:nth-child(2) {

            text-align: left;

        }

        .standings td {

            padding: 14px 15px;

            border-bottom: 1px solid #EEF0F3;

            color: #647084;

            font-size: 12px;

            text-align: center;

            white-space: nowrap;

        }

        .standings td:nth-child(2) {

            text-align: left;

        }

        .standings tbody tr {

            transition: .15s;

        }

        .standings tbody tr:hover {

            background: #FBFCFD;

        }

        .rank {

            width: 27px;

            height: 27px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            background: #F0F2F5;

            color: #697383;

            font-size: 10px;

            font-weight: 800;

        }

        .rank-top {

            background: #FFF6DA;

            color: #8A681A;

        }

        .team-code {

            width: 35px;

            height: 35px;

            border-radius: 9px;

            background: #F1F4F7;

            border: 1px solid #E0E4E9;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #0B1F3A;

            font-size: 9px;

            font-family: 'Montserrat', sans-serif;

            font-weight: 800;

        }


        /* =================================================
        SCHEDULE
        ================================================= */

        .schedule-card {

            background: #FFFFFF;

            border: 1px solid #E3E7EC;

            border-radius: 15px;

            padding: 18px;

            transition: .2s;

        }

        .schedule-card:hover {

            transform: translateY(-2px);

            box-shadow:
                0 10px 28px rgba(11,31,58,.055);

            border-color: #D8DEE6;

        }

        .schedule-team {

            font-weight: 700;

            color: #0B1F3A;

            font-size: 12px;

        }

        .schedule-score {

            font-family: 'Montserrat', sans-serif;

            color: #0B1F3A;

            font-weight: 800;

            font-size: 14px;

        }


        /* =================================================
        FOOTER
        ================================================= */

        footer {

            background: #0B1F3A;

            color: #FFFFFF;

        }

        .footer-muted {

            color: #AEB8C7;

        }


        /* =================================================
        MOBILE
        ================================================= */

        @media (max-width: 768px) {

            .desktop-nav {

                display: none !important;

            }

            .mobile-menu-button {

                display: flex;

            }

            .hero-title {

                font-size: 37px;

            }

            .court-container {

                height: 245px;

            }

            .court {

                height: 210px;

            }

            .court-border {

                inset: 20px;

            }

            .court-center {

                top: 20px;

                bottom: 20px;

            }

            .center-circle {

                width: 60px;

                height: 60px;

            }

            .key {

                width: 65px;

                height: 90px;

            }

            .key-left {

                left: 20px;

            }

            .key-right {

                right: 20px;

            }

            .basketball-icon {

                width: 64px;

                height: 64px;

                font-size: 24px;

            }

        }


        @media (max-width: 500px) {

            .hero-title {

                font-size: 32px;

            }

            .hero-description {

                font-size: 13px;

            }

            .stat-card {

                padding: 12px;

            }

            .stat-card .number {

                font-size: 19px;

            }

            .team-box {

                width: 52px;

                height: 52px;

            }

            .big-score {

                font-size: 24px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
NAVBAR
========================================================= -->

<nav class="navbar sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-5 md:px-6">

        <div class="h-[70px] flex items-center justify-between">


            <!-- BRAND -->

            <a
                href="index.php"
                class="flex items-center gap-3"
            >

                <div class="brand-icon">

                    <i class="fa-solid fa-basketball"></i>

                </div>

                <div>

                    <div class="brand-name text-sm md:text-base">

                        Production Cup

                    </div>

                    <div class="brand-sub">

                        Basketball Tournament

                    </div>

                </div>

            </a>


            <!-- DESKTOP NAVIGATION -->

            <div class="desktop-nav hidden md:flex items-center gap-8">

                <a
                    href="index.php"
                    class="nav-link nav-active"
                >
                    Beranda
                </a>

                <a
                    href="#jadwal"
                    class="nav-link"
                >
                    Jadwal
                </a>

                <a
                    href="hasil.php"
                    class="nav-link"
                >
                    Hasil
                </a>

                <a
                    href="#klasemen"
                    class="nav-link"
                >
                    Klasemen
                </a>

            </div>


            <!-- DESKTOP LOGIN -->

            <a
                href="login.php"
                class="login-btn hidden sm:flex items-center gap-2"
            >

                <i class="fa-solid fa-right-to-bracket"></i>

                <span>
                    Admin
                </span>

            </a>


            <!-- MOBILE MENU BUTTON -->

            <button
                type="button"
                id="mobileMenuButton"
                class="mobile-menu-button"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
            >

                <i
                    id="mobileMenuIcon"
                    class="fa-solid fa-bars"
                ></i>

            </button>

        </div>


        <!-- =================================================
        MOBILE MENU
        ================================================= -->

        <div
            id="mobileMenu"
            class="mobile-menu"
        >

            <div class="mobile-menu-inner">

                <a
                    href="index.php"
                    class="mobile-nav-link active"
                    onclick="closeMobileMenu()"
                >

                    <i class="fa-solid fa-house"></i>

                    <span>
                        Beranda
                    </span>

                </a>


                <a
                    href="#jadwal"
                    class="mobile-nav-link"
                    onclick="closeMobileMenu()"
                >

                    <i class="fa-solid fa-calendar-days"></i>

                    <span>
                        Jadwal
                    </span>

                </a>


                <a
                    href="hasil.php"
                    class="mobile-nav-link"
                    onclick="closeMobileMenu()"
                >

                    <i class="fa-solid fa-chart-column"></i>

                    <span>
                        Hasil
                    </span>

                </a>


                <a
                    href="#klasemen"
                    class="mobile-nav-link"
                    onclick="closeMobileMenu()"
                >

                    <i class="fa-solid fa-ranking-star"></i>

                    <span>
                        Klasemen
                    </span>

                </a>


                <a
                    href="login.php"
                    class="mobile-nav-link mobile-admin"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    <span>
                        Admin
                    </span>

                </a>

            </div>

        </div>

    </div>

</nav>


<!-- =========================================================
HERO
========================================================= -->

<section class="hero">

    <div class="hero-grid"></div>

    <div class="max-w-7xl mx-auto px-5 md:px-6 py-14 md:py-20">

        <div class="hero-content">

            <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">


                <!-- HERO LEFT -->

                <div>

                    <div class="hero-badge mb-6">

                        <i class="fa-solid fa-shield-halved"></i>

                        Turnamen Resmi • Musim 2026

                    </div>


                    <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl">

                        Basketball

                        <br>

                        <span>
                            Production Cup
                        </span>

                        <br>

                        2026

                    </h1>


                    <p class="hero-description mt-6 text-sm md:text-base">

                        Platform informasi resmi pertandingan Basketball Production Cup 2026.
                        Pantau jadwal, hasil pertandingan, dan klasemen secara terpusat.

                    </p>


                    <!-- BUTTON -->

                    <div class="flex flex-wrap gap-3 mt-8">

                        <a
                            href="#jadwal"
                            class="px-5 py-3 rounded-xl bg-navy text-white text-xs font-bold flex items-center gap-2 hover:bg-navy2 transition"
                        >

                            <i class="fa-solid fa-calendar-days"></i>

                            Lihat Jadwal

                        </a>


                        <a
                            href="#klasemen"
                            class="px-5 py-3 rounded-xl bg-white border border-gray-300 text-navy text-xs font-bold flex items-center gap-2 hover:bg-gray-50 transition"
                        >

                            <i class="fa-solid fa-ranking-star"></i>

                            Lihat Klasemen

                        </a>

                    </div>


                    <!-- STATISTICS -->

                    <div class="grid grid-cols-3 gap-3 mt-9 max-w-lg">


                        <!-- TEAMS -->

                        <div class="stat-card">

                            <div class="stat-icon">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <div class="mt-3">

                                <div class="heading font-extrabold text-xl text-navy">

                                    <?= count($standings) ?>

                                </div>

                                <div class="text-[10px] text-gray-500">

                                    Tim Peserta

                                </div>

                            </div>

                        </div>


                        <!-- MATCH -->

                        <div class="stat-card">

                            <div class="stat-icon">

                                <i class="fa-solid fa-basketball"></i>

                            </div>

                            <div class="mt-3">

                                <div class="heading font-extrabold text-xl text-navy">

                                    <?= $total_done ?>

                                </div>

                                <div class="text-[10px] text-gray-500">

                                    Selesai

                                </div>

                            </div>

                        </div>


                        <!-- TOTAL -->

                        <div class="stat-card">

                            <div class="stat-icon">

                                <i class="fa-solid fa-list-check"></i>

                            </div>

                            <div class="mt-3">

                                <div class="heading font-extrabold text-xl text-navy">

                                    <?= $total_matches ?>

                                </div>

                                <div class="text-[10px] text-gray-500">

                                    Total Match

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- HERO RIGHT -->

                <div class="court-container">

                    <div class="court">

                        <div class="court-label">

                            Official Tournament

                        </div>

                        <div class="court-label-right">

                            Production Cup 2026

                        </div>

                        <div class="court-border"></div>

                        <div class="court-center"></div>

                        <div class="center-circle"></div>

                        <div class="key key-left"></div>

                        <div class="key key-right"></div>

                        <div class="basketball-icon">

                            <i class="fa-solid fa-basketball"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
MAIN
========================================================= -->

<main class="max-w-7xl mx-auto px-5 md:px-6 py-14">


    <!-- =====================================================
    LIVE MATCH
    ===================================================== -->

    <?php if ($live_match): ?>

        <section class="mb-14">

            <div class="flex items-center gap-3 mb-5">

                <div class="section-line"></div>

                <div>

                    <div class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">

                        Live Match

                    </div>

                    <h2 class="section-title text-2xl">

                        Pertandingan Sedang Berlangsung

                    </h2>

                </div>

            </div>


            <div class="premium-card match-card">

                <div class="match-header px-5 py-4">

                    <div class="flex flex-wrap justify-between items-center gap-3">

                        <div>

                            <div class="font-bold text-navy text-xs">

                                <?= e($live_match['match_code']) ?>

                                •

                                <?= e($live_match['fase']) ?>

                            </div>

                            <div class="text-[10px] text-gray-500 mt-1">

                                <?= e($live_match['hari']) ?>

                                •

                                <?= e($live_match['jam']) ?>

                            </div>

                        </div>


                        <span class="status status-live">

                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>

                            LIVE

                        </span>

                    </div>

                </div>


                <div class="p-8 md:p-10">

                    <div class="grid grid-cols-3 items-center">


                        <div class="text-center">

                            <div class="team-box mx-auto mb-3">

                                <?= e($live_match['team_a']) ?>

                            </div>

                            <div class="font-bold text-navy text-sm">

                                <?= e($live_match['team_a']) ?>

                            </div>

                        </div>


                        <div class="text-center">

                            <div class="big-score">

                                <?= intval($live_match['score_a']) ?>

                                <span class="text-gray-300 mx-1">
                                    :
                                </span>

                                <?= intval($live_match['score_b']) ?>

                            </div>

                            <div class="vs mt-2">

                                LIVE SCORE

                            </div>

                        </div>


                        <div class="text-center">

                            <div class="team-box mx-auto mb-3">

                                <?= e($live_match['team_b']) ?>

                            </div>

                            <div class="font-bold text-navy text-sm">

                                <?= e($live_match['team_b']) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
    NEXT MATCH
    ===================================================== -->

    <?php if ($next_match && !$live_match): ?>

        <section class="mb-14">

            <div class="flex items-center gap-3 mb-5">

                <div class="section-line"></div>

                <div>

                    <div class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">

                        Upcoming

                    </div>

                    <h2 class="section-title text-2xl">

                        Pertandingan Berikutnya

                    </h2>

                </div>

            </div>


            <div class="premium-card match-card">

                <div class="match-header px-5 py-4">

                    <div class="flex flex-wrap justify-between items-center gap-3">

                        <div>

                            <div class="font-bold text-navy text-xs">

                                <?= e($next_match['match_code']) ?>

                                •

                                <?= e($next_match['fase']) ?>

                            </div>

                            <div class="text-[10px] text-gray-500 mt-1">

                                <?= e($next_match['hari']) ?>

                                •

                                <?= e($next_match['jam']) ?>

                            </div>

                        </div>


                        <span class="status status-upcoming">

                            <i class="fa-regular fa-clock"></i>

                            BELUM DIMULAI

                        </span>

                    </div>

                </div>


                <div class="p-8 md:p-10">

                    <div class="grid grid-cols-3 items-center">


                        <div class="text-center">

                            <div class="team-box mx-auto mb-3">

                                <?= e($next_match['team_a']) ?>

                            </div>

                            <div class="font-bold text-navy text-sm">

                                <?= e($next_match['team_a']) ?>

                            </div>

                        </div>


                        <div class="text-center">

                            <div class="vs">

                                VS

                            </div>

                        </div>


                        <div class="text-center">

                            <div class="team-box mx-auto mb-3">

                                <?= e($next_match['team_b']) ?>

                            </div>

                            <div class="font-bold text-navy text-sm">

                                <?= e($next_match['team_b']) ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
    KLASEMEN
    ===================================================== -->

    <section
        id="klasemen"
        class="mb-14"
    >

        <div class="mb-5">

            <div class="flex items-center gap-3 mb-2">

                <div class="section-line"></div>

                <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">

                    Standings

                </span>

            </div>

            <h2 class="section-title text-2xl md:text-3xl">

                Klasemen Sementara

            </h2>

            <p class="section-sub mt-1">

                Perhitungan berdasarkan hasil pertandingan fase penyisihan.

            </p>

        </div>


        <div class="premium-card overflow-hidden">

            <div class="table-wrap">

                <table class="standings">

                    <thead>

                        <tr>

                            <th>
                                Pos
                            </th>

                            <th>
                                Tim
                            </th>

                            <th>
                                Main (P)
                            </th>

                            <th>
                                Menang (W)
                            </th>

                            <th>
                                Kalah (L)
                            </th>

                            <th>
                                Poin Masuk (PF)
                            </th>

                            <th>
                                Poin Kemasukan (PA)
                            </th>

                            <th>
                                Selisih (DIFF)
                            </th>

                            <th>
                                Poin Klasemen
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (count($standings) > 0): ?>

                            <?php foreach ($standings as $index => $team): ?>

                                <tr>


                                    <!-- POS -->

                                    <td>

                                        <span
                                            class="rank <?= $index < 2 ? 'rank-top' : '' ?>"
                                        >

                                            <?= $index + 1 ?>

                                        </span>

                                    </td>


                                    <!-- TEAM -->

                                    <td>

                                        <div class="flex items-center gap-3">

                                            <div class="team-code">

                                                <?= e($team['code']) ?>

                                            </div>

                                            <div>

                                                <div class="font-bold text-navy">

                                                    <?= e($team['name']) ?>

                                                </div>


                                                <?php if ($index < 2): ?>

                                                    <div class="text-[8px] uppercase tracking-wider text-amber-700 font-bold">

                                                        Zona Grand Final

                                                    </div>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- MAIN -->

                                    <td>

                                        <?= $team['played'] ?>

                                    </td>


                                    <!-- MENANG -->

                                    <td>

                                        <span class="font-bold text-emerald-600">

                                            <?= $team['won'] ?>

                                        </span>

                                    </td>


                                    <!-- KALAH -->

                                    <td>

                                        <span class="font-bold text-red-500">

                                            <?= $team['lost'] ?>

                                        </span>

                                    </td>


                                    <!-- PF -->

                                    <td>

                                        <span class="font-semibold text-navy">

                                            <?= $team['pf'] ?>

                                        </span>

                                    </td>


                                    <!-- PA -->

                                    <td>

                                        <span class="font-semibold text-slate-600">

                                            <?= $team['pa'] ?>

                                        </span>

                                    </td>


                                    <!-- DIFF -->

                                    <td>

                                        <?php if ($team['diff'] > 0): ?>

                                            <span class="font-bold text-emerald-600">

                                                +<?= $team['diff'] ?>

                                            </span>

                                        <?php elseif ($team['diff'] < 0): ?>

                                            <span class="font-bold text-red-500">

                                                <?= $team['diff'] ?>

                                            </span>

                                        <?php else: ?>

                                            <span class="font-bold text-slate-500">

                                                0

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- POIN KLASemen -->

                                    <td>

                                        <span class="heading font-extrabold text-navy">

                                            <?= $team['points'] ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="py-10 text-center"
                                >

                                    Belum ada data klasemen.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- KETERANGAN KLASEMEN -->

        <div class="flex flex-wrap gap-x-5 gap-y-2 mt-4 text-[10px] text-gray-500">

            <span>
                <strong class="text-navy">P</strong> = Main
            </span>

            <span>
                <strong class="text-navy">W</strong> = Menang
            </span>

            <span>
                <strong class="text-navy">L</strong> = Kalah
            </span>

            <span>
                <strong class="text-navy">PF</strong> = Poin Masuk
            </span>

            <span>
                <strong class="text-navy">PA</strong> = Poin Kemasukan
            </span>

            <span>
                <strong class="text-navy">DIFF</strong> = PF − PA
            </span>

            <span>
                <strong class="text-navy">Poin</strong> = W × 2 + L × 1
            </span>

        </div>

    </section>


    <!-- =====================================================
    JADWAL
    ===================================================== -->

    <section
        id="jadwal"
        class="mb-6"
    >

        <div class="mb-5">

            <div class="flex items-center gap-3 mb-2">

                <div class="section-line"></div>

                <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">

                    Schedule

                </span>

            </div>


            <h2 class="section-title text-2xl md:text-3xl">

                Jadwal Pertandingan

            </h2>


            <p class="section-sub mt-1">

                Jadwal lengkap pertandingan Basketball Production Cup 2026.

            </p>

        </div>


        <div class="grid md:grid-cols-2 gap-4">

            <?php foreach ($matches as $m): ?>

                <div class="schedule-card">


                    <!-- TOP -->

                    <div class="flex justify-between items-start gap-3">

                        <div>

                            <div class="flex items-center gap-2">

                                <i class="fa-regular fa-calendar text-gray-400 text-[10px]"></i>

                                <span class="font-bold text-navy text-[11px]">

                                    <?= e($m['hari']) ?>

                                </span>

                            </div>


                            <div class="text-[10px] text-gray-500 mt-1">

                                <?= e($m['jam']) ?>

                                •

                                <?= e($m['fase']) ?>

                            </div>

                        </div>


                        <?php if ($m['status'] === 'Selesai'): ?>

                            <span class="status status-done">

                                SELESAI

                            </span>

                        <?php elseif ($m['status'] === 'Berlangsung'): ?>

                            <span class="status status-live">

                                LIVE

                            </span>

                        <?php else: ?>

                            <span class="status status-upcoming">

                                UPCOMING

                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- TEAMS -->

                    <div class="mt-5 flex items-center justify-between gap-3">


                        <!-- TEAM A -->

                        <div class="flex items-center gap-3 min-w-0">

                            <div class="team-code shrink-0">

                                <?= e($m['team_a']) ?>

                            </div>

                            <div class="schedule-team truncate">

                                <?= e($m['team_a']) ?>

                            </div>

                        </div>


                        <!-- SCORE -->

                        <div class="schedule-score shrink-0">

                            <?php if ($m['status'] === 'Selesai'): ?>

                                <?= intval($m['score_a']) ?>

                                <span class="text-gray-300 mx-1">
                                    :
                                </span>

                                <?= intval($m['score_b']) ?>

                            <?php else: ?>

                                <span class="text-gray-400 text-[10px]">

                                    VS

                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- TEAM B -->

                        <div class="flex items-center gap-3 min-w-0 justify-end">

                            <div class="schedule-team truncate text-right">

                                <?= e($m['team_b']) ?>

                            </div>

                            <div class="team-code shrink-0">

                                <?= e($m['team_b']) ?>

                            </div>

                        </div>

                    </div>


                    <!-- MATCH CODE -->

                    <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between">

                        <span class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">

                            Match Code

                        </span>

                        <span class="text-[9px] font-bold text-gray-500">

                            <?= e($m['match_code']) ?>

                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</main>


<!-- =========================================================
FOOTER
========================================================= -->

<footer>

    <div class="max-w-7xl mx-auto px-5 md:px-6 py-10">

        <div class="grid md:grid-cols-2 gap-8 items-center">


            <!-- FOOTER BRAND -->

            <div>

                <div class="flex items-center gap-3">

                    <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-gold">

                        <i class="fa-solid fa-basketball"></i>

                    </div>

                    <div>

                        <div class="heading font-extrabold text-sm">

                            Production Cup Basketball

                        </div>

                        <div class="text-[9px] uppercase tracking-widest footer-muted mt-1">

                            Official Tournament Portal

                        </div>

                    </div>

                </div>


                <p class="footer-muted text-[11px] leading-relaxed mt-4 max-w-md">

                    Portal informasi resmi Production Cup Basketball 2026 untuk memantau jadwal,
                    hasil pertandingan, dan klasemen turnamen.

                </p>

            </div>


            <!-- FOOTER INFO -->

            <div class="md:text-right">

                <div class="footer-muted text-[11px]">

                    © <?= date('Y') ?> Production Cup Basketball

                </div>

                <div class="footer-muted text-[9px] mt-2 uppercase tracking-widest">

                    Official Tournament Information

                </div>

            </div>

        </div>

    </div>

</footer>


<!-- =========================================================
MOBILE NAVBAR SCRIPT
========================================================= -->

<script>

    const mobileMenuButton =
        document.getElementById('mobileMenuButton');

    const mobileMenu =
        document.getElementById('mobileMenu');

    const mobileMenuIcon =
        document.getElementById('mobileMenuIcon');


    function openMobileMenu() {

        mobileMenu.classList.add('active');

        mobileMenuButton.setAttribute(
            'aria-expanded',
            'true'
        );

        mobileMenuButton.setAttribute(
            'aria-label',
            'Tutup menu navigasi'
        );

        mobileMenuIcon.classList.remove(
            'fa-bars'
        );

        mobileMenuIcon.classList.add(
            'fa-xmark'
        );

    }


    function closeMobileMenu() {

        mobileMenu.classList.remove('active');

        mobileMenuButton.setAttribute(
            'aria-expanded',
            'false'
        );

        mobileMenuButton.setAttribute(
            'aria-label',
            'Buka menu navigasi'
        );

        mobileMenuIcon.classList.remove(
            'fa-xmark'
        );

        mobileMenuIcon.classList.add(
            'fa-bars'
        );

    }


    mobileMenuButton.addEventListener(
        'click',
        function () {

            if (
                mobileMenu.classList.contains('active')
            ) {

                closeMobileMenu();

            } else {

                openMobileMenu();

            }

        }
    );


    // Tutup menu jika layar kembali ke desktop

    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth > 768) {

                closeMobileMenu();

            }

        }
    );


    // Tutup menu ketika klik area di luar navbar

    document.addEventListener(
        'click',
        function (event) {

            const navbar =
                document.querySelector('.navbar');

            if (
                mobileMenu.classList.contains('active') &&
                !navbar.contains(event.target)
            ) {

                closeMobileMenu();

            }

        }
    );

</script>


</body>

</html>