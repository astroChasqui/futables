<?php

  include_once "include/functions.php";
  include_once "include/database.php";

  $username = $name = $email = $msg = $errMsg = $hideSignup = "";

  if (isset($_POST["signup"])) {
    $username = get_post("username");
    $password = get_post("password");
    $name = get_post("name");
    $email = get_post("email");
    $errMsg .= validate_name($name);
    $errMsg .= validate_email($email);
    $errMsg .= validate_username($username);
    $errMsg .= check_username_exists($username);
    $errMsg .= validate_password($password);
    if (!$errMsg) {
      $saltL = "*w!k";
      $saltR = "d@E&";
      $token = md5("$saltL$password$saltR");
      $query = "INSERT INTO users
                (username, password, name, email, signup_date, last_login)
                VALUES ('$username', '$token', '$name', '$email',
                        NOW(), NOW())";
      queryMysql($query);
      $msg = "Sign up successful. Go back to the home page and log in.";
      $hideSignup = "hidden";
    }
  }

?>
