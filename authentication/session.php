<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: authentication/login.php");
    exit();
}
?>