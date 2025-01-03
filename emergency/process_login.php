<?php
// Database credentials
$servername = "localhost";
$username = "root"; // or your database username
$password = ""; // or your database password
$dbname = "emergency_db"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $inputUsername, $inputPassword);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $role);
        $stmt->fetch();
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        header("Location: admin_dashboard.php"); // Redirect to admin dashboard
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>
