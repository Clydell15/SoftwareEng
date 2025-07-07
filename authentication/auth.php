<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

$error = "";

if (isset($_SESSION['error_auth'])) {
    $error = $_SESSION['error_auth'];
    unset($_SESSION['error_auth']);
}

$success = "";
if (isset($_SESSION['success_auth'])) {
    $success = $_SESSION['success_auth'];
    unset($_SESSION['success_auth']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($action == "login") {
        $emailLogin = trim($email);
        $stmt = $conn->prepare("SELECT id, password, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $emailLogin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (isset($user['is_verified']) && !$user['is_verified']) {
                $_SESSION['error_auth'] = "Please verify your email before logging in.";
                header("Location: auth.php?form=login");
                exit();
            }
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
        $emailTrimmed = trim($email);
        $passwordTrimmed = trim($password);

        // Check for empty or only spaces
        if ($emailTrimmed === "" || $passwordTrimmed === "") {
            $_SESSION['error_auth'] = "Email and password cannot be empty or only spaces";
            header("Location: auth.php?form=signup");
            exit();
        }
        // Check for leading/trailing spaces
        if ($email !== $emailTrimmed) {
            $_SESSION['error_auth'] = "Email cannot start or end with spaces";
            header("Location: auth.php?form=signup");
            exit();
        }
        if ($password !== $passwordTrimmed) {
            $_SESSION['error_auth'] = "Password cannot start or end with spaces";
            header("Location: auth.php?form=signup");
            exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_auth'] = "Invalid email format";
            header("Location: auth.php?form=signup");
            exit();
        }
        if (strlen($password) < 6) {
            $_SESSION['error_auth'] = "Password must be at least 6 characters long";
            header("Location: auth.php?form=signup");
            exit();
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $_SESSION['error_auth'] = "An account with this email already exists";
            header("Location: auth.php?form=signup");
            exit();
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $verification_code = strtoupper(bin2hex(random_bytes(3)));
            $stmt = $conn->prepare("INSERT INTO users (email, password, verification_code) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $hashed_password, $verification_code);

            if ($stmt->execute()) {
                $_SESSION['pending_email'] = $email;
                unset($_SESSION['verification_email_sent']);
                header("Location: verify.php?email=" . urlencode($email));
                exit();
            } else {
                $_SESSION['error_auth'] = "Error creating account";
                header("Location: auth.php?form=signup");
                exit();
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
                
                <button type="submit" id="register-btn" class="btn btn-secondary w-100">
                    <span id="register-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span id="register-btn-text">Sign In</span>
                </button>
                <p class="mt-3 text-center">
                    Don't have an account? <a href="#" onclick="toggleForm('signup'); formJustSwitched = true" class='white-text'>Sign up</a>
                </p>
                <p class="mt-3 text-center">
                    <a href="forgot.php" class="white-text" style="text-decoration: underline;">Forgot password?</a>
                </p>

                <?php if (!empty($success)) echo "<p id='success-message' class='alert alert-success text-center' id='success-message'>$success</p>"; ?>
                <?php if (!empty($error)) echo "<p id='error-message' class='alert alert-danger text-center' id='error-message'>$error</p>"; ?>
            </form>
        </div>


    </div>

<script src="../assets/javascript/auth.js"></script>
</body>
</html>