<?php

//checkreplycount v1.0 by kill0rz - visit kill0rz.com

//Dieses Script prüft, ob die verzeichneten Antworten alles Threads mit den tatsächlichen Antworten übereinstimmen.
//Bei zu wenig verzeichneten Antworten werde(n) der/die letzte(n) Posts in einem Thread angezeigt, bei zu vielen wird eine leere Seite angehangen.

//Das Script in das WBB-Verzeichnis packen und im Browser aufrufen. Nach Ausführung wieder löschen.

//kill0rz, 07.02.2016

//connect to DB:
require './acp/lib/config.inc.php';
require './acp/lib/class_db_mysql.php';

$db = mysqli_connect($sqlhost, $sqluser, $sqlpassword, $sqldb);

$mustbefixed = false;
$hadbeenfixed = false;

if (isset($_GET['fixit']) && trim($_GET['fixit']) == "true") {
	$fixit = true;
} else {
	$fixit = false;
}

$sql = "SELECT threadid, topic, replycount FROM bb1_threads;";
$result = mysqli_query($db, $sql);
while ($row = mysqli_fetch_object($result)) {
	$is_replycount = $row->replycount;

	$sql2 = "SELECT count(threadid) AS replycount FROM bb1_posts WHERE threadid='$row->threadid' GROUP BY (threadid)";
	$result2 = mysqli_query($db, $sql2);
	while ($row2 = mysqli_fetch_object($result2)) {
		$should_replycount = $row2->replycount - 1;
	}

	if ($is_replycount != $should_replycount) {
		if ($fixit) {
			$sql = "UPDATE bb1_threads
					SET replycount = '" . $should_replycount . "'
					WHERE threadid = '" . $row->threadid . "';";
			mysqli_query($db, $sql);
			if (mysqli_error($db) == '') {
				$hadbeenfixed = true;
			}
		} else {
			echo "Thread <i>" . $row->topic . "</i> (" . $row->threadid . ") hat <b>" . $is_replycount . "</b> Antworten; errechnet wurden aber <b>" . $should_replycount . "</b>.<br>\n";
			$mustbefixed = true;
		}
	}
}

if ($mustbefixed) {
	echo '<form action="./checkreplycount.php" method="get" accept-charset="utf-8">';
	echo '<input type="hidden" name="fixit" value="true">';
	echo '<input type="submit" name="Fix this!" value="Fix this!">';
	echo "</form>";
} elseif ($hadbeenfixed) {
	echo "Erfolgreich geupdatet!";
} else {
	echo "Nichts zu tun, alles ok! :)";
}