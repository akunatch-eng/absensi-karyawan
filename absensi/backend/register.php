<?php
session_start();
require "db.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Pembuatan Akun</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* =======================
           Global Style
        ======================= */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 30px 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            color: #1aa34a;
        }

        /* Form */
        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #41524a;
        }
        form input {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            transition: 0.3s;
        }
        form input:focus {
            outline: none;
            border-color: #1aa34a;
            box-shadow: 0 0 0 2px rgba(26,163,74,0.15);
        }

        /* Button */
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg,#1aa34a,#14903d);
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn:hover {
            opacity: 0.85;
        }

        /* Info / message */
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #e5533d;
            font-weight: 500;
        }

        /* Link login */
        .login-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }
        .login-link a {
            color: #1aa34a;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h3>Ajukan Pembuatan Akun</h3>

        <?php if(isset($_GET['msg'])): ?>
            <p class="message"><?= htmlspecialchars($_GET['msg']) ?></p>
        <?php endif; ?>

        <form action="request_akun.php" method="post">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_karyawan" required placeholder="Masukkan nama lengkap">

            <label>NIP</label>
            <input type="text" name="nip" required placeholder="Masukkan NIP">

            <label>Email</label>
            <input type="email" name="email" required placeholder="Masukkan email">

            <button class="btn" type="submit" id="submitBtn">
             <span id="btnText">Ajukan Pembuatan Akun</span>
             <span id="btnLoading" style="display:none;">Sedang mengirim...</span>
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="../index.php">Login di sini</a>
        </div>
    </div>
</div>

<script>
const form = document.querySelector('form');
const btnText = document.getElementById('btnText');
const btnLoading = document.getElementById('btnLoading');

form.addEventListener('submit', () => {
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline';
});
</script>

</body>
</html>
