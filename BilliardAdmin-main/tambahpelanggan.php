<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "billiard_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Hanya admin yang dapat mengakses halaman ini.";
    header("Location: login.php");
    exit();
}

// Handle form submission
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $phone_number = filter_var($_POST['nomorhp'], FILTER_SANITIZE_STRING);
    $visit_count = filter_var($_POST['riwayat'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);

    // Validate inputs
    if (empty($name) || empty($phone_number)) {
        $error = "Nama dan nomor HP wajib diisi.";
    } elseif (!preg_match("/^[0-9]{10,13}$/", $phone_number)) {
        $error = "Nomor HP tidak valid (harus 10-13 digit angka).";
    } elseif ($visit_count === false) {
        $error = "Riwayat kunjungan harus angka positif atau nol.";
    } else {
        // Check if phone number already exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        if ($stmt->rowCount() > 0) {
            $error = "Nomor HP sudah terdaftar.";
        } else {
            // Insert new customer
            $stmt = $conn->prepare("INSERT INTO customers (name, phone_number, visit_count) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone_number, $visit_count]);
            $_SESSION['success'] = "Pelanggan berhasil ditambahkan.";
            header("Location: datapelanggan.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pelanggan - Admin Billiard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .container {
            padding: 20px;
        }

        .form-card {
            max-width: 500px;
            margin: 80px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit {
            background-color: #28a745;
        }

        .btn-cancel {
            background-color: #6c757d;
            text-decoration: none;
            line-height: 32px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h2>Tambah Pelanggan</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>

                <label for="nomorhp">Nomor HP</label>
                <input type="number" id="nomorhp" name="nomorhp" required>

                <label for="riwayat">Riwayat Kunjungan</label>
                <input type="number" id="riwayat" name="riwayat" min="0" value="0">

                <div class="btn-group">
                    <a href="datapelanggan.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>