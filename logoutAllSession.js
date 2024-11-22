// logout-handler.js
window.addEventListener('storage', function(event) {
    if (event.key === 'isLoggedOut' && event.newValue === 'true') {
        // Log the user out in this tab
        window.location.href = '../USER-VERIFICATION/login.php';
    }
});

// Optional: Clear the logout flag when the user logs in again it will not trigger accidental logout
if (window.location.pathname === '../USER-VERIFICATION/index.php') {
    localStorage.removeItem('isLoggedOut');
}