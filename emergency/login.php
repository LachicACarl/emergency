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

$errorMessage = "";

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
        exit;
    } else {
        $errorMessage = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Modal styling */
        #error-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 1.5em;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        #error-modal h3 {
            margin-bottom: 1em;
            color: #d9534f;
        }

        #error-modal button {
            padding: 0.5em 1em;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #error-modal button:hover {
            background-color: #0056b3;
        }

        /* Overlay styling */
        #modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <main>
        <h2>Login</h2>
        <form action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </main>

    <!-- Modal pop-up for error -->
    <?php if (!empty($errorMessage)) : ?>
        <div id="modal-overlay"></div>
        <div id="error-modal">
            <h3>Error</h3>
            <p><?= htmlspecialchars($errorMessage); ?></p>
            <button onclick="closeModal()">Close</button>
        </div>
        <script>
            // Display modal and overlay
            document.getElementById('modal-overlay').style.display = 'block';
            document.getElementById('error-modal').style.display = 'block';

            // Function to close the modal
            function closeModal() {
                document.getElementById('modal-overlay').style.display = 'none';
                document.getElementById('error-modal').style.display = 'none';
            }
        </script>
    <?php endif; ?>
</body>
</html>
