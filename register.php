<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "cardDB";

// Create connection with better error handling
$data = mysqli_connect($host, $user, $password, $db);

if ($data === false) {
    $_SESSION['Message'] = "Database Connection Error: " . mysqli_connect_error();
    header("location: index.php");
    exit();
}

if (isset($_POST['apply'])) {
    $fullname = mysqli_real_escape_string($data, $_POST['fullname']);
    $username = mysqli_real_escape_string($data, $_POST['username']);
    $email = mysqli_real_escape_string($data, $_POST['email']);
    $mobile = mysqli_real_escape_string($data, $_POST['mobile']);
    $password = mysqli_real_escape_string($data, $_POST['password']);

    // Validate input
    if (empty($fullname) || empty($username) || empty($email) || empty($mobile) || empty($password)) {
        $_SESSION['Message'] = "All fields are required!";
        header("location: index.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['Message'] = "Invalid email format!";
        header("location: index.php");
        exit();
    }

    // Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($data, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['Message'] = "Email already exists!";
        header("location: index.php");
        exit();
    }

    // Check if username already exists
    $check_username_sql = "SELECT * FROM users WHERE username = ?";
    $stmt2 = mysqli_prepare($data, $check_username_sql);
    mysqli_stmt_bind_param($stmt2, "s", $username);
    mysqli_stmt_execute($stmt2);
    $check_username_result = mysqli_stmt_get_result($stmt2);

    if (mysqli_num_rows($check_username_result) > 0) {
        $_SESSION['Message'] = "Username already exists!";
        header("location: index.php");
        exit();
    }

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data using prepared statement
    $sql = "INSERT INTO users (fullname, username, email, mobile, password) VALUES (?, ?, ?, ?, ?)";
    $stmt3 = mysqli_prepare($data, $sql);
    mysqli_stmt_bind_param($stmt3, "sssss", $fullname, $username, $email, $mobile, $hashed_password);
    
    if (mysqli_stmt_execute($stmt3)) {
        $_SESSION['Message'] = "User Registered Successfully!";
        header("location: index.html");
        exit();
    } else {
        $_SESSION['Message'] = "Registration Failed: " . mysqli_error($data);
        header("location: index.php");
        exit();
    }
}

mysqli_close($data);
?>