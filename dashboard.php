<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$msg = '';
$error = '';

$allowed_status = ['Belum Dimulai', 'Berlangsung', 'Selesai'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_match'])) {

    $id = intval($_POST['match_id'] ?? 0);
    $score_a = max(0, intval($_POST['score_a'] ?? 0));
    $score_b = max(0, intval($_POST['score_b'] ?? 0));
    $status = $_POST['status'] ?? 'Belum Dimulai';

    if (!in_array($status, $allowed_status, true)) {
        $status = 'Belum Dimulai';
    }

    if ($id > 0) {
        $stmt = $conn->prepare("
            UPDATE matches 
            SET score_a = ?, score_b = ?, status = ?
            WHERE id = ?
        ");

        $stmt->bind_param("iisi", $score_a, $score_b, $status, $id);

        if ($stmt->execute()) {
            $msg = "Skor dan status pertandingan berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui data pertandingan.";
        }

        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM matches ORDER BY id ASC");

$matches = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
}

$total_match = count($matches);
$total_live = 0;
$total_finished = 0;
$total_upcoming = 0;

foreach ($matches as $match) {
    if ($match['status'] === 'Berlangsung') {
        $total_live++;
    } elseif ($match['status'] === 'Selesai') {
        $total_finished++;
    } else {
        $total_upcoming++;
    }
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function statusClass($status)
{
    return match ($status) {
        'Berlangsung' => 'bg-red-50 text-red-700 border-red-200',
        'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200'
    };
}

function statusDot($status)
{
    return match ($status) {
        'Berlangsung' => 'bg-red-500',
        'Selesai' => 'bg-emerald-500',
        default => 'bg-slate-400'
    };
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

    <title>Dashboard Admin - Production Cup Basketball 2026</title>
    <link rel="icon" type="image/png" href="assets/logo.png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        rel="stylesheet"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .score-input {
            -moz-appearance: textfield;
        }

        .score-input::-webkit-outer-spin-button,
        .score-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .score-btn {
            transition: all .15s ease;
        }

        .score-btn:active {
            transform: scale(.94);
        }

        .match-card {
            transition: all .2s ease;
        }

        .match-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }

    </style>

</head>

<body class="text-slate-800 min-h-screen">

<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="bg-white border-b border-slate-200 sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="h-16 flex items-center justify-between">

            <!-- BRAND -->

            <a
                href="index.php"
                class="flex items-center gap-3"
            >

                <div
                    class="w-10 h-10 rounded-xl bg-[#0B1F3A] text-white flex items-center justify-center shadow-sm"
                >
                    <i class="fa-solid fa-basketball text-lg"></i>
                </div>

                <div class="leading-tight">

                    <div class="font-extrabold text-[#0B1F3A] tracking-tight">
                        Production Cup
                    </div>

                    <div class="text-[10px] font-bold text-[#C89B3C] uppercase tracking-[.18em]">
                        Admin Panel
                    </div>

                </div>

            </a>


            <!-- RIGHT -->

            <div class="flex items-center gap-3">

                <div class="hidden sm:flex items-center gap-2">

                    <span
                        class="w-2 h-2 rounded-full bg-emerald-500"
                    ></span>

                    <span class="text-xs font-semibold text-slate-500">
                        Admin On-Duty
                    </span>

                </div>

                <a
                    href="index.php"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-[#0B1F3A] transition"
                >
                    <i class="fa-solid fa-globe"></i>
                    Halaman Publik
                </a>

            </div>

        </div>

    </div>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


    <!-- HEADER -->

    <div class="mb-8">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

            <div>

                <div class="flex items-center gap-2 mb-2">

                    <span
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0B1F3A]/5 text-[#0B1F3A] border border-[#0B1F3A]/10 text-[10px] font-extrabold uppercase tracking-wider"
                    >

                        <i class="fa-solid fa-shield-halved"></i>

                        Control Center

                    </span>

                </div>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0B1F3A] tracking-tight">
                    Input Skor & Status
                </h1>

                <p class="mt-2 text-sm text-slate-500 max-w-2xl">
                    Kelola skor pertandingan dan status pertandingan secara real-time dari panel admin.
                </p>

            </div>

            <div class="text-left lg:text-right">

                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                    Login sebagai
                </div>

                <div class="text-sm font-extrabold text-[#0B1F3A] mt-1">
                    <?= e($_SESSION['user'] ?? 'Admin') ?>
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         ALERT
    ====================================================== -->

    <?php if (!empty($msg)): ?>

        <div
            class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700"
        >

            <i class="fa-solid fa-circle-check mt-0.5"></i>

            <div class="text-sm font-semibold">
                <?= e($msg) ?>
            </div>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div
            class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700"
        >

            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

            <div class="text-sm font-semibold">
                <?= e($error) ?>
            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <!-- TOTAL -->

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Match
                    </div>

                    <div class="text-3xl font-extrabold text-[#0B1F3A] mt-2">
                        <?= $total_match ?>
                    </div>

                </div>

                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

            </div>

        </div>


        <!-- LIVE -->

        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Live
                    </div>

                    <div class="text-3xl font-extrabold text-red-600 mt-2">
                        <?= $total_live ?>
                    </div>

                </div>

                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <i class="fa-solid fa-broadcast-tower"></i>
                </div>

            </div>

        </div>


        <!-- FINISHED -->

        <div class="bg-white border border-emerald-100 rounded-2xl p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Selesai
                    </div>

                    <div class="text-3xl font-extrabold text-emerald-600 mt-2">
                        <?= $total_finished ?>
                    </div>

                </div>

                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

            </div>

        </div>


        <!-- UPCOMING -->

        <div class="bg-white border border-amber-100 rounded-2xl p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Belum Dimulai
                    </div>

                    <div class="text-3xl font-extrabold text-[#C89B3C] mt-2">
                        <?= $total_upcoming ?>
                    </div>

                </div>

                <div class="w-11 h-11 rounded-xl bg-amber-50 text-[#C89B3C] flex items-center justify-center">
                    <i class="fa-solid fa-hourglass-start"></i>
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MATCH LIST
    ====================================================== -->

    <div class="space-y-6">

        <?php

        $groups = [
            'Berlangsung' => [],
            'Belum Dimulai' => [],
            'Selesai' => []
        ];

        foreach ($matches as $match) {
            $groups[$match['status']][] = $match;
        }

        ?>


        <!-- =================================================
             LIVE
        ================================================== -->

        <?php if (!empty($groups['Berlangsung'])): ?>

            <section>

                <div class="flex items-center gap-3 mb-4">

                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>

                    <h2 class="text-lg font-extrabold text-[#0B1F3A]">
                        Sedang Berlangsung
                    </h2>

                    <span class="text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-2.5 py-1 rounded-full">
                        LIVE
                    </span>

                </div>


                <div class="space-y-4">

                    <?php foreach ($groups['Berlangsung'] as $match): ?>

                        <?php
                        $isFinal = ($match['match_code'] === 'FINAL');
                        ?>

                        <div
                            class="match-card bg-white border-2 border-red-200 rounded-2xl overflow-hidden shadow-sm"
                        >

                            <?php if ($isFinal): ?>

                                <div class="bg-[#0B1F3A] text-white px-5 py-2 text-xs font-extrabold tracking-wider uppercase">
                                    <i class="fa-solid fa-trophy text-[#C89B3C] mr-2"></i>
                                    Grand Final
                                </div>

                            <?php endif; ?>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="update_match"
                                    value="1"
                                >

                                <input
                                    type="hidden"
                                    name="match_id"
                                    value="<?= e($match['id']) ?>"
                                >


                                <div class="p-5 sm:p-6">

                                    <!-- MATCH INFO -->

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">

                                        <div>

                                            <div class="flex flex-wrap items-center gap-2">

                                                <span class="text-xs font-extrabold text-[#0B1F3A]">
                                                    <?= e($match['match_code']) ?>
                                                </span>

                                                <span class="text-xs text-slate-300">
                                                    •
                                                </span>

                                                <span class="text-xs font-semibold text-slate-500">
                                                    <?= e($match['fase']) ?>
                                                </span>

                                            </div>

                                            <div class="text-xs text-slate-400 mt-1">
                                                <?= e($match['hari']) ?>
                                                <span class="mx-1">•</span>
                                                <?= e($match['jam']) ?>
                                            </div>

                                        </div>


                                        <span class="inline-flex items-center gap-2 self-start sm:self-auto px-3 py-1.5 rounded-full border <?= statusClass($match['status']) ?> text-xs font-bold">

                                            <span class="w-1.5 h-1.5 rounded-full <?= statusDot($match['status']) ?>"></span>

                                            <?= e($match['status']) ?>

                                        </span>

                                    </div>


                                    <!-- SCORE AREA -->

                                    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] items-center gap-5 md:gap-8">

                                        <!-- TEAM A -->

                                        <div class="text-center md:text-right">

                                            <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">
                                                Team A
                                            </div>

                                            <div class="text-xl font-extrabold text-[#0B1F3A] mb-4">
                                                <?= e($match['team_a']) ?>
                                            </div>


                                            <input
                                                type="number"
                                                name="score_a"
                                                value="<?= e($match['score_a']) ?>"
                                                min="0"
                                                class="score-input w-28 text-center text-4xl font-extrabold text-[#0B1F3A] bg-slate-50 border border-slate-200 rounded-xl py-3 focus:outline-none focus:ring-2 focus:ring-[#C89B3C]/30 focus:border-[#C89B3C]"
                                            >


                                            <div class="flex justify-center md:justify-end flex-wrap gap-2 mt-3">

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_a', -1)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-bold hover:bg-slate-200"
                                                >
                                                    −1
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_a', 1)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#0B1F3A] text-white font-bold hover:bg-[#102b50]"
                                                >
                                                    +1
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_a', 2)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#0B1F3A] text-white font-bold hover:bg-[#102b50]"
                                                >
                                                    +2
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_a', 3)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#C89B3C] text-white font-bold hover:bg-[#b88d32]"
                                                >
                                                    +3
                                                </button>

                                            </div>

                                        </div>


                                        <!-- VS -->

                                        <div class="text-center">

                                            <div class="text-xs font-extrabold text-slate-300 uppercase tracking-widest">
                                                VS
                                            </div>

                                        </div>


                                        <!-- TEAM B -->

                                        <div class="text-center md:text-left">

                                            <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">
                                                Team B
                                            </div>

                                            <div class="text-xl font-extrabold text-[#0B1F3A] mb-4">
                                                <?= e($match['team_b']) ?>
                                            </div>


                                            <input
                                                type="number"
                                                name="score_b"
                                                value="<?= e($match['score_b']) ?>"
                                                min="0"
                                                class="score-input w-28 text-center text-4xl font-extrabold text-[#0B1F3A] bg-slate-50 border border-slate-200 rounded-xl py-3 focus:outline-none focus:ring-2 focus:ring-[#C89B3C]/30 focus:border-[#C89B3C]"
                                            >


                                            <div class="flex justify-center md:justify-start flex-wrap gap-2 mt-3">

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_b', -1)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 font-bold hover:bg-slate-200"
                                                >
                                                    −1
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_b', 1)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#0B1F3A] text-white font-bold hover:bg-[#102b50]"
                                                >
                                                    +1
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_b', 2)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#0B1F3A] text-white font-bold hover:bg-[#102b50]"
                                                >
                                                    +2
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="changeScore(this, 'score_b', 3)"
                                                    class="score-btn w-10 h-9 rounded-lg bg-[#C89B3C] text-white font-bold hover:bg-[#b88d32]"
                                                >
                                                    +3
                                                </button>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- CONTROL -->

                                    <div class="mt-7 pt-5 border-t border-slate-100">

                                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3">

                                            <div>

                                                <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">
                                                    Status Pertandingan
                                                </label>

                                                <select
                                                    name="status"
                                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#C89B3C]/30 focus:border-[#C89B3C]"
                                                >

                                                    <?php foreach ($allowed_status as $status): ?>

                                                        <option
                                                            value="<?= e($status) ?>"
                                                            <?= $match['status'] === $status ? 'selected' : '' ?>
                                                        >
                                                            <?= e($status) ?>
                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </div>


                                            <button
                                                type="button"
                                                onclick="resetScore(this)"
                                                class="self-end px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 transition"
                                            >
                                                <i class="fa-solid fa-rotate-left mr-2"></i>
                                                Reset
                                            </button>


                                            <button
                                                type="submit"
                                                class="self-end px-6 py-3 rounded-xl bg-[#0B1F3A] text-white text-sm font-extrabold hover:bg-[#102b50] shadow-sm transition"
                                            >
                                                <i class="fa-solid fa-floppy-disk mr-2"></i>
                                                Simpan
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </form>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>


        <!-- =================================================
             UPCOMING
        ================================================== -->

        <?php if (!empty($groups['Belum Dimulai'])): ?>

            <section>

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-[#C89B3C] flex items-center justify-center">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                    <div>

                        <h2 class="text-lg font-extrabold text-[#0B1F3A]">
                            Pertandingan Mendatang
                        </h2>

                        <p class="text-xs text-slate-400">
                            Match yang belum dimulai
                        </p>

                    </div>

                </div>


                <div class="space-y-4">

                    <?php foreach ($groups['Belum Dimulai'] as $match): ?>

                        <?php
                        $isFinal = ($match['match_code'] === 'FINAL');
                        ?>

                        <div
                            class="match-card bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm"
                        >

                            <?php if ($isFinal): ?>

                                <div class="bg-[#0B1F3A] text-white px-5 py-2 text-xs font-extrabold tracking-wider uppercase">
                                    <i class="fa-solid fa-trophy text-[#C89B3C] mr-2"></i>
                                    Grand Final
                                </div>

                            <?php endif; ?>


                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="update_match"
                                    value="1"
                                >

                                <input
                                    type="hidden"
                                    name="match_id"
                                    value="<?= e($match['id']) ?>"
                                >


                                <div class="p-5 sm:p-6">

                                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">

                                        <!-- INFO -->

                                        <div class="lg:w-1/4">

                                            <div class="flex items-center gap-2 mb-2">

                                                <span class="text-xs font-extrabold text-[#0B1F3A]">
                                                    <?= e($match['match_code']) ?>
                                                </span>

                                                <span class="text-xs text-slate-300">
                                                    •
                                                </span>

                                                <span class="text-xs font-semibold text-slate-500">
                                                    <?= e($match['fase']) ?>
                                                </span>

                                            </div>

                                            <div class="text-xs text-slate-400 leading-5">

                                                <div>
                                                    <i class="fa-regular fa-calendar mr-1"></i>
                                                    <?= e($match['hari']) ?>
                                                </div>

                                                <div>
                                                    <i class="fa-regular fa-clock mr-1"></i>
                                                    <?= e($match['jam']) ?>
                                                </div>

                                            </div>

                                        </div>


                                        <!-- TEAMS -->

                                        <div class="flex-1">

                                            <div class="flex items-center justify-center gap-5">

                                                <div class="text-right flex-1">

                                                    <div class="font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['team_a']) ?>
                                                    </div>

                                                    <div class="text-xs text-slate-400 mt-1">
                                                        Team A
                                                    </div>

                                                </div>


                                                <div class="text-xs font-extrabold text-slate-300 tracking-widest">
                                                    VS
                                                </div>


                                                <div class="text-left flex-1">

                                                    <div class="font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['team_b']) ?>
                                                    </div>

                                                    <div class="text-xs text-slate-400 mt-1">
                                                        Team B
                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- SCORE -->

                                        <div class="flex items-center justify-center gap-3">

                                            <input
                                                type="number"
                                                name="score_a"
                                                value="<?= e($match['score_a']) ?>"
                                                min="0"
                                                class="score-input w-20 text-center text-xl font-extrabold text-[#0B1F3A] bg-slate-50 border border-slate-200 rounded-xl py-2.5 focus:outline-none focus:border-[#C89B3C]"
                                            >

                                            <span class="text-slate-300 font-bold">
                                                -
                                            </span>

                                            <input
                                                type="number"
                                                name="score_b"
                                                value="<?= e($match['score_b']) ?>"
                                                min="0"
                                                class="score-input w-20 text-center text-xl font-extrabold text-[#0B1F3A] bg-slate-50 border border-slate-200 rounded-xl py-2.5 focus:outline-none focus:border-[#C89B3C]"
                                            >

                                        </div>


                                        <!-- CONTROL -->

                                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2 lg:w-44">

                                            <select
                                                name="status"
                                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#C89B3C]"
                                            >

                                                <?php foreach ($allowed_status as $status): ?>

                                                    <option
                                                        value="<?= e($status) ?>"
                                                        <?= $match['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= e($status) ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>


                                            <button
                                                type="submit"
                                                class="w-full px-4 py-2.5 rounded-xl bg-[#0B1F3A] text-white text-xs font-extrabold hover:bg-[#102b50] transition"
                                            >
                                                <i class="fa-solid fa-floppy-disk mr-1"></i>
                                                Simpan
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </form>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>


        <!-- =================================================
             FINISHED
        ================================================== -->

        <?php if (!empty($groups['Selesai'])): ?>

            <section>

                <div class="flex items-center gap-3 mb-4">

                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <div>

                        <h2 class="text-lg font-extrabold text-[#0B1F3A]">
                            Pertandingan Selesai
                        </h2>

                        <p class="text-xs text-slate-400">
                            Hasil pertandingan yang telah diselesaikan
                        </p>

                    </div>

                </div>


                <div class="space-y-4">

                    <?php foreach ($groups['Selesai'] as $match): ?>

                        <div
                            class="match-card bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm"
                        >

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="update_match"
                                    value="1"
                                >

                                <input
                                    type="hidden"
                                    name="match_id"
                                    value="<?= e($match['id']) ?>"
                                >


                                <div class="p-5 sm:p-6">

                                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">

                                        <!-- INFO -->

                                        <div class="lg:w-1/4">

                                            <div class="flex items-center gap-2 mb-2">

                                                <span class="text-xs font-extrabold text-[#0B1F3A]">
                                                    <?= e($match['match_code']) ?>
                                                </span>

                                                <span class="text-xs text-slate-300">
                                                    •
                                                </span>

                                                <span class="text-xs font-semibold text-slate-500">
                                                    <?= e($match['fase']) ?>
                                                </span>

                                            </div>

                                            <div class="text-xs text-slate-400 leading-5">

                                                <div>
                                                    <i class="fa-regular fa-calendar mr-1"></i>
                                                    <?= e($match['hari']) ?>
                                                </div>

                                                <div>
                                                    <i class="fa-regular fa-clock mr-1"></i>
                                                    <?= e($match['jam']) ?>
                                                </div>

                                            </div>

                                        </div>


                                        <!-- SCORE -->

                                        <div class="flex-1">

                                            <div class="flex items-center justify-center gap-5">

                                                <div class="text-right flex-1">

                                                    <div class="font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['team_a']) ?>
                                                    </div>

                                                </div>


                                                <div class="flex items-center gap-3">

                                                    <span class="text-2xl font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['score_a']) ?>
                                                    </span>

                                                    <span class="text-slate-300 font-bold">
                                                        -
                                                    </span>

                                                    <span class="text-2xl font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['score_b']) ?>
                                                    </span>

                                                </div>


                                                <div class="text-left flex-1">

                                                    <div class="font-extrabold text-[#0B1F3A]">
                                                        <?= e($match['team_b']) ?>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                                        <!-- EDIT -->

                                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2 lg:w-44">

                                            <div class="flex gap-2">

                                                <input
                                                    type="number"
                                                    name="score_a"
                                                    value="<?= e($match['score_a']) ?>"
                                                    min="0"
                                                    class="score-input w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-center focus:outline-none focus:border-[#C89B3C]"
                                                >

                                                <input
                                                    type="number"
                                                    name="score_b"
                                                    value="<?= e($match['score_b']) ?>"
                                                    min="0"
                                                    class="score-input w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-center focus:outline-none focus:border-[#C89B3C]"
                                                >

                                            </div>


                                            <select
                                                name="status"
                                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#C89B3C]"
                                            >

                                                <?php foreach ($allowed_status as $status): ?>

                                                    <option
                                                        value="<?= e($status) ?>"
                                                        <?= $match['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= e($status) ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>


                                            <button
                                                type="submit"
                                                class="w-full px-4 py-2.5 rounded-xl bg-slate-100 text-[#0B1F3A] text-xs font-extrabold hover:bg-slate-200 transition"
                                            >
                                                <i class="fa-solid fa-pen-to-square mr-1"></i>
                                                Perbarui
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </form>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>

    </div>

</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="border-t border-slate-200 bg-white mt-10">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

            <div class="text-xs text-slate-400">
                © 2026 Production Cup Basketball.
            </div>

            <div class="text-xs font-semibold text-slate-500">
                Admin Control Center
            </div>

        </div>

    </div>

</footer>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

function changeScore(button, inputName, amount) {

    const form = button.closest('form');

    if (!form) return;

    const input = form.querySelector(
        `input[name="${inputName}"]`
    );

    if (!input) return;

    let current = parseInt(input.value, 10);

    if (isNaN(current)) {
        current = 0;
    }

    current += amount;

    if (current < 0) {
        current = 0;
    }

    input.value = current;
}


function resetScore(button) {

    const form = button.closest('form');

    if (!form) return;

    const scoreA = form.querySelector(
        'input[name="score_a"]'
    );

    const scoreB = form.querySelector(
        'input[name="score_b"]'
    );

    if (scoreA) {
        scoreA.value = 0;
    }

    if (scoreB) {
        scoreB.value = 0;
    }
}

</script>

</body>
</html>