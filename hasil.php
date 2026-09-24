<?php
require_once 'koneksi.php';

// AMBIL SEMUA PERTANDINGAN YANG SUDAH SELESAI ATAU BERLANGSUNG
$result = $conn->query("
    SELECT *
    FROM matches
    WHERE status IN ('Selesai', 'Berlangsung')
    ORDER BY id ASC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hasil Pertandingan - Production Cup Basket 2026</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        rel="stylesheet"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .nav-shadow {
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
        }

        .card-shadow {
            box-shadow:
                0 10px 25px rgba(15, 23, 42, 0.05),
                0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .card-shadow:hover {
            box-shadow:
                0 18px 35px rgba(15, 23, 42, 0.08),
                0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .team-box {
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
        }

        .score-box {
            background: linear-gradient(145deg, #0b1f3a, #122c4f);
        }
    </style>
</head>

<body class="text-slate-800 min-h-screen flex flex-col">

    <!-- =========================================================
         NAVBAR
    ========================================================== -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 nav-shadow">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="h-20 flex items-center justify-between">

                <!-- BRAND -->
                <div class="flex items-center gap-3">

                    <div
                        class="w-11 h-11 rounded-xl flex items-center justify-center text-white shadow-sm"
                        style="background:#0B1F3A;"
                    >
                        <i class="fa-solid fa-basketball text-lg"></i>
                    </div>

                    <div>
                        <a
                            href="index.php"
                            class="block text-lg sm:text-xl font-black tracking-tight"
                            style="color:#0B1F3A;"
                        >
                            Production Cup
                        </a>

                        <span
                            class="text-[10px] sm:text-xs font-semibold tracking-[0.18em] uppercase text-slate-500"
                        >
                            Basketball Tournament 2026
                        </span>
                    </div>

                </div>

                <!-- DESKTOP MENU -->
                <div class="hidden md:flex items-center gap-1">

                    <a
                        href="index.php#beranda"
                        class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:text-[#0B1F3A] hover:bg-slate-50 transition"
                    >
                        Beranda
                    </a>

                    <a
                        href="index.php#jadwal"
                        class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:text-[#0B1F3A] hover:bg-slate-50 transition"
                    >
                        Jadwal
                    </a>

                    <a
                        href="hasil.php"
                        class="px-4 py-2.5 rounded-lg text-sm font-bold bg-[#0B1F3A]/5 text-[#0B1F3A] border border-[#0B1F3A]/10"
                    >
                        Hasil
                    </a>

                    <a
                        href="index.php#klasemen"
                        class="px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-600 hover:text-[#0B1F3A] hover:bg-slate-50 transition"
                    >
                        Klasemen
                    </a>

                    <a
                        href="login.php"
                        class="ml-3 px-5 py-2.5 rounded-lg text-sm font-bold text-white flex items-center gap-2 shadow-sm hover:shadow-md transition"
                        style="background:#0B1F3A;"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Admin
                    </a>

                </div>

            </div>

        </div>

    </nav>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->
    <main class="flex-grow">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">

            <!-- PAGE HEADER -->
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-10">

                <div>

                    <div class="flex items-center gap-2 mb-3">

                        <span
                            class="w-8 h-1 rounded-full"
                            style="background:#C89B3C;"
                        ></span>

                        <span
                            class="text-xs font-bold uppercase tracking-[0.2em]"
                            style="color:#C89B3C;"
                        >
                            Match Results
                        </span>

                    </div>

                    <h1
                        class="text-3xl sm:text-4xl font-black tracking-tight"
                        style="color:#0B1F3A;"
                    >
                        Hasil Pertandingan
                    </h1>

                    <p class="text-sm sm:text-base text-slate-500 mt-2 max-w-2xl">
                        Rekap skor pertandingan yang sedang berlangsung dan
                        pertandingan yang telah selesai.
                    </p>

                </div>


                <!-- BACK BUTTON -->
                <a
                    href="index.php"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-600 hover:text-[#0B1F3A] hover:border-slate-300 transition shadow-sm"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>

            </div>


            <!-- =================================================
                 MATCH RESULTS
            ================================================== -->

            <?php if ($result && $result->num_rows > 0): ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <?php while ($m = $result->fetch_assoc()): ?>

                        <article
                            class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 card-shadow transition duration-300"
                        >

                            <!-- MATCH INFO -->
                            <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100">

                                <div>

                                    <span
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide"
                                        style="background:#C89B3C14; color:#9A741F; border:1px solid #C89B3C33;"
                                    >
                                        <?= htmlspecialchars($m['fase']) ?>
                                    </span>

                                    <div class="flex items-center gap-2 mt-3 text-xs text-slate-500">

                                        <i class="fa-regular fa-calendar"></i>

                                        <span>
                                            <?= htmlspecialchars($m['hari']) ?>
                                        </span>

                                    </div>

                                </div>


                                <!-- STATUS -->
                                <div>

                                    <?php if ($m['status'] === 'Selesai'): ?>

                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold"
                                            style="background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;"
                                        >
                                            <i class="fa-solid fa-check text-[9px]"></i>
                                            Selesai
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold"
                                            style="background:#fff1f2; color:#be123c; border:1px solid #fecdd3;"
                                        >
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-60"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                            </span>
                                            Berlangsung
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- =================================================
                                 SCORE AREA
                            ================================================== -->

                            <div class="flex items-center justify-between gap-3 py-7">

                                <!-- TEAM A -->
                                <div class="flex-1 text-center">

                                    <div
                                        class="team-box w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl border border-slate-200 flex items-center justify-center shadow-sm"
                                    >
                                        <span
                                            class="text-lg sm:text-xl font-black"
                                            style="color:#0B1F3A;"
                                        >
                                            <?= htmlspecialchars($m['team_a']) ?>
                                        </span>
                                    </div>

                                    <p
                                        class="mt-3 text-sm sm:text-base font-bold text-slate-800"
                                    >
                                        <?= htmlspecialchars($m['team_a']) ?>
                                    </p>

                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">
                                        Team A
                                    </span>

                                </div>


                                <!-- SCORE -->
                                <div class="w-28 sm:w-36 text-center">

                                    <div
                                        class="score-box rounded-2xl px-3 py-4 sm:px-4 sm:py-5 shadow-md"
                                    >

                                        <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-slate-300 mb-2">
                                            Score
                                        </div>

                                        <div class="text-2xl sm:text-3xl font-black tracking-wider text-white">

                                            <?= (int)$m['score_a'] ?>

                                            <span
                                                class="mx-1"
                                                style="color:#C89B3C;"
                                            >
                                                -
                                            </span>

                                            <?= (int)$m['score_b'] ?>

                                        </div>

                                    </div>

                                </div>


                                <!-- TEAM B -->
                                <div class="flex-1 text-center">

                                    <div
                                        class="team-box w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl border border-slate-200 flex items-center justify-center shadow-sm"
                                    >
                                        <span
                                            class="text-lg sm:text-xl font-black"
                                            style="color:#0B1F3A;"
                                        >
                                            <?= htmlspecialchars($m['team_b']) ?>
                                        </span>
                                    </div>

                                    <p
                                        class="mt-3 text-sm sm:text-base font-bold text-slate-800"
                                    >
                                        <?= htmlspecialchars($m['team_b']) ?>
                                    </p>

                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">
                                        Team B
                                    </span>

                                </div>

                            </div>


                            <!-- MATCH CODE / FOOTER -->
                            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">

                                <div class="flex items-center gap-2 text-xs text-slate-400">

                                    <i class="fa-regular fa-clock"></i>

                                    <span>
                                        <?= htmlspecialchars($m['jam']) ?>
                                    </span>

                                </div>


                                <div class="text-xs font-bold text-slate-400">

                                    <?= htmlspecialchars($m['match_code']) ?>

                                </div>

                            </div>

                        </article>

                    <?php endwhile; ?>

                </div>


            <?php else: ?>

                <!-- EMPTY STATE -->

                <div
                    class="bg-white border border-slate-200 rounded-2xl p-12 sm:p-16 text-center card-shadow"
                >

                    <div
                        class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-5"
                        style="background:#0B1F3A0D;"
                    >
                        <i
                            class="fa-solid fa-basketball text-2xl"
                            style="color:#C89B3C;"
                        ></i>
                    </div>

                    <h2
                        class="text-lg font-bold"
                        style="color:#0B1F3A;"
                    >
                        Belum Ada Hasil Pertandingan
                    </h2>

                    <p class="text-sm text-slate-500 max-w-md mx-auto mt-2">
                        Hasil skor pertandingan akan ditampilkan di halaman ini
                        setelah wasit memasukkan data pertandingan ke dalam sistem.
                    </p>

                    <a
                        href="index.php"
                        class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-sm hover:shadow-md transition"
                        style="background:#0B1F3A;"
                    >
                        <i class="fa-solid fa-house"></i>
                        Kembali ke Beranda
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </main>


    <!-- =========================================================
         FOOTER
    ========================================================== -->
    <footer
        class="bg-[#0B1F3A] text-white mt-10"
    >

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="py-8 flex flex-col sm:flex-row items-center justify-between gap-4">

                <div class="text-center sm:text-left">

                    <div class="font-bold text-sm">
                        Production Cup Basketball Tournament
                    </div>

                    <div class="text-xs text-slate-400 mt-1">
                        Official Tournament Information System
                    </div>

                </div>

                <div class="text-xs text-slate-400 text-center sm:text-right">
                    &copy; 2026 Production Cup. All rights reserved.
                </div>

            </div>

        </div>

    </footer>

</body>
</html>