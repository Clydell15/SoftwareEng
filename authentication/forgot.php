<?php
session_start();
include '../config db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

$message = "";
$step = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $forgot_code = strtoupper(bin2hex(random_bytes(3)));
            $stmt = $conn->prepare("UPDATE users SET forgot_code=? WHERE email=?");
            $stmt->bind_param("ss", $forgot_code, $email);
            $stmt->execute();

            // Send email
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
                $mail->Subject = 'TaskFlow Password Reset Code';
                $mail->Body    = "Your password reset code is: <b>$forgot_code</b>";

                $mail->send();
                $_SESSION['forgot_email'] = $email;
                $message = "Verification code sent to your email.";
                $step = 2;
            } catch (Exception $e) {
                $message = "Could not send verification email. Please contact support.";
            }
        } else {
            $message = "Email not found.";
        }
    } elseif (isset($_POST['verify_code'], $_POST['new_password'], $_POST['confirm_password'])) {
        $email = $_SESSION['forgot_email'] ?? '';
        $code = strtoupper(trim($_POST['verify_code']));
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!$email) {
            $message = "Session expired. Please try again.";
        } elseif ($new_password !== $confirm_password) {
            $message = "Passwords do not match.";
            $step = 2;
        } elseif (strlen($new_password) < 6) {
            $message = "Password must be at least 6 characters.";
            $step = 2;
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND forgot_code=?");
            $stmt->bind_param("ss", $email, $code);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET password=?, forgot_code=NULL WHERE email=?");
                $stmt->bind_param("ss", $hashed_password, $email);
                $stmt->execute();
                unset($_SESSION['forgot_email']);
                $message = "Password updated successfully! You can now log in.";
                $step = 3;
            } else {
                $message = "Invalid verification code.";
                $step = 2;
            }
        }
    }
} elseif (isset($_SESSION['forgot_email'])) {
    $step = 2;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color:rgb(206, 221, 237); }
        .auth-container { max-width: 400px; margin: auto; padding-top: 100px; }
        .card { padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="auth-page">
    <?php include '../taskflow/component functions/navbar.php'; ?>
    <div class="auth-container">
        <div class="card text-center">
            <h2 class="mb-4">Forgot Password</h2>
            <?php if ($message): ?>
                <div class="mb-3">
                    <p class="alert alert-<?php
                        echo (
                            $step === 3 ||
                            $message === "Verification code sent to your email."
                        ) ? 'success' : 'danger';
                    ?> mb-2"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label for="email" class="form-label">Enter your email</label>
                        <input type="email" class="form-control text-center" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-2">Send Verification Code</button>
                </form>
            <?php elseif ($step === 2): ?>
                <form method="POST" class="mb-3">
                    <div class="mb-3">
                        <label for="verify_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control text-center" id="verify_code" name="verify_code" maxlength="12" required style="text-transform:uppercase;">
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control text-center" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control text-center" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-2">Reset Password</button>
                </form>
            <?php elseif ($step === 3): ?>
                <a href="auth.php?form=login" class="btn btn-secondary w-100 rounded">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>