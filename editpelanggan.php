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

// Check if customer ID is provided
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error'] = "ID pelanggan tidak valid.";
    header("Location: datapelanggan.php");
    exit();
}

$customerId = $_GET['id'];

// Fetch customer data
$stmt = $conn->prepare("SELECT name, phone_number, visit_count FROM customers WHERE id = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    $_SESSION['error'] = "Pelanggan tidak ditemukan.";
    header("Location: datapelanggan.php");
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
        // Update customer data
        $stmt = $conn->prepare("UPDATE customers SET name = ?, phone_number = ?, visit_count = ? WHERE id = ?");
        $stmt->execute([$name, $phone_number, $visit_count, $customerId]);
        $_SESSION['success'] = "Data pelanggan berhasil diperbarui.";
        header("Location: datapelanggan.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pelanggan - Admin Billiard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }

        .container {
            margin-left: 220px;
            padding: 20px;
        }

        .form-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 50px auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
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
            <h2>Edit Pelanggan</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($customer['name']); ?>" required>

                <label for="nomorhp">Nomor HP</label>
                <input type="text" id="nomorhp" name="nomorhp" value="<?php echo htmlspecialchars($customer['phone_number']); ?>" required>

                <label for="riwayat">Riwayat Kunjungan</label>
                <input type="number" id="riwayat" name="riwayat" min="0" value="<?php echo htmlspecialchars($customer['visit_count']); ?>">

                <div class="btn-group">
                    <a href="datapelanggan.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>