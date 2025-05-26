<?php
session_start();

// Destroy session and redirect to login
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Logout</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .logout-modal {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .logout-modal h2 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }

        .logout-modal p {
            font-size: 15px;
            color: #666;
            margin-bottom: 25px;
        }

        .btn-group {
            display: flex;
            justify-content: space-around;
        }

        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-logout {
            background-color: #dc3545;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="logout-modal">
        <h2>Konfirmasi Logout</h2>
        <p>Apakah Anda yakin ingin keluar dari akun?</p>
        <div class="btn-group">
            <button class="btn-cancel" onclick="window.history.back()">Batal</button>
            <button class="btn-logout" onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>
</body>
</html>