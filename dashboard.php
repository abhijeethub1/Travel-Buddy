<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM trips WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Buddy - Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: auto;
            padding: 30px 0;
        }

        .dashboard {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h1 {
            margin-top: 0;
            font-size: 32px;
            color: #00796B;
        }

        a {
            text-decoration: none;
            color: #00796B;
            float: right;
            font-weight: bold;
        }

        .trip-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .trip-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .trip-form button {
            background-color: #00796B;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .tab {
            padding: 10px 20px;
            background-color: #eee;
            border-radius: 8px;
            cursor: pointer;
        }

        .tab.active {
            background-color: #00796B;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .card {
            background: #fafafa;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .save-trip-btn {
            background-color: #388E3C;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #00796B;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        #totalCost {
            font-weight: bold;
            font-size: 18px;
            margin: 15px 0;
        }

        @media screen and (max-width: 600px) {
            .trip-form {
                flex-direction: column;
            }

            .tabs {
                flex-direction: column;
            }

            a {
                float: none;
                display: block;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <script src="static/script.js"></script>
    <div class="container">
        <div class="dashboard">
            <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
            <a href="index.php?logout=true">Logout</a>
            <h2>Plan Your Trip</h2>
            <form id="tripForm" class="trip-form">
                <input type="text" id="source" placeholder="Source (e.g., Delhi)" required>
                <input type="text" id="destination" placeholder="Destination (e.g., Mumbai)" required>
                <button type="button" onclick="fetchTripData()">Search</button>
            </form>

            <div class="tabs">
                <div class="tab active" onclick="showTab('weather')">Weather</div>
                <div class="tab" onclick="showTab('flights')">Flights</div>
                <div class="tab" onclick="showTab('trains')">Trains</div>
                <div class="tab" onclick="showTab('hotels')">Hotels</div>
                <div class="tab" onclick="showTab('attractions')">Attractions</div>
            </div>

            <div id="weather" class="tab-content active"></div>
            <div id="flights" class="tab-content"></div>
            <div id="trains" class="tab-content"></div>
            <div id="hotels" class="tab-content"></div>
            <div id="attractions" class="tab-content"></div>
            <div id="totalCost"></div>

            <button class="save-trip-btn" onclick="saveTrip()">Save Trip</button>

            <h2>Your Saved Trips</h2>
            <table>
                <tr>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Transport</th>
                    <th>Hotel</th>
                    <th>Cost</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
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

    <script>
        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));

            document.querySelector(`.tab[onclick="showTab('${tabId}')"]`).classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        
    </script>
</body>
</html>
