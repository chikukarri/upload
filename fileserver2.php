?php
// connect and login data
$web = 'www.alnada-capital.com';
$user = 'alfarisr';
$pass = 'al123456';
// file location
$server_file = '/public_html/example.txt';
$local_file = 'example.txt';
//connect
$conn_id = ftp_connect($web);
$login_result = ftp_login($conn_id,$user,$pass);
//uploading
if (ftp_put($conn_id, $server_file, $local_file, FTP_BINARY))
 {echo "Success";} 
else {echo "Failed";}
?>