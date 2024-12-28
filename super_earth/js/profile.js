// Simulated local storage user data (corrected accounts)
const users = {
    "user": "123456",  // Regular user
    "admin": "admin123456"  // Admin user
};

// Function to encode data to Base64
function encodeBase64(data) {
    return btoa(data);
}

// Function to decode Base64 data
function decodeBase64(encodedData) {
    return atob(encodedData);
}

// Check if the user is logged in by verifying localStorage
function checkLoginStatus() {
    const loggedInUser = localStorage.getItem("loggedInUser");
    if (loggedInUser) {
        // Display user info if logged in
        document.getElementById("user-name").innerText = decodeBase64(loggedInUser);
    } else {
        // If not logged in, show alert and redirect to login page
        alert("You are not logged in. Please log in first.");
        window.location.href = "login.html";  // Redirect to the login page
    }
}

// Logout function: Remove user from localStorage and redirect to login page
function logout() {
    localStorage.removeItem("loggedInUser");  // Remove login status from localStorage
    window.location.href = "login.html";  // Redirect to login page
}

// Login function: Checks credentials and stores the login status in localStorage
function login(username, password) {
    const encodedUsername = encodeBase64(username);
    const encodedPassword = encodeBase64(password);

    if (users[username] && users[username] === decodeBase64(encodedPassword)) {
        localStorage.setItem("loggedInUser", encodedUsername);  // Store encoded login info
        if (username === "admin") {
            window.location.href = "admin.html";  // Redirect to admin page upon successful login
        } else {
            window.location.href = "profile.html";  // Redirect to user profile page upon successful login
        }
    } else {
        alert("Invalid username or password");
    }
}

// Update user profile: Allows users to update their username and password
function updateProfile() {
    const newUsername = sanitizeInput(document.getElementById("new-username").value);
    const newPassword = sanitizeInput(document.getElementById("new-password").value);

    // Update localStorage with new user data (Base64 encoded)
    const loggedInUser = localStorage.getItem("loggedInUser");
    if (loggedInUser) {
        const decodedUsername = decodeBase64(loggedInUser);
        if (users[decodedUsername]) {
            users[decodedUsername] = decodeBase64(newPassword);  // Update password
            localStorage.setItem("loggedInUser", encodeBase64(newUsername));  // Update username
            alert("Profile updated successfully!");
            window.location.reload();  // Reload the page to reflect updated data
        }
    }
}

// Admin actions: Only accessible by admin user to manage charity organizations
function adminActions() {
    const loggedInUser = localStorage.getItem("loggedInUser");
    if (loggedInUser && decodeBase64(loggedInUser) === "admin") {
        // Perform admin actions like creating, updating, or deleting charity organizations
        alert("Admin actions performed");
    } else {
        alert("You do not have admin privileges.");
    }
}

// Sanitize user input to prevent XSS and SQL injection attacks
function sanitizeInput(input) {
    const element = document.createElement('div');
    if (input) {
        element.innerText = input;  // Escape HTML characters
        element.textContent = input;  // Escape text content to avoid XSS
    }
    return element.innerHTML;  // Return the sanitized input
}

// Check login status on page load
// checkLoginStatus();