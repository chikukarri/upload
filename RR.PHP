<?php
$con=mysql_connect("localhost","root","");
// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  $data="mysql";
 @mysql_select_db($data); 
//$result=mysql_query($con,"show databases");
$query="SHOW TABLES";$result=mysql_query($query);
while($row = mysql_fetch_array($result))
  {
  echo $row['Tables_in_mysql'];
  echo "<br>";
  }
  mysql_close($con);
  ?> 