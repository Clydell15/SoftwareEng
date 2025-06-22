<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config db.php';

$error = "";

if (isset($_SESSION['error_auth'])) {
    $error = $_SESSION['error_auth'];
    unset($_SESSION['error_auth']); // clear it after use
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($action == "login") {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: ../taskflow/dashboard.php");
                exit();
            } else {
                $_SESSION['error_auth'] = "Invalid password";
                header("Location: auth.php?form=login");
                exit();
            }
        } else {
            $_SESSION['error_auth'] = "User not found";
            header("Location: auth.php?form=login");
            exit();
        }
    } elseif ($action == "signup") {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_auth'] = "Invalid email format";
            header("Location: auth.php?form=login");
            exit();
        } elseif (strlen($password) < 6) {
            $_SESSION['error_auth'] = "Password must be at least 6 characters long";
            header("Location: auth.php?form=login");
            exit();
        } else {
            // Check if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $checkResult = $check->get_result();
        
            if ($checkResult->num_rows > 0) {
                $_SESSION['error_auth'] = "An account with this email already exists";
                header("Location: auth.php?form=login");
                exit();
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $email, $hashed_password);
        
                if ($stmt->execute()) {
                    header("Location: auth.php?success=registered");
                    exit();
                } else {
                    $_SESSION['error_auth'] = "Error creating account";
                    header("Location: auth.php?form=login");
                    exit();
                }
            }
        }
        
    }
} elseif (isset($_SESSION['user_id'])) {
    header("Location: ../taskflow/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auth | TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css"><style>
        body { background-color:rgb(206, 221, 237); }
        .auth-container { max-width: 400px; margin: auto; padding-top: 100px; }
        .card { padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="auth-page">
    <?php include '../taskflow/component functions/navbar.php'; ?>

    <div class="auth-container">
        <div class="card">
            <h2 class="text-center" id="form-title">Login</h2>
            <form method="POST" action="auth.php">
                <input type="hidden" name="action" id="form-action" value="login">

                <div class="mb-3">
                    <input type="email" name="email" class="form-control rounded" placeholder="Email" required>
                </div>
                
                <div class="mb-3">
                    <input type="password" name="password" class="form-control rounded" placeholder="Password" required>
                </div>
                
                <button type="submit" class="btn btn-secondary w-100 ">Sign In</button>

                <p class="mt-3 text-center">
                    Don't have an account? <a href="#" onclick="toggleForm('signup'); formJustSwitched = true" class='white-text'>Sign up</a>
                </p>

                <?php if (!empty($error)) echo "<p id='error-message' class='text-warning text-center'>$error</p>"; ?>

            </form>
        </div>


    </div>

<script src="../assets/javascript/auth.js"></script>
</body>
</html>