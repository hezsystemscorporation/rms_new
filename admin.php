<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qiao's Handmade Login</title>
    <link rel="icon" href="qiao_logo.svg" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('bg_07_16.jpg');
            background-size: 100%;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
        }
        .main-container {
            display: flex;
            position: relative;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            width: 30%;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .login-container h2 {
            margin-top: 0;
            text-align: center;
        }
        .login-container form {
            text-align: center;
        }
        .login-container input[type="text"],
        .login-container input[type="password"],
        .login-container button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .links {
            color: #00aeec;
        }
        .links:hover {
            color: #00aeec;
        }
        .links:visited {
            color: #00aeec;
        }
        .feedback {
            font-size: 14px;
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php
session_start();
include 'connectdb.php';  // Include database connection

// Generate and verify CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return $token === $_SESSION['csrf_token'];
}

// Check if user is already logged in and redirect to backstage page
if (isset($_SESSION['username'])) {
    header("Location: backstage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form input
    $s_name = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $s_password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        echo "<div class='feedback' style='color: red;'>Invalid CSRF Token</div>";
        exit();
    }

    // When logging in, use database to verify username and password
    if (isset($_POST['login'])) {
        $stmt = $conn->prepare("SELECT s_name, s_password FROM seller WHERE s_name = :s_name");
        $stmt->bindValue(':s_name', $s_name, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($s_password, $user['s_password'])) {
                $_SESSION['username'] = $user['s_name'];
                $_SESSION['last_activity'] = time();
                header("Location: backstage.php");
                exit();
            } else {
                echo "<div class='feedback' style='color: red;'>Username or password incorrect!</div>";
            }
        } else {
            echo "<div class='feedback' style='color: red;'>Username or password incorrect!</div>";
        }
    }
}
?>
<!-- Login Form -->
<div class="main-container">
    <div class="login-container">
        <img src="qiao_logo.svg" style="width: 300px;">
        <h1 style="font-family: 'Segoe UI Light', Arial, sans-serif; padding: 0px; margin: 0px; color: #aaa;" id="greetings"></h1>
        <h2 style="margin: 20px;">Admin Login</h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="text" name="username" placeholder="Username" id="username" onfocus="inputFocus(this)" required>
            <input type="password" name="password" placeholder="Password" id="password" onfocus="inputFocus(this)" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</div>

<script>
    var currentDate = new Date();
    var currentHour = currentDate.getHours();
    var body = document.body;
    if (currentHour < 4 || currentHour > 19) {
        body.style.backgroundImage = "url('bg_19_04.jpg')";
        document.getElementById("greetings").innerHTML = "May you have a good night.";
    } else if (currentHour >= 4 && currentHour < 7) {
        body.style.backgroundImage = "url('bg_04_07.jpg')";
        document.getElementById("greetings").innerHTML = "Good morning. <br>A new day is about to begin.";
    } else if (currentHour >= 16 && currentHour <= 19) {
        body.style.backgroundImage = "url('bg_16_19.jpg')";
        document.getElementById("greetings").innerHTML = "The day's coming to an end. <br>Now cherish the evening sunset.";
    } else {
        body.style.backgroundImage = "url('bg_07_16.jpg')";
        document.getElementById("greetings").innerHTML = "Good afternoon! <br>Hope you're having a great day!";
    }

    function inputFocus(input) {
        input.style.borderColor = "#007bff";
    }
</script>

</body>
</html>
