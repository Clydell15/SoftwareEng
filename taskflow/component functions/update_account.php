<?php
include '../../config db.php';
include '../../authentication/session.php';

$user_id = $_SESSION['user_id'];

// Fetch current user data from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted values
    $email = trim($_POST['email']);
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate the old password
    if (!password_verify($old_password, $user['password'])) {
        $_SESSION['account_error'] = "Old password is incorrect.";
        header("Location: ../settings.php");
        exit();
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['account_error'] = "Invalid email format.";
        header("Location: ../settings.php");
        exit();
    }

    // Validate password and confirmation if new password is set
    if (!empty($new_password) && !empty($confirm_password)) {
        $new_password_trimmed = trim($new_password);
        $confirm_password_trimmed = trim($confirm_password);

        if ($new_password_trimmed === "" || $confirm_password_trimmed === "") {
            $_SESSION['account_error'] = "New password cannot be empty or only spaces.";
            header("Location: ../settings.php");
            exit();
        }
        // Check for leading or trailing spaces
        if ($new_password !== $new_password_trimmed || $confirm_password !== $confirm_password_trimmed) {
            $_SESSION['account_error'] = "New password cannot start or end with spaces.";
            header("Location: ../settings.php");
            exit();
        }
        if (strlen($new_password) < 6) {
            $_SESSION['account_error'] = "New password must be at least 6 characters long.";
            header("Location: ../settings.php");
            exit();
        }
        if ($new_password !== $confirm_password) {
            $_SESSION['account_error'] = "New Password do not match.";
            header("Location: ../settings.php");
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user['password']; 
    }

    // Update user info in the database
    $update_sql = "UPDATE users SET email = ?, password = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $email, $hashed_password, $user_id);

    if ($update_stmt->execute()) {
        $_SESSION['account_success'] = "Account settings updated successfully.";
        header("Location: ../settings.php");
        exit();
    } else {
        $_SESSION['account_error'] = "Something went wrong. Please try again later.";
        header("Location: ../settings.php");
        exit();
    }
}
?>
