<?php
require("conf/config.php");
require("classes/PHPMailer_5.2.1/class.phpmailer.php");

set_time_limit(0);

//Change status True & False for Jquery
function fix_true_false($value){
	if ($value){
		return 'true';
	}
	else{
		return 'false';
	}
}

//Send syslog message
function send_remote_syslog($ip, $hostname, $server, $port=514, $facility=14, $severity=3, $type) {

	$priority_value=$facility*8+$severity;
	
	if ($type=='down'){
		$syslog_msg = '<'.$priority_value.'> '. date('Y M j H:i:s ') . ': The host '.$hostname.' ('.$ip.') is down.';
	}
	else{
		$syslog_msg = '<'.$priority_value.'> '. date('Y M j H:i:s ') . ': The host '.$hostname.' ('.$ip.') is back up.';
	}
	
	if (!$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)){
		exit("Cannot create socket");
	}
	if (!socket_sendto($sock, $syslog_msg, strlen($syslog_msg), 0, $server, $port)){
		exit("Cannot send syslog message");
	}
	socket_close($sock);
	
}

//Send  multiple alert via mail
function send_mail($ip, $hostname, $first_down, $last_down, $latency) {
	$mail = new PHPMailer();

	$mail->IsSMTP();
	$mail->Host = $GLOBALS['mail_server'];
	$mail->SMTPAuth = $GLOBALS['mail_auth'];
	if ($GLOBALS['mail_auth']){
		$mail->Username = $GLOBALS['mail_username'];
		$mail->Password = $GLOBALS['mail_password'];
	}
	$mail->Port = $GLOBALS['mail_port'];
	$mail->SMTPSecure = $GLOBALS['mail_secure_protocol'];

	$mail->From = $GLOBALS['mail_from'];
	$mail->FromName = $GLOBALS['mail_from'];
	
	$mail->WordWrap = 200;
	$mail->IsHTML(false);
	
	if($first_down==0){
		$minutes_down=0;
	}
	else{
		$minutes_down=round(($last_down-$first_down)/60);
	}
	
	if ($latency=='-1'){
		$mail->Subject = "Host error: ". $hostname.' (' .$ip .') is down';
		$mail->Body    = "The hostname ".$hostname. ' (' . $ip .") is down.\n\n";
		$mail->Body    .= $hostname. ' (' . $ip .") downtime is ". $minutes_down . " minutes.\n";
		$mail->Body    .= "First down: ".date("r",$first_down)."\n";
		$mail->Body    .= "Last check: ".date("r",$last_down)."\n";
	}
	else{
		$mail->Subject = "Host notice: ". $hostname.' (' .$ip .') is up';
		$mail->Body    = "The hostname ".$hostname. ' (' . $ip .") is back up.\n\n";
		$mail->Body    .= "Host previously down for: ". $minutes_down ." minutes.\n";
		$mail->Body    .= "Last check: ".date("r",$last_down)."\n";
		$mail->Body    .= "Current ping response: ".$latency."\n";
	}
	
	$mail_to_list=explode(',', $GLOBALS['mail_to']);

	$mail_counter=0;
	while($mail_counter < count($mail_to_list)){
		$mail_new = clone $mail;
		$mail_new->AddAddress(str_replace(' ', '', $mail_to_list[$mail_counter]));
		$mail_new->Send();
		$mail_counter++;
	}

}

if (((isset($_GET['secret']) && $secret_key == $_GET['secret']) || (isset($argv) && $secret_key==$argv[1]))){

	if(($nmap_folder=='' || !is_dir($nmap_folder)) || ($script_folder=='' && !is_dir($script_folder))){
		exit ('Check your nmap or script absolute path');
	}

	$loop=true;
	echo 'Status: Running... ';
	while($loop){

		//Needed for reload variables using php command line
		require("conf/config.php");

		if (isset($argv)){
			$loop=true;
		}
		else{
			$loop=false;
		}

		unset($i, $latency, $contents, $tot_hosts, $top_hosts_up, $top_hosts_down, $html_hosts_tmp_up, $html_hosts_tmp_down, $entry, $html_tmp, $n_tot, $xml_tmp, $info_stat_host);
		$i=0; $latency=''; $tot_hosts=0; $top_hosts_up=0; $top_hosts_down=0; $html_hosts_tmp_up=''; $html_hosts_tmp_down=''; $html_tmp='';
		
		$hosts_info=array();
		$hosts_name_tmp=array();
		$hosts_name=array();
		$hosts_ip=array();
		$info_stat_host=array();
		$list_ip=array();
		
		chdir($nmap_folder);

		//Only for advanced configuration
		exec("nmap -sn -n -v -iL ".$script_folder."conf/".$file_ip_list." -oX ".$script_folder."TMP/".$file_tmp_xml);
		exec("nmap -sn -n -v -iL ".$script_folder."conf/".$file_ip_list." -oX ".$script_folder."TMP/".$file_tmp_xml);

		chdir($script_folder);
		chmod($script_folder."TMP/".$file_tmp_xml, 0666);

		$xml_tmp = simplexml_load_file($script_folder.'TMP/'.$file_tmp_xml);
		
		foreach($xml_tmp->host as $host){
			$hosts_info[$tot_hosts]['ip']=(string)$host->address['addr'];
			$hosts_info[$tot_hosts]['state']=(string)$host->status['state'];
			$hosts_info[$tot_hosts]['latency']=(string)$host->times['srtt'];
			$list_ip[$tot_hosts]=(string)$host->address['addr'];
			$tot_hosts++;
		}
		
		$n_tot=0;	
		if (!$handle = fopen($script_folder.'conf/'.$file_ip_list, "r")) {
			exit ("Cannot open file -r- conf/".$file_ip_list." file");
		}

		while (($buffer = fgets($handle)) !== false) {
			if (preg_match("/ # /", $buffer)){
				$hosts_name_tmp=explode(' # ', $buffer);
				$hosts_name[$n_tot]=@str_replace(array("\r", "\n"), "", (string)$hosts_name_tmp[1]);
				$hosts_ip[$n_tot]=@str_replace(array("\r", "\n"), "", (string)$hosts_name_tmp[0]);
				$n_tot++;
			}
		}
		if (!feof($handle)) {
			exit("Error: unexpected fgets() fail\n");
		}
		fclose($handle);
		
		$i=0;
		while($i<$n_tot){
			if (@$hosts_info[$i]['state']=='up'){
				$html_hosts_tmp_up .=  '<span class="host_up">IP: '.$hosts_ip[$i].'<br />Name: '.$hosts_name[$i].'<br />Latency: ';
				if ($hosts_info[$i]['latency']==0){
					$latency='<1ms';
				}
				else{
					$latency=round($hosts_info[$i]['latency']/1000000,3).'ms';
				}

				$html_hosts_tmp_up .=  ' '.$latency.'</span>';
				
				if (file_exists($script_folder.'TMP/'.$hosts_ip[$i])){
				
					$info_stat_host=array();

					if (!$handle = fopen($script_folder.'TMP/'.$hosts_ip[$i], "r")){
						exit("INDEX: Cannot open TMP/".$hosts_ip[$i]." file");
					}
					if (!$contents = fread($handle , filesize($script_folder.'TMP/'.$hosts_ip[$i]))){
						exit("INDEX: Cannot read TMP/".$hosts_ip[$i]." file");
					}
					fclose($handle);
					$info_stat_host=explode('|',$contents);
					
					if (!unlink($script_folder.'TMP/'.$hosts_ip[$i])){
						exit ("Cannot delete TMP/".$hosts_ip[$i]." file");
					}
					
					if ($syslog_alert){
						send_remote_syslog($hosts_ip[$i], $hosts_name[$i], $syslog_server, $syslog_port, $syslog_facility, $syslog_severity, 'up');
					}

					if ($mail_alert){
						send_mail($hosts_ip[$i], $hosts_name[$i], (int)$info_stat_host[0], time(), $latency);
					}
					
				}
				
				$top_hosts_up++;
			}
			else{
				$info_stat_host=array();
				$new_tmp_file=false;

				if (@filesize('TMP/'.$hosts_ip[$i])<=0){
				
					if (!$handle = fopen($script_folder.'TMP/'.$hosts_ip[$i], "w")){
						exit("Cannot open -w- TMP/".$hosts_ip[$i]." file");
					}
					if (!fwrite($handle,time().'|'.time())){
						exit("Cannot write TMP/".$hosts_ip[$i]." file");
					}
					fclose($handle);
					
					chmod($script_folder.'TMP/'.$hosts_ip[$i], 0666);
					$new_tmp_file=true;

				}

				if (!$handle = fopen($script_folder.'TMP/'.$hosts_ip[$i], "r")){
					exit("INDEX: Cannot open TMP/".$hosts_ip[$i]." file");
				}
				if (!$contents = fread($handle , filesize($script_folder.'TMP/'.$hosts_ip[$i]))){
					exit("INDEX: Cannot read TMP/".$hosts_ip[$i]." file");
				}
				fclose($handle);

				$info_stat_host=explode('|',$contents);
				
				if ($info_stat_host[1]<=(time()-$threshold*60) || $new_tmp_file){

					if (!$handle = fopen($script_folder.'TMP/'.$hosts_ip[$i], "w")){
						exit("Cannot open -w- TMP/".$hosts_ip[$i]." file");
					}
					if (!fwrite($handle,$info_stat_host[0].'|'.time())){
						exit("Cannot write TMP/".$hosts_ip[$i]." file");
					}
					fclose($handle);

					if ($syslog_alert){
						send_remote_syslog($hosts_ip[$i], $hosts_name[$i], $syslog_server, $syslog_port, $syslog_facility, $syslog_severity, 'down');
					}

					if ($mail_alert){
						send_mail($hosts_ip[$i], $hosts_name[$i], (int)$info_stat_host[0], time(), '-1');
					}
				}
				
				$html_hosts_tmp_down .=  '<span class="host_down">IP: '.$hosts_ip[$i].'<br />Name: '.$hosts_name[$i].'<br />Fail: '.date('Y M D H:i:s',$info_stat_host[0]).'</span>';
				$top_hosts_down++;
			}
			$i++;
		}
		
		$html_tmp = '<div id="header"><div id="title"><img src="images/title.png" /></div><div id="info"><div id="hosts_info"><span id="total_hosts">Total hosts: '.$n_tot.'</span><span id="n_up_hosts">Up: <span id="up">'.$top_hosts_up.'</span></span><span id="n_down_hosts">Down: <span id="down">'.$top_hosts_down.'</span></span></div><div id="scan_info">Scanning time: '.$xml_tmp->runstats->finished['elapsed'].' seconds</div><div id="update_info">Last updated: '.date('H:i:s').'</div></div></div>';
		$html_tmp .= '<div class="space-top"></div>';
		$html_tmp .= '<div id="hosts_down_box"><div id="hosts_down_title">Hosts Down</div><div id="hosts_down">'.$html_hosts_tmp_down.'</div></div>';
		$html_tmp .= '<div id="hosts_up_box"><div id="hosts_up_title">Hosts Up</div><div id="hosts_up">'.$html_hosts_tmp_up.'</div></div>';
		$html_tmp .= '<div class="space-bottom"></div>';
		$html_tmp .= '<div id="footer"><div id="last_updated"><a href="admin.php">Admin Page</a> - <a href="http://code.google.com/p/whoisup/" target="_blank">WhoisUP&copy;</a></div></div>';
		$html_tmp .= '<div id="value_tmp"><div id="popup_alert">'.fix_true_false($popup_alert).'</div><div id="sound_alert">'.fix_true_false($sound_alert).'</div><div id="sound_file">'.$sound_file.'</div></div>';

		if (!$handle = fopen($script_folder.'TMP/'.$file_tmp_html, 'w')) {
			exit ("Cannot open TMP/".$file_tmp_html." file");
		}
		if (fwrite($handle, $html_tmp) === FALSE) {
			exit ("Cannot write TMP/".$file_tmp_html." file");
		}
		fclose($handle);
		
		chmod($script_folder.'TMP/'.$file_tmp_html, 0666);
	
		if ($loop){
			sleep($refresh_time);
		}
	}

	header('refresh:'.($refresh_time).';url=core.php?secret='.$secret_key);
	exit();
	
}
else{

	if (!$handle = @fopen($script_folder.'TMP/'.$file_tmp_html, "r")){
		$contents = 'Have you enabled the core.php script?';
	}
	else{
		if (!$contents = @fread($handle , filesize($script_folder.'TMP/'.$file_tmp_html))){
			$contents = 'Have you enabled the core.php script?';
		}
		fclose($handle);
	}
	echo $contents;
}
?>