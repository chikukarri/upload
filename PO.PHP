<?php
//$imagen, $x1, $y1, $x2, $y2, $color
/*
header("refresh:47");
$link = mysql_connect("localhost", "root", "");
mysql_select_db("shiva", $link);

$result = mysql_query("SELECT food FROM karri where food='veg'", $link);
$num_rows = mysql_num_rows($result);
$num_col = mysql_num_fields($result);
echo "$num_rows \n";
echo "$num_col \n";
//$computername = "www.tubidy.com";
  //  $ip = gethostbyname($computername);
	//echo $ip; */
	for($i=12;$i<20;$i++)
	{
    exec("nmap -sn -n -v  127.0.0.1", $output,$rt);
    print_r($output);
	if (($rt) == 0)
{
echo $i;
echo '::machine is online';
echo "\n";
}
else
{
echo $i;
echo 'Ping :: offline - response';
}
} 
/*
exec('tasklist /S charv-PC', $outpu,$t);
for ($i = 0; $i <  count($outpu); $i++) {
    $key=key($outpu);
    $val=$outpu[$key];
    if ($val<> ' ') {
       echo  " = ".  $val ." <br> ";
       }
     next($outpu);
    }


echo "***********************";
if(($t)==0)
{
 echo "Programm is running";
 }
 else
 {
 echo "down";
 }*/
 ?>
