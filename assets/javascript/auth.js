let formJustSwitched = false;

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("button[data-toggle-form]").forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            formJustSwitched = true;
            toggleForm(link.dataset.toggleForm); // 'signup' or 'login'
        });
    });
    attachToggleFormListeners();
});


function attachToggleFormListeners() {
    document.querySelectorAll("[data-toggle-form]").forEach(link => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            formJustSwitched = true;
            toggleForm(link.dataset.toggleForm);
        });
    });
}

// Toggle the form content (login/signup)
function toggleForm(type) {
    document.getElementById("form-action").value = type;
    document.getElementById("form-title").innerText = type === "login" ? "Login" : "Sign Up";
    document.querySelector("button[type='submit']").innerText = type === "login" ? "Sign In" : "Register";

    document.querySelector("p").innerHTML = type === "login"
        ? `Don't have an account? <a href="#" data-toggle-form="signup" class='white-text toggle-form-link'>Sign up</a>`
        : `Already have an account? <a href="#" data-toggle-form="login" class='white-text toggle-form-link'>Login</a>`;

    attachToggleFormListeners();
    if (formJustSwitched) {
        const errorEl = document.getElementById('error-message');
        if (errorEl) errorEl.innerText = '';
        formJustSwitched = false; 
    }

    // Set active button
    const loginButton = document.getElementById('login-btn');
    const signupButton = document.getElementById('signup-btn');

    if (type === 'login') {
        loginButton.classList.add('active');
        signupButton.classList.remove('active');
    } else {
        signupButton.classList.add('active');
        loginButton.classList.remove('active');
    }
}

// On load, use URL param or default to login
window.onload = function () {
    const params = new URLSearchParams(window.location.search);
    const formType = params.get('form');
    toggleForm(formType === 'signup' || formType === 'login' ? formType : 'login');
};

