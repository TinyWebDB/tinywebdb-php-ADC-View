<h1>TinyWebDB API <a href=index.php>HOME</a> and <a href=tags.php>TAGS</a></h1>
<form method="POST" action="">
<?php
setlocale(LC_TIME, "ja_JP");
date_default_timezone_set('Asia/Tokyo');

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


echo "<h3>TinyWebDB Tags</h3>";
echo "<table border=1>";
echo "<thead><tr>";
echo "<th> </th>";
echo "<th> Tag Name </th>";
echo "<th> Size </th>";
echo "<th> Ver </th>";
echo "<th> localIP </th>";
echo "<th> Gain </th>";
echo "<th> Count </th>";
echo "<th> Data </th>";
echo "<th> battery_Vcc </th>";
echo "<th> localTime </th>";
echo "<th> FileMTime </th>";
echo "</tr></thead>\n";
if ($listTxt) {
    $now = time();
    sort($listTxt);
    foreach ($listTxt as $sub) {
	$tagValue = file_get_contents("_data/" . $sub);
        $obj = json_decode($tagValue);
	$tim_stmp = $obj->{'localTime'} - 9*3600;
        if(($now-$tim_stmp) > 600){
            echo "<tr bgcolor=#AAAAAA>";
        } else {
            echo "<tr>";
        }
        echo "<td> <input type=checkbox name='tagList[]' value=" . substr($sub, 0, -4) . "></td>\n";
        echo "<td><a href=tags.php?tag=" . substr($sub, 0, -4) . ">" .substr($sub, 0, -4) . "</a></td>\n";
        echo "<td>" . filesize("_data/" . $sub) . "</td>\n";
	echo "<td>" . $obj->{'Ver'} . "</td>\n";
        echo "<td>" . $obj->{'localIP'} . "</td>\n";
        echo "<td>" . $obj->{'Gain'} . "</td>\n";
        echo "<td>" . $obj->{'Count'} . "</td>\n";
        if (is_array($obj->{'sersorData'})) echo "<td>" . count($obj->{'sersorData'}) . "</td>\n"; 
	else echo "<td>null</td>";
        echo "<td>" . $obj->{'battery_Vcc'} . "</td>\n";
        echo "<td>" . strftime("%D %T", (int)$tim_stmp) . "</td>\n";
        echo "<td>" . strftime("%D %T", filemtime("_data/" . $sub)) . "</td>\n";
        echo "</tr>";
    }
}
echo "</table>";
echo "<input type=submit value=submit>";
echo "</form>";

if (isset($_GET['tag'])) {
    $tagName = $_GET['tag'];
    echo "<h2>tagName : " . $tagName . "</h2>";
    if (file_exists('drawdata.php')) echo "<p><img src = 'drawdata.php?tagName=$tagName'></p>";
}

if (isset($_POST['tagList'])) {
    $clientMix = array();
    echo "<h3>TinyWebDB Tags</h3>";
    echo "<table border=1>";
    echo "<thead><tr>";
    echo "count = " . count($clientMix) . "<br>";
    foreach($_POST['tagList'] as $tagName) {
	echo "<th> ";
	echo $tagName . "<br>";
	echo "</th>";
	$tagValue = file_get_contents("_data/" . $tagName . ".txt");
    	$obj = json_decode($tagValue);
    	$clientList = $obj->{'clientList'};
	array_merge($clientMix, $clientList);
    echo "count = " . count($clientList, COUNT_RECURSIVE) . "<br>";
    foreach ($clientList as $mac => $rssi ) {
        echo $mac . " -> " . $rssi . "<br>";
    }

	$objList[$tagName] = $obj;
    }
    echo "</tr> ";
    echo "<tr> ";
    foreach($_POST['tagList'] as $tagName) {
        echo "<td>" . $objList[$tagName]->{'localTime'} . "</td>\n";
    }
    echo "</tr></thead>\n";
    echo "</table>";
    foreach ($clientMix as $mac => $rssi ) {
	echo $mac . " -> " . $rssi . "<br>";
    }
}
