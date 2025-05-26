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

// Fetch customers for dropdown
$stmt = $conn->query("SELECT name FROM customers ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Available tables and payment methods
$tables = ["Meja 1", "Meja 2", "Meja 3", "Meja 4", "Meja 5"];
$payment_methods = ["Cash", "QRIS", "Transfer"];

// Handle form submission
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $table_number = $_POST['meja'];
    $transaction_date = $_POST['tanggal'];
    $duration = filter_var($_POST['durasi'], FILTER_SANITIZE_STRING);
    $total_amount = filter_var($_POST['bayar'], FILTER_SANITIZE_STRING);
    $payment_method = $_POST['metode'];

    // Validate inputs
    if (empty($customer_name) || empty($table_number) || empty($transaction_date) || empty($duration) || empty($total_amount) || empty($payment_method)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!in_array($table_number, $tables)) {
        $error = "Nomor meja tidak valid.";
    } elseif (!in_array($payment_method, $payment_methods)) {
        $error = "Metode pembayaran tidak valid.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $transaction_date)) {
        $error = "Format tanggal tidak valid.";
    } elseif (!preg_match("/^\d+\s*jam$/", $duration)) {
        $error = "Durasi harus dalam format 'X jam' (contoh: 2 jam).";
    } elseif (!preg_match("/^\d+$/", $total_amount)) {
        $error = "Total bayar harus berupa angka tanpa titik atau koma.";
    } else {
        // Insert new transaction
        $stmt = $conn->prepare("INSERT INTO transactions (customer_name, table_number, transaction_date, duration, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_name, $table_number, $transaction_date, $duration, $total_amount, $payment_method]);
        $_SESSION['success'] = "Transaksi berhasil ditambahkan.";
        header("Location: transaksi.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi - Admin Billiard</title>
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
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 80px auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .btn-group {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            color: white;
        }

        .btn-cancel {
            background-color: #6c757d;
            text-decoration: none;
            display: inline-block;
        }

        .btn-submit {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h2>Input Transaksi</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="nama">Nama Pelanggan</label>
                <select id="nama" name="nama" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['name']); ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="meja">No Meja</label>
                <select id="meja" name="meja" required>
                    <option value="">-- Pilih Meja --</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?php echo htmlspecialchars($table); ?>"><?php echo htmlspecialchars($table); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" required>

                <label for="durasi">Durasi</label>
                <input type="text" id="durasi" name="durasi" placeholder="contoh: 2 jam" required>

                <label for="bayar">Total Bayar</label>
                <input type="text" id="bayar" name="bayar" placeholder="contoh: 60000" required>

                <label for="metode">Metode Pembayaran</label>
                <select id="metode" name="metode" required>
                    <option value="">-- Pilih Metode --</option>
                    <?php foreach ($payment_methods as $method): ?>
                        <option value="<?php echo htmlspecialchars($method); ?>"><?php echo htmlspecialchars($method); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="btn-group">
                    <a href="transaksi.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>