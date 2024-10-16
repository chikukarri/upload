<?PHP

$start = "D:";
$handle = opendir($start);

# Making an array containing the files in the current directory:
while ($file = readdir($handle))
{
    $files[] = $file;
}
closedir($handle);

#echo the files
foreach ($files as $file) {
echo $file;
    echo "<a href='dow.php?download_file=$file'>$file</a>"."<br />";
}
?> 