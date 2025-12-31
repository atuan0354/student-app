<?php
ob_start();
session_start();

// Lấy DB config từ ENV (K8S Secret)
$servername   = getenv('DB_HOST') ?: '127.0.0.1';
$db_username  = getenv('DB_USER') ?: 'studentuser';
$db_password  = getenv('DB_PASS') ?: '';
$db_name      = getenv('DB_NAME') ?: 'studentdb';

$message = "";

try {
    // Kết nối DB
    $conn = new PDO(
        "mysql:host=$servername;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("DB connection failed: " . $e->getMessage());
}

// Xử lý login
if (!empty($_POST['username']) && !empty($_POST['password'])) {
    try {
        $stmt = $conn->prepare("CALL CheckLogin(:username, :password)");
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->bindParam(':password', $_POST['password']);
        $stmt->execute();
        $login_result = $stmt->fetch();

        if ($login_result && isset($login_result['result']) && $login_result['result'] === 'SUCCESS') {
            $_SESSION['username'] = $login_result['user_username'];
            $_SESSION['type']     = $login_result['user_type'];

            $type = $login_result['user_type'];

            if ($type === 'STUDENT') {
                header("Location: student.php");
                exit;
            } elseif ($type === 'EMPLOYEE') {
                header("Location: employee.php");
                exit;
            } elseif ($type === 'TEACHER') {
                header("Location: teacher.php");
                exit;
            } else {
                $message = "Unknown user type: " . htmlspecialchars($type);
            }
        } else {
            $msg = $login_result['msg'] ?? 'Login failed';
            $message = "Đăng nhập thất bại. " . htmlspecialchars($msg);
        }

    } catch (PDOException $e) {
        $message = "Login error: " . $e->getMessage();
    }
} else {
    // Không submit form
    // $message = "Please login.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>member</title>
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <!-- Nếu file này là trang xử lý login POST thì không cần form -->
    <!-- Nếu muốn có form ở đây luôn, bạn có thể bật đoạn form dưới -->

    <!--
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Đăng nhập</button>
    </form>
    -->
</body>
</html>
