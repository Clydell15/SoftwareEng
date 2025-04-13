<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: authentication/auth.php");
    exit();
}
?>