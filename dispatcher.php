<?php
/***********************************************************************************************
 *																			*
 *							Scratch Resources 2.0							     *
 *																			*
 ***********************************************************************************************
 
 	Based on The Mod Share Rapid Development Framework 1.0	
	Copyright (C)2012-2013 jvvg and LS97
	All code contained here is subject to the license.
	
	Scratch Resources 2.0 Copyright (C)2013 Jacob G.
	
	MAIN DISPATCHER
	All requests (except ones overridden in .htaccess) go through this file
	To make a new page, go to config/pages.php
	To change database settings or the list of allowed mods, go to config/bootstrap.php
	This prevents unwanted access to system files
	
	This script generates a few variables for use in all of Mod Share's pages:
	 SRV_ROOT			Defines the root PHP directory of public_html
	 MS_DEBUG			Defines a boolean of whether the site is in debug mode
	 MS_EMERGENCY		Defines a boolean of whether the site is in emergency mode
	 $db_info			IDs and passwords for the database login
	 $ms_config			Contains basic key/value configuration
	 $ms_user			Contains authentication info and preferences of the logged in user
	 $modlist			An array containing info about the allowed mods
	 
****************************************************************************/

/*if (!isset($_GET['debug']) && strpos($_SERVER['REMOTE_ADDR'], 'styles') !== false) {
	header('Location: /data/down.html'); die;
}*/

// define the server root to be used in all scripts
define('SRV_ROOT', dirname(__FILE__));

//set PHP settings for sessions and IO
ini_set('magic_quotes_runtime', 0);
ini_set('session.save_path', SRV_ROOT . '/sess');
ini_set('session.save_handler', 'files');
ini_set('session.name', 'MODSHARESESSIONID');
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7);
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 7);

include SRV_ROOT . '/config/bootstrap.php'; //get bootstrap

// start the user session
session_start();

//look for the page and set the page info
$url = str_replace($basepath, '', rawurldecode(strtok($_SERVER['REQUEST_URI'], '?')));
$dirs = explode('/', $url);
$base = dirname($url);

$file = rawurldecode(strtok($_SERVER['REQUEST_URI'], '?'));
$raw_url = false;

//check if the literal file exists
if (in_array($dirs[1], $disallowed_dirs)) {
	header('HTTP/1.1 404 Not found');
	include SRV_ROOT . '/errorpages/404.php';
	die;
}

if (file_exists(SRV_ROOT . $file . 'index.php')) {
	$raw_url = $file . 'index.php';
} else if (file_exists(SRV_ROOT . $file . '/index.php')) {
	header('Location: ' . $file . '/'); die;
} else if (file_exists(SRV_ROOT . $file)) {
	$raw_url = $file;
}
if ($raw_url && $file != '/') {
	$ext = pathinfo($raw_url, PATHINFO_EXTENSION);
	switch ($ext) {
		case 'php':
			header('Content-type: text/html');
			include SRV_ROOT . $raw_url; break;
		case 'css':
			header('Content-type: text/css');
			echo file_get_contents(SRV_ROOT . $raw_url); break;
		case 'png':
			header('Content-type: image/png');
			echo file_get_contents(SRV_ROOT . $raw_url); break;
		case 'gif':
			header('Content-type: image/gif');
			echo file_get_contents(SRV_ROOT . $raw_url); break;
		case 'js':
			header('Content-type: application/javascript');
			echo file_get_contents(SRV_ROOT . $raw_url); break;
		default:
			header('HTTP/1.1 404 Not found');
			include SRV_ROOT . '/errorpages/404.php';
	}
	die;
}

// include core files
include SRV_ROOT . '/config/pages.php';
include SRV_ROOT . '/drivers/mysqli.php';
include SRV_ROOT . '/includes/global_functions.php';
include SRV_ROOT . '/includes/filter.php';

if (defined('MS_EMERGENCY') && MS_EMERGENCY) {
	error('The site is currently down due to a system emergency.');
}

//initialise the database using $db_info
$db = new databasetool($db_info);
if (!$db->link) {
	error('Failed to connect to database', __FILE__, __LINE__, $db->connect_error());
}

//get site config from database (or cache)
if (!file_exists(SRV_ROOT . '/cache/cache_config.php')) {
	$ms_config = array();
	$result = $db->query('SELECT c_name,c_value FROM config') or error('Failed to get config', __FILE__, __LINE__, $db->error());
	while (list($key,$val) = $db->fetch_row($result)) {
		$ms_config[$key] = $val;
	}
	$data = '<?php' . "\n" . '$ms_config = ';
	$data .= var_export($ms_config, true);
	$data .= ';';
	file_put_contents(SRV_ROOT . '/cache/cache_config.php', $data);
} else {
	@include SRV_ROOT . '/cache/cache_config.php';
	if (!isset($ms_config)) {
		header('Refresh: 0'); die;
	}
}

//fix $_POST if necessary (remove the backslashes)
function stripslashes_array($array) {
	foreach ($array as &$val) {
		if (is_array($val)) {
			$val = stripslashes_array($val);
		} else {
			$val = stripslashes($val);
		}
	}
	return $array;
}
if (ini_get('magic_quotes_gpc')) {
	$_POST = stripslashes_array($_POST);
	$_GET = stripslashes_array($_GET);
	$_COOKIE = stripslashes_array($_COOKIE);
	$_REQUEST = stripslashes_array($_REQUEST);
}

// get user info from database
$ms_user = array();
check_user($ms_user);

include SRV_ROOT . '/includes/alpha.php';

//maintenance?
if ($ms_config['status'] == 'maint' && time() > $ms_config['turn_off_maint']) {
	set_config('status', 'normal');
}
if ($ms_config['status'] == 'maint' && $_SERVER['REQUEST_URI'] != '/styles/default.css' && !$ms_user['is_admin'] && $_SERVER['REQUEST_URI'] != '/login') {
	include SRV_ROOT . '/pages/maintenance.php';
	die;
}

//check for bans
/*$result = $db->query('SELECT type FROM bans
WHERE (user_id=' . ($ms_user['valid'] ? $ms_user['id'] : -1) . '
OR ip=\'' . $_SERVER['REMOTE_ADDR'] . '\'
OR ip LIKE \'%,' . $_SERVER['REMOTE_ADDR'] . '\'
OR ip LIKE \'%,' . $_SERVER['REMOTE_ADDR'] . ',%\'
OR ip LIKE \'' . $_SERVER['REMOTE_ADDR'] . ',%\')
AND expires>' . time()) or error('Failed to check bans', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result) && !$ms_user['is_admin']) {
	$ms_user['banned'] = true;
	$_SESSION['banned'] = true;
	$ban_info = $db->fetch_assoc($result);
	if ($ban_info['type'] == 'login') {
		if (strpos($url, '/login') === 0 || strpos($url, '/register') === 0) {
			header('Location: /banned');
			echo 'You are banned from Mod Share.';
			die;
		}
	} else {
		if ($url != '/banned' && strpos($url, '/styles') !== 0 && $url != '/logout' && $url != '/login' && $url != '/notifications' && $url != '/terms' && ($url != '/help' || !$ms_user['valid'])) {
			if (strstr($_SERVER['HTTP_USER_AGENT'], 'Squeak')) {
				echo 'banned'; die;
			}
			header('Location: /banned');
			echo 'You are banned from Mod Share.';
			die;
		}
	}
}
if (isset($_SESSION['banned']) && !$ms_user['banned']) {
	unset($_SESSION['banned']);
}*/

//future code to bust users trying to get around bans

//check if the page exists
$ok = false;
$permission_error = false;
if (array_key_exists($url, $pages)) {
	$ok = true;
	$page_info = $pages[$url];
	if ($page_info['permission'] > $ms_user['permission']) {
		$ok = false;
		$permission_error = true;
	}
} else {
	foreach ($pageswithsubdirs as $key => $val) {
		if (strpos($url, $key) === 0) {
			$ok = true;
			$page_info = $val;
			break;
		}
	}
	if (isset($page_info['permission']) && $page_info['permission'] > $ms_user['permission']) {
		$ok = false;
		$permission_error = true;
	}
}

// if page found, render the page
if ($ok) {
	// start the output buffer
	ob_start();
	
	// output the header
	if ($page_info['header'])
		include SRV_ROOT . '/includes/header.php';
		
	//include a prepend
	$continue = true;
	if ($page_info['prepend']) {
		include SRV_ROOT . $page_info['prepend'];
	}
	
	if ($continue) {
		// output page contents
		if(file_exists(SRV_ROOT . '/pages/' . $page_info['file'])) {
			include SRV_ROOT . '/pages/' . $page_info['file'];
		} else {
			echo '<h2>Uh oh...</h2><p>This page appears to be misconfigured on the server.</p>';
		}
	}
	
	// output the footer
	if ($page_info['header'])
		include SRV_ROOT . '/includes/footer.php';
	
	// dump the buffer into a string for manipulation
	$contents = ob_get_contents();
	ob_end_clean();
	
	// set the appropriate content type
	if (isset($content_type) && $content_type) {
		header('Content-type: ' . $content_type);
	} else {
		header('Content-type: application/xhtml+xml; charset=utf-8');
	}
	
	// modify the page title according to the variable, if present
	if ($page_info['header']) {
		if(!isset($page_title))
			$page_title = 'Scratch Resources';
		$contents = str_replace('<$page_title/>', $page_title, $contents);
	}
	
	// output the page
	echo $contents;
} else {
	// if not found, echo a 404 page
	if ($permission_error) {
		header('HTTP/1.1 403 Forbidden');
		include SRV_ROOT . '/errorpages/permission_error.php';
	} else {
		header('HTTP/1.1 404 Not found');
		include SRV_ROOT . '/errorpages/404.php';
	}
}

$db->close();