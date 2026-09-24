<?php
session_start();

// =========================================================
// PROSES LOGIN
// Username : om
// Password : om123
// =========================================================

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'om' && $password === 'om123') {

        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = 'Admin';

        header('Location: dashboard.php');
        exit;

    } else {

        $error = 'Username atau Password salah!';
    }
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
        Masuk Sistem - Production Cup Basketball 2026
    </title>

    <link rel="icon" type="image/png" href="assets/logo.png">

    <!-- =====================================================
         TAILWIND CSS
    ====================================================== -->
    <script src="https://cdn.tailwindcss.com"></script>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         CUSTOM STYLE
    ====================================================== -->
    <style>

        * {
            box-sizing: border-box;
        }

        body {

            font-family: 'Inter', sans-serif;

            background:
                radial-gradient(
                    circle at 20% 20%,
                    rgba(200, 155, 60, 0.08),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 80% 80%,
                    rgba(11, 31, 58, 0.08),
                    transparent 30%
                ),

                #f8fafc;
        }


        /* =====================================================
           LOGIN CARD
        ====================================================== */

        .login-card {

            box-shadow:
                0 25px 60px rgba(15, 23, 42, 0.08),
                0 5px 20px rgba(15, 23, 42, 0.04);
        }


        /* =====================================================
           INPUT FOCUS
        ====================================================== */

        .input-focus:focus {

            border-color: #C89B3C;

            box-shadow:
                0 0 0 3px rgba(200, 155, 60, 0.10);
        }


        /* =====================================================
           LOGO UTAMA
        ====================================================== */

        .main-logo {

            width: 0px;
            height: 0px;

            display: flex;

            align-items: center;
            justify-content: center;

            margin-left: auto;
            margin-right: auto;
        }


        .main-logo img {

            width: 100%;
            height: 100%;

            object-fit: contain;

            display: block;
        }


        /* =====================================================
           LOGO LOGIN CARD
        ====================================================== */

        .login-logo {

            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            flex-shrink: 0;
        }


        .login-logo img {

            width: 100%;
            height: 100%;

            object-fit: contain;

            display: block;
        }


        /* =====================================================
           LOGIN BUTTON
        ====================================================== */

        .login-button {

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .login-button:hover {

            transform: translateY(-2px);

            background: #132D52 !important;

            box-shadow:
                0 12px 24px rgba(11, 31, 58, 0.20) !important;
        }


        .login-button:active {

            transform: translateY(0);
        }


        /* =====================================================
           BACK LINK
        ====================================================== */

        .back-link {

            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }


        .back-link:hover {

            color: #0B1F3A;

            transform: translateX(-2px);
        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 640px) {

            body {
                padding: 16px;
            }

            .main-logo {

                width: 72px;
                height: 72px;
            }

            .login-logo {

                width: 50px;
                height: 50px;
            }

        }

    </style>

</head>


<body class="min-h-screen flex items-center justify-center p-4">


    <!-- =====================================================
         BACKGROUND DECORATION
    ====================================================== -->

    <div class="fixed inset-0 pointer-events-none overflow-hidden">

        <div
            class="absolute -top-32 -left-32 w-80 h-80 rounded-full blur-3xl"
            style="background:rgba(200,155,60,0.07);"
        ></div>

        <div
            class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full blur-3xl"
            style="background:rgba(11,31,58,0.06);"
        ></div>

    </div>



    <!-- =====================================================
         LOGIN CONTAINER
    ====================================================== -->

    <div class="relative w-full max-w-md">


        <!-- =================================================
             BRAND
        ================================================== -->

        <div class="text-center mb-7">


            <!-- LOGO PRODUCTION CUP -->

            <div class="main-logo mb-4">

                <img
                    src="assets/logo.png"
                    alt="Logo Production Cup"
                >

            </div>


            <!-- BRAND NAME -->

            <h1
                class="text-2xl sm:text-3xl font-black tracking-tight"
                style="color:#0B1F3A;"
            >

                Production Cup

            </h1>


            <!-- SUBTITLE -->

            <div class="flex items-center justify-center gap-2 mt-2">

                <span
                    class="w-6 h-px"
                    style="background:#C89B3C;"
                ></span>


                <span
                    class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em]"
                    style="color:#9A741F;"
                >

                    Basketball Tournament 2026

                </span>


                <span
                    class="w-6 h-px"
                    style="background:#C89B3C;"
                ></span>

            </div>

        </div>



        <!-- =================================================
             LOGIN CARD
        ================================================== -->

        <div
            class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 login-card"
        >


            <!-- =================================================
                 CARD HEADER
            ================================================== -->

            <div class="mb-7">


                <div class="flex items-center gap-4 mb-2">


                    <!-- LOGO PRODUCTION CUP -->

                    <div class="login-logo">

                        <img
                            src="assets/logo.png"
                            alt="Logo Production Cup"
                        >

                    </div>


                    <!-- TITLE -->

                    <div>

                        <h2
                            class="text-lg font-black"
                            style="color:#0B1F3A;"
                        >

                            Masuk Sistem

                        </h2>


                        <p class="text-xs text-slate-500">

                            Panel Admin

                        </p>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 ERROR MESSAGE
            ================================================== -->

            <?php if (!empty($error)): ?>

                <div
                    class="flex items-start gap-3 p-4 rounded-xl mb-6"
                    style="
                        background:#fff1f2;
                        border:1px solid #fecdd3;
                        color:#be123c;
                    "
                >

                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>


                    <div>

                        <p class="text-xs font-bold">

                            Login gagal

                        </p>


                        <p class="text-xs mt-0.5 opacity-80">

                            <?= htmlspecialchars($error) ?>

                        </p>

                    </div>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 LOGIN FORM
            ================================================== -->

            <form
                action=""
                method="POST"
                class="space-y-5"
            >


                <!-- =================================================
                     USERNAME
                ================================================== -->

                <div>

                    <label
                        for="username"
                        class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2"
                    >

                        Username

                    </label>


                    <div class="relative">


                        <!-- ICON -->

                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"
                        >

                            <i class="fa-solid fa-user text-sm"></i>

                        </div>


                        <!-- INPUT -->

                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                            placeholder="Masukkan username"
                            class="input-focus w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 outline-none transition"
                        >

                    </div>

                </div>



                <!-- =================================================
                     PASSWORD
                ================================================== -->

                <div>

                    <label
                        for="password"
                        class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2"
                    >

                        Password

                    </label>


                    <div class="relative">


                        <!-- PASSWORD ICON -->

                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400"
                        >

                            <i class="fa-solid fa-lock text-sm"></i>

                        </div>


                        <!-- PASSWORD INPUT -->

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                            class="input-focus w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 outline-none transition"
                        >


                        <!-- SHOW PASSWORD -->

                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-slate-700 transition"
                            aria-label="Tampilkan password"
                        >

                            <i
                                id="passwordIcon"
                                class="fa-solid fa-eye text-sm"
                            ></i>

                        </button>

                    </div>

                </div>



                <!-- =================================================
                     LOGIN BUTTON
                ================================================== -->

                <button
                    type="submit"
                    class="login-button w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white flex items-center justify-center gap-2"
                    style="
                        background:#0B1F3A;
                        box-shadow:0 8px 18px rgba(11,31,58,0.15);
                    "
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Masuk ke Dashboard

                </button>


            </form>



            <!-- =================================================
                 SECURITY INFO
            ================================================== -->

            <div
                class="mt-6 p-3.5 rounded-xl flex items-start gap-3"
                style="
                    background:#fffbeb;
                    border:1px solid #fde68a;
                "
            >

                <i
                    class="fa-solid fa-circle-info mt-0.5 text-sm"
                    style="color:#B88620;"
                ></i>


                <p class="text-[11px] leading-relaxed text-slate-600">

                    Halaman ini digunakan khusus untuk
                    <strong>Admin</strong>
                    dalam mengelola data pertandingan.

                </p>

            </div>



            <!-- =================================================
                 BACK TO PUBLIC
            ================================================== -->

            <div
                class="mt-7 pt-6 border-t border-slate-100 text-center"
            >

                <a
                    href="index.php"
                    class="back-link inline-flex items-center gap-2 text-xs font-semibold text-slate-500"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Kembali ke Halaman Publik

                </a>

            </div>


        </div>



        <!-- =====================================================
             FOOTER
        ====================================================== -->

        <div class="text-center mt-6">

            <p class="text-[10px] text-slate-400">

                &copy; 2026 Production Cup Basketball Tournament

            </p>


            <p class="text-[10px] text-slate-400 mt-1">

                Official Tournament Information System

            </p>

        </div>


    </div>



    <!-- =========================================================
         SHOW / HIDE PASSWORD
    ========================================================== -->

    <script>

        const togglePassword =
            document.getElementById('togglePassword');

        const passwordInput =
            document.getElementById('password');

        const passwordIcon =
            document.getElementById('passwordIcon');


        togglePassword.addEventListener('click', function () {

            const isPassword =
                passwordInput.getAttribute('type') === 'password';


            passwordInput.setAttribute(
                'type',
                isPassword ? 'text' : 'password'
            );


            passwordIcon.classList.toggle('fa-eye');

            passwordIcon.classList.toggle('fa-eye-slash');

        });

    </script>


</body>

</html>