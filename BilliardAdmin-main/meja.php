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

// Handle delete action
if (isset($_GET['delete'])) {
    $bookingId = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($bookingId) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $_SESSION['success'] = "Pemesanan berhasil dihapus.";
        header("Location: meja.php");
        exit();
    }
}

// Fetch all bookings
$stmt = $conn->query("SELECT id, customer_name, table_number, start_time, duration, status FROM bookings ORDER BY start_time");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pemesanan Meja - Admin Billiard</title>
    <link rel="stylesheet" href="meja.css">
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
            <h2>Pemesanan Meja</h2>
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
            <a href="tambahpemesanan.php">
                <button class="btn-tambah">+ Tambah Pemesanan</button>
            </a>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No Meja</th>
                        <th>Waktu Mulai</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['table_number']); ?></td>
                                <td><?php echo htmlspecialchars($booking['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($booking['duration']); ?></td>
                                <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                <td>
                                    <a href="editpemesanan.php?id=<?php echo $booking['id']; ?>"><button>Edit</button></a>
                                    <a href="meja.php?delete=<?php echo $booking['id']; ?>" onclick="return confirm('Yakin ingin menghapus pemesanan ini?');"><button>Hapus</button></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Tidak ada data pemesanan.</td>
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