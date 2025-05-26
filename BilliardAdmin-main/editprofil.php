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

// Handle form submission
$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['nama'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($username)) {
        $error = "Nama, email, dan username wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Kata sandi baru harus minimal 6 karakter.";
    } else {
        // Check if email or username is already taken (excluding current user)
        $stmt = $conn->prepare("SELECT id, email, username FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$email, $username, $_SESSION['user_id']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingUser) {
            if ($existingUser['email'] === $email) {
                $error = "Email sudah digunakan oleh pengguna lain.";
            } elseif ($existingUser['username'] === $username) {
                $error = "Username sudah digunakan oleh pengguna lain.";
            }
        } else {
            // Prepare update query
            $updateFields = ["name = ?", "email = ?", "username = ?"];
            $updateValues = [$name, $email, $username];

            // Handle password update if provided
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = "password = ?";
                $updateValues[] = $hashedPassword;
            }

            $updateFields = implode(", ", $updateFields);
            $updateValues[] = $_SESSION['user_id'];
            $stmt = $conn->prepare("UPDATE users SET $updateFields WHERE id = ?");
            $stmt->execute($updateValues);

            // Update session email if changed
            $_SESSION['email'] = $email;
            $success = "Profil berhasil diperbarui.";
            // Redirect to profil.php after success
            header("Location: profil.php");
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
    <title>Edit Profil Admin</title>
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

        .edit-container {
            max-width: 500px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .edit-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .edit-header h2 {
            font-size: 24px;
            color: #333;
        }

        form .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
        }

        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .save-btn {
            background-color: #28a745;
            color: white;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
        }

        .save-btn:hover {
            background-color: #218838;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h2>Edit Profil Admin</h2>
        </div>
        <?php if (!empty($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password" placeholder="Isi jika ingin mengganti password">
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">Simpan</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='profil.php'">Batal</button>
            </div>
        </form>
    </div>
</body>
</html>