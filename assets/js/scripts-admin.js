document.addEventListener('DOMContentLoaded', function () {
    // Debugging log to ensure the script is loading
    console.log('scripts.js loaded and DOM fully loaded');

    var editButton = document.getElementById('edit-api-key-btn');
    var apiKeyDisplay = document.getElementById('api-key-display');
    var apiKeyInput = document.getElementById('api-key-input');

    if (editButton && apiKeyDisplay && apiKeyInput) {
        editButton.addEventListener('click', function () {
            // Show the input field and hide the display paragraph
            apiKeyDisplay.style.display = 'none';
            apiKeyInput.style.display = 'inline-block';
            apiKeyInput.focus();
            console.log('Edit button clicked, input field displayed');
        });
    } else {
        console.log('One or more elements not found: editButton, apiKeyDisplay, or apiKeyInput');
    }
});