<?php
session_start();

// Redirect if not logged in or not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "emergency_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle Add Resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $resource = $_POST['resource'];
    $quantity = $_POST['quantity'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("INSERT INTO resources (resource, quantity, location) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $resource, $quantity, $location);
    if ($stmt->execute()) {
        $message = "Resource added successfully!";
    } else {
        $error = "Failed to add resource: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete Resource
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Resource deleted successfully!";
    } else {
        $error = "Failed to delete resource: " . $conn->error;
    }
    $stmt->close();
}

// Fetch resources from the database
$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $sql = "SELECT * FROM resources WHERE resource LIKE '%$searchQuery%' OR location LIKE '%$searchQuery%'";
} else {
    $sql = "SELECT * FROM resources";
}
$result = $conn->query($sql);

// Close connection at the end of the script
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Disaster Relief</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY"></script>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
            display: flex;
            margin: 0;
            height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: #333;
            color: #fff;
            height: 100vh;
            padding: 20px;
        }

        .sidebar h2 {
            color: #fff;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #fff;
            padding: 10px 0;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        /* Main Content Styling */
        main {
            flex-grow: 1;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 0;
        }

        /* Heading Styling */
        main h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        section {
            margin-top: 1.5rem;
        }

        section h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #007bff;
        }

        /* Form Styling */
        form label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
            text-align: left;
        }

        form input[type="text"],
        form input[type="password"],
        form input[type="number"],
        form input[type="search"] {
            width: 100%;
            padding: 0.8em;
            margin-bottom: 1em;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form button {
            padding: 0.8em 1.2em;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table thead th {
            background-color: #007bff;
            color: #fff;
            padding: 0.8rem;
            text-align: left;
        }

        table tbody td {
            padding: 0.8rem;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Map Styling */
        .map-container {
            width: 100%;
            height: 400px;
            margin-top: 20px;
        }

        /* Footer */
        footer {
            margin-top: 2em;
            font-size: 14px;
            color: #666;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }

            main {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="admin_dashboard.php">Home</a>
        <a href="reports.php">Reports</a>
        <a href="resource_management.php">Resource Management</a>
        <a href="#">Analytics</a>
        <a href="login.php">Logout</a>
    </div>

        <section>
            <h2>Resource Map</h2>
            <div id="map" class="map-container"></div>
        </section>
    </main>

    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 10.3157, lng: 123.8854 }, // Default location (Cebu, Philippines)
                zoom: 8,
            });

            const resources = <?php
                $resourcesArray = [];
                if ($result && $result->num_rows > 0) {
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) {
                        $resourcesArray[] = [
                            "resource" => $row["resource"],
                            "location" => $row["location"],
                        ];
                    }
                }
                echo json_encode($resourcesArray);
            ?>;

            resources.forEach((resource) => {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: resource.location }, (results, status) => {
                    if (status === "OK") {
                        new google.maps.Marker({
                            position: results[0].geometry.location,
                            map: map,
                            title: resource.resource,
                        });
                    }
                });
            });
        }

        window.onload = initMap;
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
