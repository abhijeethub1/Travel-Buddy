<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $email = $_POST['email'];

    // In register.php, add this before the SQL execution
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Error: Invalid email format'); window.history.back();</script>";
    exit();
}

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! Please login.'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Registration failed: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Travel Buddy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3e5f5;
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
            background: #6a1b9a;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #4a148c;
        }
        .form-box a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #6a1b9a;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
    </div>
</body>
</html>
