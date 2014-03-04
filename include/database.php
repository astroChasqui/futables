<?php

  $db_hostname="localhost";
  $db_database="futables";
  $db_username="root";
  $db_password="jxXp!_9z";
  
  //to deploy on 000webhost
  //$db_hostname = "mysql13.000webhost.com";
  //$db_database = "a3669356_futable";
  //$db_username = "a3669356_root";
  //$db_password = "jxXp!_9z";

  mysql_connect($db_hostname, $db_username, $db_password)
    or die(mysql_error());

  $db_connect = mysql_select_db($db_database);

?>
