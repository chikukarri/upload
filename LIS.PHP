<?php 
echo "karri";
$ftp_server = "127.0.0.1";
$ftp_user_name = "karri";
$ftp_user_pass = "123";
// $remote_dir = $_POST["folder"]."/";

// set up basic connection
$conn_id = ftp_connect($ftp_server);

// login with username and password
$login_result = @ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

//default values
$file_url = "";

if($login_result) {
//set passive mode enabled
ftp_pasv($conn_id, true);
$rt=".";
$ret = ftp_nlist($conn_id,$rt);
foreach($ret as $rts)
{
echo "<a href='dow.php?download_file=$rts'>$rts</a>"."<br />";
}
}



?>