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
    $_SESSION['error'] = "Hanya admin yang dapat mengakses dashboard.";
    header("Location: login.php");
    exit();
}

// Fetch total customers
$stmt = $conn->query("SELECT COUNT(*) as total FROM customers");
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch total bookings for today (2025-05-24)
$current_date = '2025-05-24'; // Hardcoded based on system date
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE booking_date = ?");
$stmt->execute([$current_date]);
$total_bookings_today = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch total transactions amount
$stmt = $conn->query("SELECT SUM(total_amount) as total FROM transactions");
$total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Fetch latest bookings (up to 3)
$stmt = $conn->query("SELECT customer_name, table_number, start_time, status 
                      FROM bookings 
                      ORDER BY created_at DESC 
                      LIMIT 3");
$latest_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Admin Billiard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="sidebar">
        <img src="asset/LOGO dashboard.png" width="180" height="100">
        <a href="dashboard.php">Dashboard</a>
        <a href="datapelanggan.php">Data Pelanggan</a>
        <a href="meja.php">Pemesanan Meja</a>
        <a href="transaksi.php">Transaksi</a>
    </div>
    <div class="main">
        <div class="topbar">
            <h2>Dashboard</h2>
            <div class="header-right">
                <div class="profile-dropdown">
                    <i class="fa-regular fa-circle-user profile-icon" id="profileIcon"></i>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="profil.php">Profil</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            </div>
        </div>
        <div class="content">
            <div class="grid-container" style="text-align: center;">
                <div class="card">
                    <h3>Total Pelanggan</h3>
                    <p><?php echo $total_customers; ?></p>
                </div>
                <div class="card">
                    <h3>Total Booking Hari Ini</h3>
                    <p><?php echo $total_bookings_today; ?></p>
                </div>
                <div class="card">
                    <h3>Total Transaksi</h3>
                    <p>Rp <?php echo number_format($total_transactions, 0, ',', '.'); ?></p>
                </div>
            </div>
            <div class="notif-section">
                <h3>Booking Terbaru</h3>
                <div class="table-wrapper">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>No Meja</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($latest_bookings) > 0): ?>
                                <?php foreach ($latest_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['table_number']); ?></td>
                                        <td><?php echo date('H:i', strtotime($booking['start_time'])) . ' WIB'; ?></td>
                                        <td>
                                            <span class="status <?php echo strtolower($booking['status']); ?>">
                                                <?php echo htmlspecialchars($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">Tidak ada booking terbaru.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        const profileIcon = document.getElementById("profileIcon");
        const dropdownMenu = document.getElementById("dropdownMenu");

        profileIcon.addEventListener("click", () => {
            dropdownMenu.style.display = dropdownMenu.style.display === "block" ? "none" : "block";
        });

        window.addEventListener("click", (e) => {
            if (!e.target.closest(".profile-dropdown")) {
                dropdownMenu.style.display = "none";
            }
        });
    </script>
</body>
</html>