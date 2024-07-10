<!DOCTYPE html>
<html>
<head>
    <title>Our cookies are being dumped...</title>
    <!-- other head elements -->
</head>
<body>

<!-- Your HTML content goes here -->

<script>
    function logout() {
        //console.log("Running logout");
        if (checkCookie()) {
            console.log("Cookie Found; Destroying Cookie");
            document.cookie = "username=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; Secure; SameSite=None;";
        } else {
            console.log("No Cookie was Found");
        }

        // Redirect the user to the login page or perform any other logout actions
        window.location.href = "Landing/Landing.php";
    }

    function checkCookie() {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i].trim();
            if (cookie.startsWith("username=")) {
                // 'username' cookie found
                return true;
            }
        }
        // 'username' cookie not found
        return false;
    }

    // Automatically run the logout function when the page loads
    window.onload = logout;
</script>

</body>
</html>
