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

// Handle login form submission
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan kata sandi wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php"); 
            $success = "Login berhasil! Anda sekarang bisa mengakses <a href='dashboard.php'>Dashboard</a>.";
        } else {
            $error = "Email atau kata sandi salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Admin Billiard</title>
    <link rel="stylesheet" href="login.css" />
    <link rel="stylesheet" href="asset/LOGO 2.png">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="asset/LOGO bener.png" width="500" height="500" alt="logo">
            </div>
        </div>
        <div class="right-panel">
            <form class="login-form" method="POST" action="">
                <h2>Masuk ke Akun Anda</h2>
                <?php if (!empty($error)): ?>
                    <p style="color: red; margin-bottom: 20px;"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <p style="color: green; margin-bottom: 20px;"><?php echo $success; ?></p>
                <?php endif; ?>
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Kata Sandi" required />
                <button type="submit" class="submit-btn">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>