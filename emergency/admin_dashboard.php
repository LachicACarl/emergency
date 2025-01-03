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

// Handle Update Resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resource'])) {
    $id = intval($_POST['id']);
    $resource = $_POST['resource'];
    $quantity = $_POST['quantity'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("UPDATE resources SET resource = ?, quantity = ?, location = ? WHERE id = ?");
    $stmt->bind_param("sisi", $resource, $quantity, $location, $id);
    if ($stmt->execute()) {
        $message = "Resource updated successfully!";
    } else {
        $error = "Failed to update resource: " . $conn->error;
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

// Handle Add Car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_car'])) {
    $car_model = $_POST['car_model'];
    $license_plate = $_POST['license_plate'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO cars (car_model, license_plate, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $car_model, $license_plate, $status);
    if ($stmt->execute()) {
        $message = "Car added successfully!";
    } else {
        $error = "Failed to add car: " . $conn->error;
    }
    $stmt->close();
}

// Handle Update Car
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_car'])) {
    $id = intval($_POST['id']);
    $car_model = $_POST['car_model'];
    $license_plate = $_POST['license_plate'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE cars SET car_model = ?, license_plate = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $car_model, $license_plate, $status, $id);
    if ($stmt->execute()) {
        $message = "Car updated successfully!";
    } else {
        $error = "Failed to update car: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete Car
if (isset($_GET['delete_car'])) {
    $id = intval($_GET['delete_car']);
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Car deleted successfully!";
    } else {
        $error = "Failed to delete car: " . $conn->error;
    }
    $stmt->close();
}

// Fetch resources with pagination
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$searchQuery = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $sql = "SELECT * FROM resources WHERE resource LIKE '%$searchQuery%' OR location LIKE '%$searchQuery%' LIMIT $limit OFFSET $offset";
    $totalSQL = "SELECT COUNT(*) as total FROM resources WHERE resource LIKE '%$searchQuery%' OR location LIKE '%$searchQuery%'";
} else {
    $sql = "SELECT * FROM resources LIMIT $limit OFFSET $offset";
    $totalSQL = "SELECT COUNT(*) as total FROM resources";
}

$result = $conn->query($sql);
$totalResult = $conn->query($totalSQL)->fetch_assoc();
$totalPages = ceil($totalResult['total'] / $limit);

// Fetch cars with pagination
$carLimit = 10;
$carPage = isset($_GET['car_page']) ? intval($_GET['car_page']) : 1;
$carOffset = ($carPage - 1) * $carLimit;

$carSearchQuery = "";
if (isset($_GET['car_search']) && !empty($_GET['car_search'])) {
    $carSearchQuery = $_GET['car_search'];
    $carSql = "SELECT * FROM cars WHERE car_model LIKE '%$carSearchQuery%' OR license_plate LIKE '%$carSearchQuery%' LIMIT $carLimit OFFSET $carOffset";
    $carTotalSQL = "SELECT COUNT(*) as total FROM cars WHERE car_model LIKE '%$carSearchQuery%' OR license_plate LIKE '%$carSearchQuery%'";
} else {
    $carSql = "SELECT * FROM cars LIMIT $carLimit OFFSET $carOffset";
    $carTotalSQL = "SELECT COUNT(*) as total FROM cars";
}

$carResult = $conn->query($carSql);
$carTotalResult = $conn->query($carTotalSQL)->fetch_assoc();
$carTotalPages = ceil($carTotalResult['total'] / $carLimit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Disaster Relief</title>
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

    <main>
        <section>
            <h2>Manage Resources</h2>
            <!-- Resource Management Forms and Table as in the previous example -->

            <h2>Manage Cars</h2>
            <!-- Add Car Form -->
            <form method="POST" action="">
                <label for="car_model">Car Model:</label>
                <input type="text" id="car_model" name="car_model" required>

                <label for="license_plate">License Plate:</label>
                <input type="text" id="license_plate" name="license_plate" required>

                <label for="status">Status:</label>
                <input type="text" id="status" name="status" required>

                <button type="submit" name="add_car">Add Car</button>
            </form>

            <!-- Update Car Form -->
            <form method="POST" action="">
                <input type="hidden" name="id" id="car_id">
                <label for="car_model">Car Model:</label>
                <input type="text" id="car_model" name="car_model" required>

                <label for="license_plate">License Plate:</label>
                <input type="text" id="license_plate" name="license_plate" required>

                <label for="status">Status:</label>
                <input type="text" id="status" name="status" required>

                <button type="submit" name="update_car">Update Car</button>
            </form>

            <!-- Delete Car -->
            <form method="GET" action="">
                <input type="hidden" name="delete_car" id="car_delete_id">
                <button type="submit" id="car_delete_button">Delete Car</button>
            </form>

            <!-- Car Search and Table -->
            <form method="GET" action="">
                <input type="text" name="car_search" placeholder="Search cars..." value="<?php echo htmlspecialchars($carSearchQuery); ?>">
                <button type="submit">Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Car Model</th>
                        <th>License Plate</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($carResult && $carResult->num_rows > 0): ?>
                        <?php while ($carRow = $carResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($carRow['car_model']); ?></td>
                                <td><?php echo htmlspecialchars($carRow['license_plate']); ?></td>
                                <td><?php echo htmlspecialchars($carRow['status']); ?></td>
                                <td>
                                    <button type="button" onclick="populateCarUpdateForm(<?php echo $carRow['id']; ?>)">Edit</button>
                                    <button type="button" onclick="populateCarDeleteForm(<?php echo $carRow['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No cars found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Car Pagination -->
            <div class="pagination">
                <?php if ($carPage > 1): ?>
                    <a href="?car_page=<?php echo $carPage - 1; ?>&car_search=<?php echo htmlspecialchars($carSearchQuery); ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $carPage; ?> of <?php echo $carTotalPages; ?></span>
                <?php if ($carPage < $carTotalPages): ?>
                    <a href="?car_page=<?php echo $carPage + 1; ?>&car_search=<?php echo htmlspecialchars($carSearchQuery); ?>">Next</a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        function populateCarUpdateForm(id) {
            document.getElementById('car_id').value = id;
            // Fetch car data via AJAX and populate form fields
        }

        function populateCarDeleteForm(id) {
            document.getElementById('car_delete_id').value = id;
            document.getElementById('car_delete_button').click();
        }
    </script>

</body>
</html>
