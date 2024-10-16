<?php
require("conf/config.php");
$login_whoisup=false;

//Return the mail authentication protocol list
function view_auth_proto($mail_secure_proto){
	$smtp_secure_list='<select name="mail_secure_protocol" id="mail_secure_protocol" class="text">';
	$auth_list=array('none', '', 'TLS', 'tls', 'SSL', 'ssl');
	for ($i = 0; $i < count($auth_list); $i=$i+2){
		$j=$i+1;
		$smtp_secure_list.='<option value="'.$auth_list[$j].'"';
		if ($mail_secure_proto==$auth_list[$j]){
			$smtp_secure_list.=' selected="selected"';
		}
		$smtp_secure_list.='>'.$auth_list[$i].'</option>';
	}
	$smtp_secure_list.='</select>';
	return $smtp_secure_list;
}

//Return the css width list
function view_css_width($type, $width){
	$css_width_list='<select name="'.$type.'" id="'.$type.'" class="text">';
	$width_list=array('One', '894', 'Two', '440', 'Three', '288', 'Four', '213', 'Five', '167', 'Six', '137', );
	for ($i = 0; $i < count($width_list); $i=$i+2){
		$j=$i+1;
		$css_width_list.='<option value="'.$width_list[$j].'"';
		if ($width==$width_list[$j]){
			$css_width_list.=' selected="selected"';
		}
		$css_width_list.='>'.$width_list[$i].'</option>';
	}
	$css_width_list.='</select>';
	return $css_width_list;
}

//Fix the path
function fix_path($path){
	if($path!=''){
		$path=str_replace('\\', '/', $path);
		if($path[strlen($path)-1]!='/'){
			$path=$path.'/';
		}
	}
	return $path;
}

//List the sound file
function sound_file($sound){
	if ($handle = @opendir($script_folder.'sound_alert')) {
		$sound_tmp='<select name="sound_file" id="sound_file" class="text">';
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".."&& $entry != ".htaccess") {
				$sound_tmp.='<option value="'.$entry.'"';
				if ($entry==$sound){
					$sound_tmp.=' selected="selected"';
				}
				$sound_tmp.='>'.$entry.'</option>';
			}
		}
		$sound_tmp.='</select>';
		closedir($handle);
		return $sound_tmp;
	}
}

//Read the ip list file
function read_ip_list($file_ip_list){
	if (!$handle = @fopen($script_folder.'conf/'.$file_ip_list, "r")){
		exit("INDEX: Cannot open conf/".$file_ip_list." file");
	}
	$contents = @fread($handle , filesize($script_folder.'conf/'.$file_ip_list));
	return $contents;
}

//Return the radio input
function radio_chk($name,$value){
	if($value==''){
		$value==false;
	}
	if($value){
		return '<input type="radio" name="'.$name.'" id="'.$name.'_on" value="true" checked="checked" /> ON - <input type="radio" name="'.$name.'" id="'.$name.'_off" value="false" /> OFF';
	}
	else{
		return '<input type="radio" name="'.$name.'" id="'.$name.'_on" value="true" /> ON - <input type="radio" name="'.$name.'" id="'.$name.'_off" value="false" checked="checked" /> OFF';
	}
}

//Clean the cookie
if (isset($_POST['exit']) && $_POST['exit']=='Exit' && isset($_COOKIE['whoisup'])){
	setcookie('whoisup', '', time() - 86400);
	header('Location: admin.php');
	exit();
}

//Script when you press the Submit button
if (isset($_POST['save']) && $_POST['save']=='Submit' && isset($_POST['secret_key']) && isset($_COOKIE['whoisup']) && $_COOKIE['whoisup']==md5($secret_key)){
	if (is_dir(fix_path($_POST['nmap_folder']))){
		$nmap_folder_tmp=fix_path($_POST['nmap_folder']);
	}
	else{
		$nmap_folder_tmp='';
	}

	if (is_dir(fix_path($_POST['script_folder'])) && file_exists(fix_path($_POST['script_folder']).'core.php')){
		$script_folder_tmp=fix_path($_POST['script_folder']);
	}
	else{
		$script_folder_tmp='';
	}
		$config_tmp='<?php
/*
.---------------------------------------------------------------------------.
|  Software: WhoisUP - Is your host up or down?                             |
|  Version: 0.1                                                             |
|  Site: http://code.google.com/p/whoisup/                                  |
| --------------------------------------------------------------------------|
|  Authors: Fabio Semperboni                                                |
|  Blog: http://www.ciscozine.com                                           |
|  Twitter: https://twitter.com/ciscozine                                   |
|  Copyright (c) 2012, Fabio Semperboni                                     |
| ------------------------------------------------------------------------- |
|  License: WhoisUP is released under the GPL                               |
|  This program is free software; you can redistribute it and/or modify     |
|  it under the terms of the GNU General Public License as published by     |
|  the Free Software Foundation; either version 2 of the License, or        |
|  (at your option) any later version.                                      |
|                                                                           |
|  This program is distributed in the hope that it will be useful,          |
|  but WITHOUT ANY WARRANTY; without even the implied warranty of           |
|  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
|  GNU General Public License for more details.                             |
|                                                                           |
|  You should have received a copy of the GNU General Public License        |
|  along with this program; if not, write to the Free Software              |
|  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301  USA |
\'---------------------------------------------------------------------------\'
*/

$secret_key = \''.$_POST['secret_key'].'\';
$threshold = \''.$_POST['threshold'].'\'; //Minutes
$refresh_time = \''.$_POST['refresh_time'].'\'; //Seconds
$cookie_time = \''.$_POST['cookie_time'].'\'; //Hours

$nmap_folder = \''.$nmap_folder_tmp.'\';
$script_folder = \''.$script_folder_tmp.'\';

$timezone_select = \''.$_POST['timezone_select'].'\';

$popup_alert = '.$_POST['popup_alert'].';
$sound_alert = '.$_POST['sound_alert'].';
$syslog_alert = '.$_POST['syslog_alert'].';
$mail_alert = '.$_POST['mail_alert'].';

$sound_file = \''.$_POST['sound_file'].'\';

$syslog_server = \''.$_POST['syslog_server'].'\';
$syslog_port = \''.$_POST['syslog_port'].'\';
$syslog_facility = \''.$_POST['syslog_facility'].'\';
$syslog_severity = \''.$_POST['syslog_severity'].'\';

$mail_server = \''.$_POST['mail_server'].'\';
$mail_port = \''.$_POST['mail_port'].'\';
$mail_auth = '.$_POST['mail_auth'].';
$mail_secure_protocol = \''.$_POST['mail_secure_protocol'].'\';
$mail_username = \''.$_POST['mail_username'].'\';
$mail_password = base64_decode(\''.base64_encode($_POST['mail_password']).'\');
$mail_from = \''.$_POST['mail_from_email'].'\';
$mail_to = \''.str_replace(array("\r\n", "\n", "\r", ";"), ',', $_POST['mail_to_email']).'\';

$host_up_width = \''.$_POST['host_up_width'].'\';
$host_down_width = \''.$_POST['host_down_width'].'\';

//Don\'t change these settings
$file_ip_list = \''.$_POST['file_ip_list'].'\';
$file_tmp_xml = \''.$_POST['file_tmp_xml'].'\';
$file_tmp_html = \''.$_POST['file_tmp_html'].'\';
$sw_version = \''.$_POST['sw_version'].'\';

if (isset($timezone_select) && $timezone_select!=\'\'){
	date_default_timezone_set($timezone_select);
}

?>';

	if ($handle = @opendir('TMP')) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != ".."&& $entry != ".htaccess" && $entry != $_POST['file_tmp_html'] && $entry != $_POST['file_tmp_xml']) {
				if (!preg_match("/$entry/", $_POST['ip_list'])) {
					if (!@unlink('TMP/'.$entry)) {
						exit("You cannot delete TMP/".$entry." file!");
					}
				}
			}
		}
		closedir($handle);
	}

	if (!$handle = @fopen('conf/config.php', "w")){
		exit("Cannot open -w- conf/config.php file");
	}
	if (!@fwrite($handle,$config_tmp)){
		exit("Cannot write conf/config.php file");
	}
	fclose($handle);

	if (!$handle = @fopen('conf/'.$_POST['file_ip_list'], "w")){
		exit("Cannot open -w- conf/".$_POST['file_ip_list']." file");
	}
	if (!@fwrite($handle,htmlentities($_POST['ip_list']))){
		exit("Cannot write conf/".$_POST['file_ip_list']." file");
	}
	fclose($handle);
	require("conf/config.php");
}

require("classes/TimezoneSelector/class.TimezoneSelector.php");

//If login or the cookie is still valid
if ((isset($_POST['secret_key']) && $secret_key==$_POST['secret_key'] && isset($_POST['login']) && $_POST['login']=='Login') || (isset($_COOKIE['whoisup']) && $_COOKIE['whoisup']==md5($secret_key))){
	$login_whoisup=true;
	setcookie('whoisup', md5($secret_key), time() + $cookie_time*3600);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/admin.css" rel="stylesheet" type="text/css" />
<script src="js/jquery-latest.js" type="text/javascript"></script>
<meta name="generator" content="WhoisUP 0.1" />
<link rel="shortcut icon" href="images/favicon.ico" />
<title>WhoisUP - Admin Page</title>

<script type="text/javascript">
//<![CDATA[//><!--

$(document).ready(function(){
						   
	$("#sound_alert_off").click(function(){
		$("#SOUND").hide("slow");
	});
	$("#sound_alert_on").click(function(){
		$("#SOUND").show("slow");
	});
	if ($("#sound_alert_off").attr("checked")) {
		$("#SOUND").hide();
	}
	
	$("#syslog_alert_off").click(function(){
		$("#SYSLOG").hide("slow");
	});
	$("#syslog_alert_on").click(function(){
		$("#SYSLOG").show("slow");
	});
	if ($("#syslog_alert_off").attr("checked")) {
		$("#SYSLOG").hide();
	}
	
	$("#mail_alert_off").click(function(){
		$("#MAIL").hide("slow");
	});
	$("#mail_alert_on").click(function(){
		$("#MAIL").show("slow");
	});
	if ($("#mail_alert_off").attr("checked")) {
		$("#MAIL").hide();
	}
	
	$("#mail_auth_off").click(function(){
		$("#MAIL_AUTH").hide("slow");
	});
	$("#mail_auth_on").click(function(){
		$("#MAIL_AUTH").show("slow");
	});
	if ($("#mail_auth_off").attr("checked")) {
		$("#MAIL_AUTH").hide();
	}
	
	

	$("#secret_key").blur(validateSecretKey);
	$("#threshold").blur(validateThreshold);
	$("#cookie_time").blur(validateCookieTime);
	$("#refresh_time").blur(validateRefreshTime);
	$("#syslog_server").blur(validateSyslogServer);
	$("#syslog_port").blur(validateSyslogPort);
	$("#syslog_facility").blur(validateSyslogFacility);
	$("#syslog_severity").blur(validateSyslogSeverity);
	$("#mail_server").blur(validateMailServer);
	$("#mail_port").blur(validateMailPort);
	$("#mail_username").blur(validateMailUsername);
	$("#mail_password").blur(validateMailPassword);
	$("#mail_from_email").blur(validateMailFromEmail);
	$("#mail_to_email").blur(validateMailToEmail);
	$("#mail_ip_list").blur(validateIpList);
	
	$("#save").click(function(){
		if (validateSecretKey() && validateThreshold() && validateCookieTime() && validateRefreshTime() && validateSyslogServer() && validateSyslogPort() && validateSyslogFacility() && validateSyslogSeverity() && validateMailServer() && validateMailFromEmail() && validateMailToEmail() && validateIpList()){
			return true;
		}
		else{
			return false;
		}
	});
	
	function validateSecretKey(){
		var filter = /^([A-Za-z0-9@#$%]{6,20})$/;
		if(filter.test($("#secret_key").val())){
			$(".error_secret_key").removeClass("error");
			$(".error_secret_key").text("");
			return true;
		}
		else{
			$(".error_secret_key").addClass("error");
			$(".error_secret_key").html("Minimum 6 chars, maximum 20 chars. Special chars permitted: <i>@#$%</i>");
			return false;
		}		
	}
	
	function validateCookieTime(){
		var filter = /^\d+$/;
		if(filter.test($("#cookie_time").val())){
			$(".error_cookie_time").removeClass("error");
			$(".error_cookie_time").text("");
			return true;
		}
		else{
			$(".error_cookie_time").addClass("error");
			$(".error_cookie_time").text("The cookie time must be >= 0");
			return false;
		}
	}
	
	function validateThreshold(){
		var filter = /^\d+$/;
		if(filter.test($("#threshold").val())){
			$(".error_threshold").removeClass("error");
			$(".error_threshold").text("");
			return true;
		}
		else{
			$(".error_threshold").addClass("error");
			$(".error_threshold").text("The threshold value must be >= 0");
			return false;
		}
	}
	
	function validateRefreshTime(){
		var filter = /^\d+$/;
		if(filter.test($("#refresh_time").val())){
			$(".error_refresh_time").removeClass("error");
			$(".error_refresh_time").text("");
			return true;
		}
		else{
			$(".error_refresh_time").addClass("error");
			$(".error_refresh_time").text("The scanning idle time  must be >= 0");
			return false;
		}
	}
	
	function validateSyslogServer(){
		if ($("#syslog_alert_off").attr("checked")) {
			return true;
		}
		if($("#syslog_server").val().length >= 1){
			$(".error_syslog_server").removeClass("error");
			$(".error_syslog_server").text("");
			return true;
		}
		else{
			$(".error_syslog_server").addClass("error");
			$(".error_syslog_server").text("The syslog server field can not be empty");
			return false;
		}
	}
	
	function validateSyslogPort(){
		if ($("#syslog_alert_off").attr("checked")) {
			return true;
		}
		var filter = /^([1-9][0-9]{0,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/;
		if(filter.test($("#syslog_port").val())){
			$(".error_syslog_port").removeClass("error");
			$(".error_syslog_port").text("");
			return true;
		}
		else{
			$(".error_syslog_port").addClass("error");
			$(".error_syslog_port").text("Insert a number between 1 and 65535");
			return false;
		}
	}
	
	function validateSyslogFacility(){
		if ($("#syslog_alert_off").attr("checked")) {
			return true;
		}
		var filter = /^([0-9]|1[0-9]|2[0-3])$/;
		if(filter.test($("#syslog_facility").val())){
			$(".error_syslog_facility").removeClass("error");
			$(".error_syslog_facility").text("");
			return true;
		}
		else{
			$(".error_syslog_facility").addClass("error");
			$(".error_syslog_facility").text("Insert a number between 0 and 23");
			return false;
		}
	}
	
	function validateSyslogSeverity(){
		if ($("#syslog_alert_off").attr("checked")) {
			return true;
		}
		var filter = /^[0-7]$/;
		if(filter.test($("#syslog_severity").val())){
			$(".error_syslog_severity").removeClass("error");
			$(".error_syslog_severity").text("");
			return true;
		}
		else{
			$(".error_syslog_severity").addClass("error");
			$(".error_syslog_severity").text("Insert a number between 0 and 7");
			return false;
		}
	}

	function validateMailServer(){
		if ($("#mail_alert_off").attr("checked")) {
			return true;
		}
		if($("#mail_server").val().length >= 1){
			$(".error_mail_server").removeClass("error");
			$(".error_mail_server").text("");
			return true;
		}
		else{
			$(".error_mail_server").addClass("error");
			$(".error_mail_server").text("The mail server field can not be empty");
			return false;
		}
	}
	
	function validateMailPort(){
		if ($("#mail_alert_off").attr("checked")) {
			return true;
		}
		var filter = /^([1-9][0-9]{0,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/;
		if(filter.test($("#mail_port").val())){
			$(".error_mail_port").removeClass("error");
			$(".error_mail_port").text("");
			return true;
		}
		else{
			$(".error_mail_port").addClass("error");
			$(".error_mail_port").text("Insert a number between 1 and 65535");
			return false;
		}
	}

	function validateMailUsername(){
		if ($("#mail_alert_off").attr("checked") || $("#mail_auth_off").attr("checked")) {
			return true;
		}
		if($("#mail_username").val().length >= 1){
			$(".error_mail_username").removeClass("error");
			$(".error_mail_username").text("");
			return true;
		}
		else{
			$(".error_mail_username").addClass("error");
			$(".error_mail_username").text("The mail username field can not be empty");
			return false;
		}
	}
	
	function validateMailPassword(){
		if ($("#mail_alert_off").attr("checked") || $("#mail_auth_off").attr("checked")) {
			return true;
		}
		if($("#mail_password").val().length >= 1){
			$(".error_mail_password").removeClass("error");
			$(".error_mail_password").text("");
			return true;
		}
		else{
			$(".error_mail_password").addClass("error");
			$(".error_mail_password").text("The mail password field can not be empty");
			return false;
		}
	}

	function validateMailFromEmail(){
		if ($("#mail_alert_off").attr("checked")) {
			return true;
		}
		var filter = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		if(filter.test($("#mail_from_email").val())){
			$(".error_mail_from_email").removeClass("error");
			$(".error_mail_from_email").text("");
			return true;
		}
		else{
			$(".error_mail_from_email").addClass("error");
			$(".error_mail_from_email").text("Insert a valid Mail!");
			return false;
		}
	}
	
	function validateMailToEmail(){
		if ($("#mail_alert_off").attr("checked")) {
			return true;
		}
		if($("#mail_to_email").val().length >= 1){
			$(".error_mail_to_email").removeClass("error");
			$(".error_mail_to_email").text("");
			return true;
		}
		else{
			$(".error_mail_to_email").addClass("error");
			$(".error_mail_to_email").text("The mail to field can not be empty");
			return false;
		}
	}

	function validateIpList(){
		if($("#ip_list").val().length >= 1){
			$(".error_ip_list").removeClass("error");
			$(".error_ip_list").text("");
			return true;
		}
		else{
			$(".error_ip_list").addClass("error");
			$(".error_ip_list").text("The IP list field can not be empty");
			return false;
		}
	}

});
//--><!]]>
</script>

</head>

<body> 

<div id="container">
<form id="admin_form" name="admin_form" method="post" action="">

<div id="header"><span id="title"><img src="images/title.png" alt="WhoisUP"/></span></div>

<?php
if ($login_whoisup){

?>

<div id="box">

<div class="line"><div class="name"><div class="title">Nmap Pat h</div><div class="description">Define your nmap absolute path, for instance:<br /><i>/usr/bin/</i> or <i>c:/Program Files (x86)/Nmap/</i></div></div><div class="value"><input type="text" name="nmap_folder" id="nmap_folder" value="<?php echo $nmap_folder; ?>" class="text" /></div></div>
<div class="line"><div class="name"><div class="title">WhoisUP Path</div><div class="description">Define your script absolute path, for instance:<br /><i>/var/www/html/whoisup/</i> or <i>e:/xampp/htdocs/whoisup/</i></div></div><div class="value"><input type="text" name="script_folder" id="script_folder" value="<?php echo $script_folder; ?>" class="text" /></div></div>
<div class="line"><div class="name"><div class="title">Password **</div><div class="description">Password used to access to the admin panel and to execute the core.php script<div class="error_secret_key"></div></div></div><div class="value"><input type="text" name="secret_key" id="secret_key" value="<?php echo $secret_key; ?>" class="text" /></div></div>
<div class="line"><div class="name"><div class="title">Cookie time</div><div class="description">Number of hours that you can remain logged to the admin page<div class="error_cookie_time"></div></div></div><div class="value"><input type="text" name="cookie_time" id="cookie_time" value="<?php echo $cookie_time; ?>" class="text" /></div></div>
<div class="line"><div class="name"><div class="title">Threshold [m]</div><div class="description">Repeat mail/syslog alert after specified number of minutes. 0 for no delay<div class="error_threshold"></div></div></div><div class="value"><input type="text" name="threshold" id="threshold" value="<?php echo $threshold; ?>" class="text" /></div></div>
<div class="line"><div class="name"><div class="title">Scanning idle time [s] *</div><div class="description">Time to wait (in seconds) between each scan. 0 for continuous scan<div class="error_refresh_time"></div></div></div><div class="value"><input type="text" name="refresh_time" id="refresh_time" value="<?php echo $refresh_time; ?>" class="text" /></div></div>

<div class="line"><div class="name"><div class="title">Number of host up per row *</div><div class="description"></div></div><div class="value"><?php echo view_css_width('host_up_width', $host_up_width); ?></div></div>
<div class="line"><div class="name"><div class="title">Number of host down per row *</div><div class="description"></div></div><div class="value"><?php echo view_css_width('host_down_width', $host_down_width); ?></div></div>

<div class="line"><div class="name"><div class="title">Timeset</div><div class="description">Time to wait (in seconds) between each scan</div></div><div class="value">
<?php $tzs = new TimezoneSelector("timezone_select",$timezone_select,"class=\"text\"");
	$tzs->show(1);
?>
</div></div>


<div class="line"><div class="name"><div class="title">Popup alert <?php echo radio_chk('popup_alert', (string)$popup_alert); ?></div><div class="description">Enable popup alert</div></div><div class="value"></div></div>

<div class="line"><div class="name"><div class="title">Sound alert <?php echo radio_chk('sound_alert', $sound_alert); ?></div><div class="description">Enable sound alert</div></div><div class="value"></div></div>
<div id="SOUND">
<div class="line"><div class="name"><div class="title">Alert sound</div><div class="description"></div></div><div class="value"><?php echo sound_file($sound_file); ?></div>
</div></div>

<div class="line"><div class="name"><div class="title">Syslog alert <?php echo radio_chk('syslog_alert', $syslog_alert); ?></div><div class="description">If you enable syslog notice, remember to define the syslog parameters</div></div><div class="value"></div></div>
<div id="SYSLOG">
<div class="name"><div class="title">Syslog server</div><div class="description"><div class="error_syslog_server"></div></div></div><div class="value"><input type="text" name="syslog_server" id="syslog_server" value="<?php echo $syslog_server; ?>" class="text" /></div>
<div class="name"><div class="title">Syslog port</div><div class="description"><div class="error_syslog_port"></div></div></div><div class="value"><input type="text" name="syslog_port" id="syslog_port" value="<?php echo $syslog_port; ?>" class="text" /></div>
<div class="name"><div class="title">Syslog facility</div><div class="description">If you don't know this parameter, use the default: 14<div class="error_syslog_facility"></div></div></div><div class="value"><input type="text" name="syslog_facility" id="syslog_facility" value="<?php echo $syslog_facility; ?>" class="text" /></div>
<div class="name"><div class="title">Syslog severity</div><div class="description">If you don't know this parameter, use the default: 3<div class="error_syslog_severity"></div></div></div><div class="value"><input type="text" name="syslog_severity" id="syslog_severity" value="<?php echo $syslog_severity; ?>" class="text" /></div>
</div>

<div class="line"><div class="name"><div class="title">Mail alert <?php echo radio_chk('mail_alert', $mail_alert); ?></div><div class="description">If you enable mail notice, remember to define the smtp parameters</div></div><div class="value"></div></div>
<div id="MAIL">
<div class="name"><div class="title">SMTP server</div><div class="description"><div class="error_mail_server"></div></div></div><div class="value"><input type="text" name="mail_server" id="mail_server" value="<?php echo $mail_server; ?>" class="text" /></div>
<div class="name"><div class="title">SMTP port</div><div class="description"><div class="error_mail_port"></div></div></div><div class="value"><input type="text" name="mail_port" id="mail_port" value="<?php echo $mail_port; ?>" class="text" /></div>
<div class="name"><div class="title">SMTP authentication <?php echo radio_chk('mail_auth', $mail_auth); ?></div><div class="description">Enable SMTP authentication to send mail alert</div></div><div class="value"></div>
<div id="MAIL_AUTH">
<div class="name"><div class="title">SMTP secure protocol</div><div class="description">You can choose three type: none, TLS, SSL</div></div><div class="value"><?php echo view_auth_proto($mail_secure_protocol); ?></div>
<div class="name"><div class="title">SMTP username</div><div class="description">Compile this field only if you have enabled SMTP authentication<div class="error_mail_username"></div></div></div><div class="value"><input type="text" name="mail_username" id="mail_username" value="<?php echo $mail_username; ?>" class="text" /></div>
<div class="name"><div class="title">SMTP password</div><div class="description">Compile this field only if you have enabled SMTP authentication<div class="error_mail_password"></div></div></div><div class="value"><input type="text" name="mail_password" id="mail_password" value="<?php echo htmlspecialchars($mail_password); ?>" class="text" /></div>
</div>
<div class="name"><div class="title">Email from</div><div class="description"><div class="error_mail_from_email"></div></div></div><div class="value"><input type="text" name="mail_from_email" id="mail_from_email" value="<?php echo $mail_from; ?>" class="text" /></div>
<div class="name"><div class="title">Email to</div><div class="description">Use this syntax to add more than one email<br /><i>nospam@ciscozine.com<br />nospam@test23.com</i><div class="error_mail_to_email"></div></div></div><div class="value"><textarea name="mail_to_email" id="mail_to_email" class="text" rows="" cols=""><?php echo str_replace(',', "\n", $mail_to); ?></textarea></div>
</div>

<div class="line"><div class="name"><div class="title">IP address List</div><div class="description">Insert your <i>IP address # hostname</i>.<br />For instance<br /><i>192.168.1.1 # my-router<br />8.8.8.8 # google dns</i><div class="error_ip_list"></div></div></div><div class="value"><textarea name="ip_list" id="ip_list" class="text" rows="" cols=""><?php echo read_ip_list($file_ip_list); ?></textarea></div></div>

<input type="hidden" name="file_ip_list" value="<?php echo $file_ip_list; ?>" />
<input type="hidden" name="file_tmp_xml" value="<?php echo $file_tmp_xml; ?>" />
<input type="hidden" name="file_tmp_html" value="<?php echo $file_tmp_html; ?>" />
<input type="hidden" name="sw_version" value="<?php echo $sw_version; ?>" />
<div id="note">* You must reload the index page to apply the changes<br />
** Remember to change the password in the core script</div>
<input type="submit" name="save" class="submit" id="save" value="Submit" />
<input type="submit" name="exit" class="submit" value="Exit" />

</div>

<?php
}
else{

?>

<div id="box">

<?php
	if(isset($_POST['login']) && $_POST['login']=='Login' && isset($_POST['secret_key']) && $secret_key!=$_POST['secret_key']){
		echo '<div id="login-failed">Login failed, check your password!</div>';
	}
?>

<input type="text" name="secret_key" id="secret" value="" />
<input type="submit" name="login" class="submit" value="Login" />
</div>

<?php
}
?>

</form>
<div id="footer"><a href="index.php">Index Page</a> - <a href="http://code.google.com/p/whoisup/" target="_blank">WhoisUP&copy;</a></div>

</div>

</body>
</html>