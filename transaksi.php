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

// Fetch all transactions
$stmt = $conn->query("SELECT t.id, t.customer_name, t.table_number, t.transaction_date, t.duration, t.total_amount, t.payment_method 
                      FROM transactions t 
                      ORDER BY t.transaction_date DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transaksi - Admin Billiard</title>
    <link rel="stylesheet" href="transaksi.css">
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
            <h2>Transaksi</h2>
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
            <?php if (!empty($_SESSION['success'])): ?>
                <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <a href="inputdata.php">
                <button class="btn-export">Input Data</button>
            </a>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>No Meja</th>
                        <th>Tanggal</th>
                        <th>Durasi</th>
                        <th>Total Bayar</th>
                        <th>Metode</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['table_number']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($transaction['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['duration']); ?></td>
                                <td>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Tidak ada data transaksi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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