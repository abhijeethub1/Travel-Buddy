<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $email = $_POST['email'];
        
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $email);
        
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.');</script>";
        } else {
            echo "<script>alert('Registration failed: " . $conn->error . "');</script>";
        }
    } elseif (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        
        $sql = "SELECT id FROM users WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid credentials.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Buddy - Your Perfect Travel Companion</title>
    <link rel="stylesheet" href="static/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<script src="static/script.js"></script>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="https://via.placeholder.com/40x40/FF5A5F/FFFFFF?text=TB" alt="Travel Buddy Logo">
                Travel Buddy
            </a>
            <div class="auth-buttons">
                <a href="login.php" id="login-link" class="auth-btn login-btn">Login</a>
                <a href="register.php" id="register-link" class="auth-btn register-btn">Register</a>
                <a href="admin.php" class="auth-btn admin-btn">Admin</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section with Search -->
    <section class="hero">
        <div class="search-container">
            <h1>Where do you want to go?</h1>
            <form class="search-form" id="tripForm">
                <input type="text" placeholder="From" id="source" required>
                <input type="text" placeholder="To" id="destination" required>
                <input type="date" placeholder="Departure">
                <input type="date" placeholder="Return">
                <select>
                    <option value="1">1 Traveler</option>
                    <option value="2">2 Travelers</option>
                    <option value="3">3 Travelers</option>
                    <option value="4">4 Travelers</option>
                    <option value="5">5+ Travelers</option>
                </select>
                <button type="button" onclick="fetchTripData()">Search</button>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <div class="tabs-container">
        <div class="tabs">
            <div class="tab active" data-tab="weather">Weather</div>
            <div class="tab" data-tab="flights">Flights</div>
            <div class="tab" data-tab="trains">Trains</div>
            <div class="tab" data-tab="hotels">Hotels</div>
            <div class="tab" data-tab="attractions">Attractions</div>
        </div>
    </div>

    <main class="container">
        <!-- Tab Contents -->
        <div id="weather" class="tab-content active">
            <!-- Weather content will be loaded here -->
        </div>
        <div id="flights" class="tab-content">
            <!-- Flights content will be loaded here -->
        </div>
        <div id="trains" class="tab-content">
            <!-- Trains content will be loaded here -->
        </div>
        <div id="hotels" class="tab-content">
            <!-- Hotels content will be loaded here -->
        </div>
        <div id="attractions" class="tab-content">
            <!-- Attractions content will be loaded here -->
        </div>

        <!-- Cost Summary -->
        <div id="totalCost" class="cost-summary"></div>

        <!-- Save Trip Button (visible when logged in) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="auth-btn register-btn" onclick="saveTrip()" style="width: 100%; padding: 15px; margin-bottom: 50px;">Save Trip</button>
        <?php endif; ?>
        
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3>Travel Buddy</h3>
                <p>Your perfect travel companion for all your journey needs.</p>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Feedback</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Connect With Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Travel Buddy. All rights reserved.
        </div>
    </footer>

    <!-- Add this JavaScript at the end of your index.php -->
    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Your existing fetchTripData() and other functions remain the same
    </script>
    

</body>
</html>