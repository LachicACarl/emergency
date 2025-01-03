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

// Handle Edit Resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_resource'])) {
    $id = $_POST['id'];
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

// Fetch resources from the database
$sql = "SELECT * FROM resources";
$result = $conn->query($sql);

// Close connection at the end of the script
?>

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

    main {
        flex-grow: 1;
        padding: 20px;
        background-color: rgba(255, 255, 255, 0.9);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 0;
    }

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

    form label {
        display: block;
        margin-bottom: 0.5em;
        font-weight: bold;
        text-align: left;
    }

    form input[type="text"],
    form input[type="number"] {
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
</style>

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
        <h1>Resource Management</h1>

        <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <h2>Add New Resource</h2>
        <form method="POST" action="">
            <label for="resource">Resource:</label>
            <input type="text" id="resource" name="resource" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>

            <button type="submit" name="add_resource">Add Resource</button>
        </form>

        <div id="editResourceSection" style="display: none;">
            <h2>Edit Resource</h2>
            <form method="POST" action="">
                <input type="hidden" name="id" id="edit_id">
                <label for="edit_resource">Resource:</label>
                <input type="text" id="edit_resource" name="resource" required>

                <label for="edit_quantity">Quantity:</label>
                <input type="number" id="edit_quantity" name="quantity" required>

                <label for="edit_location">Location:</label>
                <input type="text" id="edit_location" name="location" required>

                <button type="submit" name="edit_resource">Edit Resource</button>
            </form>
        </div>

        <h2>Manage Resources</h2>
        <?php
        if ($result && $result->num_rows > 0) {
            echo '<table>';
            echo '<thead><tr><th>Resource</th><th>Quantity</th><th>Location</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['resource']) . '</td>';
                echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                echo '<td>' . htmlspecialchars($row['location']) . '</td>';
                echo '<td>';
                echo '<a href="#" onclick="editResource(' . $row['id'] . ', \'' . htmlspecialchars($row['resource']) . '\', ' . $row['quantity'] . ', \'' . htmlspecialchars($row['location']) . '\')">Edit</a> | ';
                echo '<a href="resource_management.php?delete=' . $row['id'] . '" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No resources found.</p>';
        }
        ?>
    </main>

    <script>
        function editResource(id, resource, quantity, location) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_resource').value = resource;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_location').value = location;
            document.getElementById('editResourceSection').style.display = 'block';
            document.getElementById('editResourceSection').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>

<?php
$conn->close();
?>
