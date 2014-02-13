<?php

  include_once "include/functions.php";
  include_once "include/database.php";

  $username = $errMsg = $hideSignup = "";
  $hideLogin  = "";
  $hideLogout = $hideManage = "hidden";

  if (isset($_POST["login"])) {
    $username = get_post("username");
    $password = get_post("password");
    $errMsg .= validate_username($username);
    $errMsg .= validate_password($password);
    if (!$errMsg) {
      $saltL = "*w!k";
      $saltR = "d@E&";
      $token = md5("$saltL$password$saltR");
      $query = "SELECT * FROM users
                WHERE username = '$username' AND password = '$token'";
      $result = queryMysql($query);
      if (mysql_num_rows($result)) {
        $row = mysql_fetch_assoc($result);
        $_SESSION["user_id"]   = $user_id   = $row["id"];
        $_SESSION["user_name"] = $user_name = $row["name"];
      } else {
        $errMsg = "Invalid username/password combination";
      }
    }
  }

  if (isset($_POST["logout"])) destroySession();

  if (isset($_SESSION["user_id"])) {
    $hideLogin  = "hidden";
    $hideSignup = "hidden";
    $hideManage = "";
    $hideLogout = "";
  }

  //print_r($_POST);
  //print_r($_SESSION);
  //print_r(session_id());

?>
