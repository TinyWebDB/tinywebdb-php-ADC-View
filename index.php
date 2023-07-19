<?php
if (isset($_SERVER['REQUEST_URI'])) {
    $request = $_SERVER['REQUEST_URI'];
} else {
    $request = substr($_SERVER['PHP_SELF'], 1);
    if (isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] != '') {
        $request .= '?' . $_SERVER['QUERY_STRING'];
    }
}
if (isset($_POST['action'])) {
    $request = $_POST['action'] . '/';
}

// make Logs and Tags (file) list
$listLog = array();
if ($handler = opendir("_log/")) {
    while (($sub = readdir($handler)) !== FALSE) {
        if (is_file("_log/" . $sub)) { $listLog[] = $sub; }
    }
    closedir($handler);
}
$listTag = array();
if ($handler = opendir("_data/")) {
    while (($sub = readdir($handler)) !== FALSE) {
        if (is_file("_data/" . $sub)) $listTxt[] = $sub;
    }
    closedir($handler);
}

{
    header("HTTP/1.1 200 OK");
    $path = parse_url ($request, PHP_URL_PATH);
    $action = basename( $path );
    switch ($action) {
        case "getvalue": // this action enable from v 0.1.x
            // JSON_API , Post Parameters : tag
            $tagName  = $_REQUEST['tag'];
            $tagValue = '';
	    $tagfName = "_data/" . $tagName . ".txt";
	    if (empty($tagName)) {
		$tagValue = $listTag;
	    } else {
            	is_file($tagfName) && ($tagValue = file_get_contents($tagfName . ".txt"));
	    }
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json');
            echo json_encode(array(
                "VALUE",
                $tagName,
                $tagValue
            ));
            exit;
            break;
        case "storeavalue": // this action will enable from v 0.2.x
            // JSON_API , Post Parameters : tag,value
            $tagName     = $_POST['tag'];
            $tagValue    = $_POST['value'];
            $apiKey      = '';	// $_POST['apikey'];
            $log_message = sprintf("%s:%s\n", date('Y-m-d H:i:s'), "storeavalue: ($apiKey) $tagName -- $tagValue");
            $file_name   = '_log/tinywebdb_' . date('Y-m-d') . '.log';
	    $tagfName = "_data/" . $tagName . ".txt";
            error_log($log_message, 3, $file_name);
            $setting_apikey = '';
            if ($apiKey == $setting_apikey) {
                if(strlen($tagValue) == 0) { 
		    unlink($tagfName);
                    echo "Removed tagName: " . $tagName;
		    exit;
		}
                $fh = fopen($tagfName, "w") or die("check file write permission.");
                fwrite($fh, $tagValue);
                fclose($fh);
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/json');
                echo json_encode(array(
                    "STORED",
                    $tagName,
                    $tagValue
                ));
            } else {
                echo "check api key.";
            }
            exit;
            break;
        default:
            break;
    }
}

include_once("main.html");

if (file_exists('tags.php')) echo "<h3>TinyWebDB <a href=tags.php>Tags</a></h3>";
else echo "<h3>TinyWebDB Tags</h3>";
echo "<table border=1>";
echo "<thead><tr>";
echo "<th> Tag Name </th>";
echo "<th> Size </th>";
echo "</tr></thead>\n";
if ($listTag) {
    sort($listTag);
    foreach ($listTag as $Tag) {
        echo "<tr>";
        echo "<td><a href=getvalue?tag=" . $Tag . ">" . $Tag . "</a></td>\n";
        echo "<td>" . filesize("_data/" . $Tag . ".txt" ) . "</td>\n";
        echo "</tr>";
    }
}
echo "</table>";

echo "<h3>TinyWebDB Log Tail</h3>";
echo "<table border=1>";
echo "<thead><tr>";
echo "<th> Log Name </th>";
echo "<th> Size </th>";
echo "</tr></thead>\n";
if ($listLog) {
    sort($listLog);
    foreach ($listLog as $sub) {
        echo "<tr>";
        echo "<td><a href=?logfile=" . $sub . ">$sub</a></td>\n";
        echo "<td>" . filesize("_log/" . $sub) . "</td>\n";
        echo "</tr>";
    }
}
echo "</table>";

if (isset($_GET['logfile'])) {
    $logfile = substr($_GET['logfile'], 0, 24);
    if (file_exists('draw.php')) echo "<p><img src = 'draw.php?logfile=$logfile'></p>";
    echo "<h2>Log file : " . $logfile . "</h2>";
    $lines = wp_tinywebdb_api_read_tail("_log/" . $logfile, 20);
    foreach ($lines as $line) {
        echo $line . "<br>";
    }
}


exit; // this stops rest steps

function wp_tinywebdb_api_read_tail($file, $lines)
{
    //global $fsize;
    $handle      = fopen($file, "r");
    $linecounter = $lines;
    $pos         = -2;
    $beginning   = false;
    $text        = array();
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if (fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true;
                break;
            }
            $t = fgetc($handle);
            $pos--;
        }
        $linecounter--;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines - $linecounter - 1] = fgets($handle);
        if ($beginning)
            break;
    }
    fclose($handle);
    return array_reverse($text);
}

