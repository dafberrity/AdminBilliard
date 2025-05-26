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

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "Data pengguna tidak ditemukan.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profil Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        .profile-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header i {
            font-size: 60px;
            color: #007bff;
            margin-bottom: 10px;
        }

        .profile-header h2 {
            margin: 0;
            font-size: 22px;
            color: #333;
        }

        .profile-info {
            margin-bottom: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-item label {
            font-weight: bold;
            display: block;
            color: #555;
            margin-bottom: 5px;
        }

        .info-item span {
            color: #333;
            display: block;
        }

        .profile-actions {
            display: flex;
            justify-content: space-between;
        }

        .profile-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .logout-btn:hover {
            background-color: #b52a pretended;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <i class="fas fa-user-circle"></i>
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        </div>
        <?php if (!empty($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <div class="profile-info">
            <div class="info-item">
                <label>Nama Lengkap</label>
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <div class="info-item">
                <label>Email</label>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="info-item">
                <label>Username</label>
                <span><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
        </div>
        <div class="profile-actions">
            <a href="editprofil.php">
                <button class="edit-btn">Edit Profil</button>
            </a>
            <a href="logout.php">
                <button class="logout-btn">Logout</button>
            </a>
        </div>
    </div>
</body>
</html>