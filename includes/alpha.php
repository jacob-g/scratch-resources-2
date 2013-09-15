<?php
if (!$ms_user['valid']) {
	if (strpos($url, '/signup') !== 0 && strpos($url, '/login') !== 0 && $dirs[1] != 'styles') {
		header('Location: ' . $basepath . '/signup'); die;
	}
}