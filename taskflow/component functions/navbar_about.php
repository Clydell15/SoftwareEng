<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg shadow-sm auth-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">Task Flow</a>

        <div class="mx-auto d-flex">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-3">
                <li class="nav-item">
                    <a href="auth.php"
                       class="nav-link nav-underline <?php echo $current_page == 'auth.php' ? 'active' : ''; ?>">
                       Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="about.php"
                       class="nav-link nav-underline <?php echo $current_page == 'about.php' ? 'active' : ''; ?>">
                       About Us
                    </a>
                </li>
            </ul>
        </div>

        <div class="d-flex gap-2">
            <button onclick="window.location.href='auth.php?form=login'"  class="btn btn-outline-secondary" id="login-btn">Login</button>
            <button onclick="window.location.href='auth.php?form=signup'" class="btn btn-outline-secondary" id="signup-btn">Signup</button>

        </div>
    </div>
</nav>
