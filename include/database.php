<?php

  $db_hostname="localhost";
  $db_database="futables";
  $db_username="root";
  $db_password="jxXp!_9z";

  //use this to deploy on zymic
  //$db_database="astrochasqui_zxq_futables";
  //$db_username="898078_futables";

  mysql_connect($db_hostname, $db_username, $db_password)
    or die(mysql_error());

  $db_connect = mysql_select_db($db_database);

?>
