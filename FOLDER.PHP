<?PHP
    $matches = array();
	$karri="";
    preg_match_all("/(a href\=\")([^\?\"]*)(\")/i", get_text('http://localhost'), $matches);
    foreach($matches[2] as $match)
    {
echo "<a href='dow.php?download_file=$karri$match'>$match</a>"      
	   . '<br>';
    }

    function get_text($filename)
    {
        $fp_load = fopen("$filename", "rb");
        $content=0;
		if ( $fp_load )
        {
            while ( !feof($fp_load) )
            {
                $content .= fgets($fp_load, 99192);
            }
            fclose($fp_load);
        return $content;
        }
    }
	
	
	

?>