<?php

  function sanitizeString($var)
  {
    $var = strip_tags($var);
    $var = htmlentities($var);
    $var = stripslashes($var);
    return mysql_real_escape_string($var);
  }

  function get_post($var)
  {
    return sanitizeString($_POST[$var]);
  }

  function queryMysql($query)
  {
    $result = mysql_query($query) or die("MySQL error: ".mysql_error());
    return $result;
  }

  function destroySession()
  {
    $_SESSION = array();
    if (session_id() != "" || isset($_COOKIE[session_name()]))
       setcookie(session_name(), '', time()-2592000, '/');
    if (session_id() != "") session_destroy();
  }

  // SIGN UP and LOG IN validations

  function validate_name($field) {
    if ($field == "") return "What is your name?<br />";
      else if (preg_match("/[^a-zA-Z' ']/", $field))
        return "Only letters allowed in names<br />";
    return "";
  }

  function validate_email($field) {
    if ($field == "") return "Need an email address to signup<br />";
      else if (!((strpos($field,".") > 0) &&
             (strpos($field,"@") > 0)) ||
             preg_match("/[^a-zA-Z0-9.@_-]/", $field))
      return "The email address is invalid<br />";
    return "";
  }

  function validate_username($field) {
    if ($field == "") return "No username was entered<br />";
      else if (strlen($field) < 5)
        return "Username must be at least 5 characters<br />";
      else if (preg_match("/[^a-zA-Z0-9.@_-]/", $field))
        return "Only letters, numbers, -, and _ in usernames<br />";
    return "";
  }

  function check_username_exists($field) {
    if (mysql_num_rows(queryMysql("SELECT id FROM users 
                                   WHERE username='$field'")))
      return "Username already exists<br />";
    return "";
  }

  function validate_password($field) {
    if ($field == "") return "Please enter your password<br />";
      else if (strlen($field) < 6)
        return "Password must be at least 6 characters<br />";
    return "";
  }

?>
