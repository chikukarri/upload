<?php
$handle = @fopen("data.txt", "r");
$conn = mysql_connect("localhost","username","password"); 
mysql_select_db("mydatabase",$conn);
while (!feof($handle)) // Loop til end of file.
{
$buffer = fgets($handle, 4096);
 // Read a line.
list($a,$b,$c)=explode("|",$buffer);
//Separate string by the means of |
echo $a."-".$b."-".$c."<br>";
$sql = "INSERT INTO data_table (iddata, name, age) VALUES('".$a."','".$b."',".$c.")";   
mysql_query($sql,$conn) or die(mysql_error());
}
?>