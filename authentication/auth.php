<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config db.php';

$error = "";

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
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
    } elseif ($action == "signup") {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        } else {
            // Check if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $checkResult = $check->get_result();
        
            if ($checkResult->num_rows > 0) {
                $error = "An account with this email already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $email, $hashed_password);
        
                if ($stmt->execute()) {
                    header("Location: auth.php?success=registered");
                    exit();
                } else {
                    $error = "Error creating account";
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
                    Don't have an account? <a href="#" onclick="toggleForm('signup')" class='white-text'>Sign up</a>
                </p>

                <?php if (!empty($error)) echo "<p class='text-warning text-center'>$error</p>"; ?>
            </form>
        </div>


    </div>

    <script>
        // Function to change the active link
        function setActiveLink(link) {
            // Remove active class from all links
            const links = document.querySelectorAll('.nav-link');
            links.forEach((el) => el.classList.remove('active'));

            // Add active class to the clicked link
            link.classList.add('active');
        }

        // Toggle the form content (login/signup)
        function toggleForm(type) {
            // Change the form title and button text
            document.getElementById("form-action").value = type;
            document.getElementById("form-title").innerText = type === "login" ? "Login" : "Sign Up";
            document.querySelector("button[type='submit']").innerText = type === "login" ? "Sign In" : "Register";


            
            // Change the link text in the form
            document.querySelector("p").innerHTML = type === "login" ? 
                "Don't have an account? <a href='#' onclick='toggleForm(\"signup\")' class='white-text'>Sign up</a>" : 
                "Already have an account? <a href='#' onclick='toggleForm(\"login\")' class='white-text'>Login</a>";

            // Set active class on the correct button
            const loginButton = document.getElementById('login-btn');
            const signupButton = document.getElementById('signup-btn');

            if (type === 'login') {
                // Activate the login button
                loginButton.classList.add('active');
                signupButton.classList.remove('active');
            } else if (type === 'signup') {
                // Activate the signup button
                signupButton.classList.add('active');
                loginButton.classList.remove('active');
            }
        }

        // Initially set active class based on default state (login)
        window.onload = function() {
            toggleForm('login');
        };
    </script>
</body>
</html>