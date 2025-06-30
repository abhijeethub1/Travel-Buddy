<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    // In admin.php, modify the login check

    
    $sql = "SELECT id FROM admin WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin_id'] = $result->fetch_assoc()['id'];
        header("Location: admin.php");
    } else {
        echo "<script>alert('Invalid admin credentials.');</script>";
    }
}

if (!isset($_SESSION['admin_id'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Travel Buddy - Admin Login</title>
        <link rel="stylesheet" href="static/style.css">
        <style>
            body {
                background-color: #f0f2f5;
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }

            .login-container {
                width: 100%;
                max-width: 400px;
                background: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .login-box h1 {
                text-align: center;
                margin-bottom: 20px;
                color: #333;
            }

            .login-box form input {
                width: 100%;
                padding: 12px 15px;
                margin: 10px 0;
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            .login-box form button {
                width: 100%;
                padding: 12px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }

            .login-box form button:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <h1>Admin Login</h1>
                <form method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$sql = "SELECT u.username, t.source, t.destination, t.transport_type, t.hotel_name, t.total_cost 
        FROM users u LEFT JOIN trips t ON u.id = t.user_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Buddy - Admin Dashboard</title>
    <link rel="stylesheet" href="static/style.css">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 40px;
        }

        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-container h1 {
            margin-bottom: 10px;
            color: #333;
        }

        .admin-container a {
            text-decoration: none;
            color: #ffffff;
            background-color: #f44336;
            padding: 10px 15px;
            border-radius: 5px;
            float: right;
            margin-top: -40px;
        }

        .admin-container h2 {
            margin-top: 30px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f0f0f0;
            color: #333;
        }

        table tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-container">
            <h1>Admin Dashboard</h1>
            <a href="index.php?logout=true">Logout</a>
            <h2>User Trips</h2>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Transport</th>
                    <th>Hotel</th>
                    <th>Cost</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['source']; ?></td>
                    <td><?php echo $row['destination']; ?></td>
                    <td><?php echo $row['transport_type']; ?></td>
                    <td><?php echo $row['hotel_name']; ?></td>
                    <td><?php echo $row['total_cost']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
