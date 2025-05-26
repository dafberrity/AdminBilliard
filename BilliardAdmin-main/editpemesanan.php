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

// Check if booking ID is provided
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error'] = "ID pemesanan tidak valid.";
    header("Location: meja.php");
    exit();
}

$bookingId = $_GET['id'];

// Fetch booking data
$stmt = $conn->prepare("SELECT customer_name, table_number, start_time, duration, status FROM bookings WHERE id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    $_SESSION['error'] = "Pemesanan tidak ditemukan.";
    header("Location: meja.php");
    exit();
}

// Available tables and durations
$tables = ["Meja 1", "Meja 2", "Meja 3", "Meja 4", "Meja 5"];
$durations = ["1 jam", "2 jam", "3 jam"];
$statuses = ["Menunggu", "Aktif", "Selesai"];

// Handle form submission
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $table_number = $_POST['noMeja'];
    $start_time = $_POST['waktuMulai'];
    $duration = $_POST['durasi'];
    $status = $_POST['status'];

    // Validate inputs
    if (empty($customer_name) || empty($table_number) || empty($start_time) || empty($duration) || empty($status)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!in_array($table_number, $tables)) {
        $error = "Nomor meja tidak valid.";
    } elseif (!in_array($duration, $durations)) {
        $error = "Durasi tidak valid.";
    } elseif (!in_array($status, $statuses)) {
        $error = "Status tidak valid.";
    } elseif (!preg_match("/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/", $start_time)) {
        $error = "Format waktu mulai tidak valid.";
    } else {
        // Convert duration to hours for conflict check
        $duration_hours = (int) str_replace(" jam", "", $duration);
        $start_datetime = new DateTime("2025-05-24 $start_time:00");
        $end_datetime = clone $start_datetime;
        $end_datetime->modify("+$duration_hours hours");

        // Check for table availability (exclude current booking)
        $stmt = $conn->prepare("SELECT id FROM bookings WHERE table_number = ? AND status != 'Selesai' AND id != ? AND (
            (start_time < ? AND DATE_ADD(start_time, INTERVAL duration HOUR) > ?) OR
            (start_time >= ? AND start_time < ?)
        )");
        $stmt->execute([
            $table_number,
            $bookingId,
            $end_datetime->format('H:i:s'),
            $start_datetime->format('H:i:s'),
            $start_datetime->format('H:i:s'),
            $end_datetime->format('H:i:s')
        ]);
        if ($stmt->rowCount() > 0) {
            $error = "Meja sudah dipesan pada waktu tersebut.";
        } else {
            // Update booking data
            $stmt = $conn->prepare("UPDATE bookings SET customer_name = ?, table_number = ?, start_time = ?, duration = ?, status = ? WHERE id = ?");
            $stmt->execute([$customer_name, $table_number, $start_time, $duration, $status, $bookingId]);
            $_SESSION['success'] = "Pemesanan berhasil diperbarui.";
            header("Location: meja.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Edit Pemesanan</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 500px;
            margin: 80px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-batal {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-simpan {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Pemesanan</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($booking['customer_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="noMeja">Nomor Meja</label>
                <select id="noMeja" name="noMeja" required>
                    <option value="">-- Pilih Meja --</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?php echo htmlspecialchars($table); ?>" <?php echo $table === $booking['table_number'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($table); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="waktuMulai">Waktu Mulai</label>
                <input type="time" id="waktuMulai" name="waktuMulai" value="<?php echo htmlspecialchars($booking['start_time']); ?>" required>
            </div>
            <div class="form-group">
                <label for="durasi">Durasi</label>
                <select id="durasi" name="durasi" required>
                    <?php foreach ($durations as $duration): ?>
                        <option value="<?php echo htmlspecialchars($duration); ?>" <?php echo $duration === $booking['duration'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($duration); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status === $booking['status'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <a href="meja.php" class="btn btn-batal">Batal</a>
                <button type="submit" class="btn btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>