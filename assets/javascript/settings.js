function toggleEditAccount() {
    const inputs = document.querySelectorAll('#account-settings-form input');
    const newPasswordDiv = document.getElementById('new_password_div');
    const confirmPasswordDiv = document.getElementById('confirm_password_div');
    const editBtn = document.getElementById('edit-account-button');
    const saveBtn = document.getElementById('save-account-button');
    const cancelBtn = document.getElementById('cancel-account-button');

    const isReadonly = inputs[0].hasAttribute('readonly');

    console.log('isReadonly:', isReadonly, 'cancelBtn:', cancelBtn);

    inputs.forEach(input => {
        if (input.id !== "new_password" && input.id !== "confirm_password") {
            input.readOnly = !isReadonly;
        }
    });


    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (isReadonly) {
        newPasswordInput.readOnly = false;
        confirmPasswordInput.readOnly = false;
    } else {
        newPasswordInput.readOnly = true;
        confirmPasswordInput.readOnly = true;
    }

    if (isReadonly) {
        newPasswordDiv.classList.remove('d-none');
        confirmPasswordDiv.classList.remove('d-none');
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
        cancelBtn.classList.remove('d-none');
    } else {
        newPasswordDiv.classList.add('d-none');
        confirmPasswordDiv.classList.add('d-none');
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    }
}


function toggleEditPomodoro() {
    const inputs = document.querySelectorAll('#pomodoro-settings-form input');
    const editBtn = document.getElementById('edit-pomodoro-button');
    const saveBtn = document.getElementById('save-pomodoro-button');

    // Check if the inputs are readonly
    const isReadonly = inputs[0].hasAttribute('readonly');

    inputs.forEach(input => {
        input.readOnly = !isReadonly;
    });

    // Toggle visibility of buttons
    if (isReadonly) {
        editBtn.classList.add('d-none');
        saveBtn.classList.remove('d-none');
    } else {
        editBtn.classList.remove('d-none');
        saveBtn.classList.add('d-none');
    }
}
