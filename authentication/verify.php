<?php
session_start();
include '../config db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

$email = $_GET['email'] ?? $_SESSION['pending_email'] ?? '';
$code = $_GET['code'] ?? '';
$message = "";
$success = false;

// Use a per-email session flag to prevent multiple sends
if (!isset($_SESSION['verification_email_sent'])) {
    $_SESSION['verification_email_sent'] = [];
}

if ($email && !isset($_SESSION['verification_email_sent'][$email]) && $_SERVER['REQUEST_METHOD'] !== 'POST' && !$code) {
    $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!empty($row['verification_code'])) {
        $verification_code = $row['verification_code'];
    } else {
        $verification_code = strtoupper(bin2hex(random_bytes(3)));
        $stmt = $conn->prepare("UPDATE users SET verification_code=?, is_verified=0 WHERE email=?");
        $stmt->bind_param("ss", $verification_code, $email);
        $stmt->execute();
    }

    // Send the email
    $verify_link = "https://softwareeng.onrender.com/authentication/verify.php?email=" . urlencode($email) . "&code=" . urlencode($verification_code);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'taskflowmanagerverify@gmail.com';
        $mail->Password   = 'lotveclnpeqmoyyn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('taskflowmanagerverify@gmail.com', 'TaskFlow');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your TaskFlow account';
        $mail->Body    = "Click <a href='$verify_link'>here</a> to verify your account.<br>Or copy and paste this link in your browser: $verify_link";

        $mail->send();
        $_SESSION['verification_email_sent'][$email] = true;
        $message = "Verification email sent! Please check your inbox.";
        $email_sent = true;
    } catch (Exception $e) {
        $message = "Could not send verification email. Please contact support.";
    }
}

// Handle manual code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $email = $_SESSION['pending_email'] ?? '';
    $code = strtoupper(trim($_POST['verify_code']));
    if ($email && $code) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND verification_code=? AND is_verified=0");
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $update = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE email=?");
            $update->bind_param("s", $email);
            $update->execute();
            $message = "Your account has been verified! You can now log in.";
            $success = true;
            unset($_SESSION['pending_email']);
            unset($_SESSION['verification_email_sent'][$email]);
            header("Location: auth.php?form=login");
        } else {
            $message = "Invalid or expired verification code.";
        }
    } else {
        $message = "Please enter your verification code.";
    }
}

// Handle resend email
if (isset($_POST['resend_email'])) {
    $email = $_SESSION['pending_email'] ?? '';
    if ($email) {
        $verification_code = strtoupper(bin2hex(random_bytes(3)));
        $stmt = $conn->prepare("UPDATE users SET verification_code=?, is_verified=0 WHERE email=?");
        $stmt->bind_param("ss", $verification_code, $email);
        $stmt->execute();

        $verify_link = "http://localhost/SoftwareEng/authentication/verify.php?email=" . urlencode($email) . "&code=" . urlencode($verification_code);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'taskflowmanagerverify@gmail.com';
            $mail->Password   = 'lotveclnpeqmoyyn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('taskflowmanagerverify@gmail.com', 'TaskFlow');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify your TaskFlow account';
            $mail->Body    = "Click <a href='$verify_link'>here</a> to verify your account.<br>Or copy and paste this link in your browser: $verify_link";

            $mail->send();
            $message = "Verification email resent! Please check your inbox.";
            $email_sent = true;
            $_SESSION['verification_email_sent'] = true;
        } catch (Exception $e) {
            $message = "Could not resend verification email. Please contact support.";
        }
    } else {
        $message = "No email found to resend verification.";
    }
}

// Handle direct link verification
if ($email && $code && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND verification_code=? AND is_verified=0");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE email=?");
        $update->bind_param("s", $email);
        $update->execute();
        $message = "Your account has been verified! You can now log in.";
        $success = true;
        unset($_SESSION['pending_email']);
        unset($_SESSION['verification_email_sent'][$email]);
    } elseif ($code) {
        $message = "Invalid or expired verification link.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify | TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color:rgb(206, 221, 237); }
        .auth-container { max-width: 400px; margin: auto; padding-top: 100px; }
        .card { padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .countdown { font-size: 1.1rem; color: #555; }
    </style>
</head>
<body class="auth-page">
    <?php include '../taskflow/component functions/navbar.php'; ?>

    <div class="auth-container">
        <div class="card text-center">
            <h2 class="mb-4">Email Verification</h2>
            <?php if ($message): ?>
                <div class="mb-3">
                    <p class="alert alert-<?php echo ($success || $email_sent) ? 'success' : 'danger'; ?> mb-2"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label for="verify_code" class="form-label">Enter Verification Code</label>
                        <input type="text" class="form-control text-center" id="verify_code" name="verify_code" maxlength="12" required placeholder="Enter code from email" style="text-transform:uppercase;">
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-2">Verify</button>
                </form>
                <form method="POST">
                    <button type="submit" name="resend_email" class="btn btn-link w-100">Resend Verification Email</button>
                </form>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="countdown mb-2 white-text">
                    Redirecting to login page in <span id="countdown">10</span> seconds...
                </div>
                <a href="auth.php?form=login" class="btn btn-secondary w-100 rounded">Go to Login Now</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success): ?>
    <script>
        let seconds = 10;
        const countdownElem = document.getElementById("countdown");
        const redirectUrl = "auth.php?form=login";
        setInterval(() => {
            seconds--;
            countdownElem.textContent = seconds;
            if (seconds <= 0) {
                window.location.href = redirectUrl;
            }
        }, 1000);
        
        function setActiveLink(link) {
            const links = document.querySelectorAll('.nav-link');
            links.forEach((el) => el.classList.remove('active'));

            link.classList.add('active');
        }

        function toggleForm(type) {
            document.getElementById("form-action").value = type;
            document.getElementById("form-title").innerText = type === "login" ? "Login" : "Sign Up";
            document.querySelector("button[type='submit']").innerText = type === "login" ? "Sign In" : "Register";

            document.querySelector("p").innerHTML = type === "login" ? 
                "Don't have an account? <a href='#' onclick='toggleForm(\"signup\")' class='white-text'>Sign up</a>" : 
                "Already have an account? <a href='#' onclick='toggleForm(\"login\")' class='white-text'>Login</a>";

            const loginButton = document.getElementById('login-btn');
            const signupButton = document.getElementById('signup-btn');

            if (type === 'login') {
                loginButton.classList.add('active');
                signupButton.classList.remove('active');
            } else if (type === 'signup') {
                signupButton.classList.add('active');
                loginButton.classList.remove('active');
            }
        }
    </script>
    <?php endif; ?>
<script src="../assets/javascript/auth.js"></script>
</body>
</html>