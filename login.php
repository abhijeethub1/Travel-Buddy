<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT id FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid credentials.');</script>";
    }

    // In login.php, add this after the credentials check
if ($result->num_rows == 0) {
    echo "<script>alert('Error: Invalid credentials'); window.history.back();</script>";
    exit();
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Travel Buddy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e0f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #ccc;
            width: 300px;
        }
        .form-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-box input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background: #00796b;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #004d40;
        }
        .form-box a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #00796b;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register</a>
    </div>
</body>
</html>
