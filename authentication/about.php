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
    <?php include '../taskflow/component functions/navbar_about.php'; ?>

    <div class="container-sm mt-3">
        <img src="../assets/images/devTeam.jpg" class="rounded mx-auto d-block img-fluid" style="max-width: 750px;" alt="Development Team">
    </div>

    
    <div class="container-fluid mt-3 d-flex justify-content-center">
        <h2 class="text-center">Meet the brilliant minds behind the project!</h2>
        <ul class="list-group list-group-horizontal">
            <li class="list-group-item">John Patrick Palanas</li>
            <li class="list-group-item">Rydell Clyde Serrano</li>
            <li class="list-group-item">Gabriel Verzosa</li>
            <li class="list-group-item">Ace Merced</li>
        </ul>
    </div>
    


    <script>
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
</body>
</html>