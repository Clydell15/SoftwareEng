<?php
$stmtTags = $conn->prepare("SELECT * FROM tags WHERE user_id = ? AND archived = 0 ORDER BY id DESC");
$stmtTags->bind_param("i", $_SESSION['user_id']);
$stmtTags->execute();
$resultTags = $stmtTags->get_result();
$categories = $resultTags->fetch_all(MYSQLI_ASSOC);
$stmtTags->close();
?>