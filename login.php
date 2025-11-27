<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (hash('sha256', $password) === $user['password']) {
            $_SESSION['user'] = $user;
            $_SESSION['message'] = 'Login successful!';
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><a href="register.php">Register</a></p>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>

    <script>
        <?php if (isset($_SESSION['message'])): ?>
            alert("<?= $_SESSION['message']; ?>");
            <?php unset($_SESSION['message']);
            ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            alert("<?= $error; ?>");
        <?php endif; ?>
    </script>
</body>

</html>