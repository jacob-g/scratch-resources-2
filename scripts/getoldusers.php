<?php
define('SRV_ROOT', '/var/www/scratchres');
// include core files
include SRV_ROOT . '/config/bootstrap.php';
include SRV_ROOT . '/config/pages.php';
include SRV_ROOT . '/drivers/mysqli.php';
include SRV_ROOT . '/includes/global_functions.php';
include SRV_ROOT . '/includes/filter.php';

$db = new databasetool($db_info);
if (!$db->link) {
	die('Failed to connect to database: ' . $db->connect_error());
}

$urls = array('http://resources.scratchr.org/browse/');
$users = array();
foreach ($urls as $url) {
	$data = file_get_contents($url);
	preg_match('%\.\.\. \| <a href="/browse/\d+">(\d+)</a>%', $data, $matches);
	$max = $matches[1];
	echo 'Reading ' . $url . "\n" . ' ';
	for ($i = 1; $i <= $max; $i++) {
		$pct = floor(($i / $max) * 100);
		echo "\r";
		$half = false;
		for ($j = 1; $j <= 50; $j++) {
			if ($j > 22 && $j < 27) {
				if (!$half) {
					echo '[' . sprintf('%2d', $pct) . '%]';
					$half = true;
				}
			} else if ($j < $pct / 2) {
				echo '=';
			} else {
				echo ' ';
			}
		}
		$newdata = file_get_contents($url . '/' . $i);
		preg_match('%<a href="/users/(.*?)">.*?</a>%', $newdata, $matches);
		$username = $matches[1];
		if (!in_array($username, $users)) {
			$users[] = $username;
		}
		sleep((rand(500,2000) / 1000));
	}
	echo "\n";
}
echo 'The following ' . sizeof($users) . ' users are registered on Scratch Resources:' . "\n";
echo implode("\n", $users) . "\n";
echo 'Inserting...' . "\n";
foreach ($users as $val) {
	$db->query('INSERT INTO users(username,password_hash) VALUES(\'' . $db->escape($val) . '\',\'reset\')') or die('FAILED: ' . $db->error());
}