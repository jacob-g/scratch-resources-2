<?php

$pages = array(
	//******************** INDEX *********************//
	
	'/index.php' => array(
		'file'		=> 'index.php', //the file containing the page in /pages
		'header'	=> true, //does this page need a header and footer?
		'permission'=> 0, //what permissions does this page require? (0 for guest, 1 for logged in, 2 for moderator, and 3 for admin
		'prepend'	=> null //what files need to be put before it?
	),

	'/' => array(
		'file'		=> 'index.php',
		'header'	=> true,
		'permission'=> 0,
		'prepend'	=> null
	),
	
	'/login' => array(
		'file'		=> 'login.php',
		'header'		=> true,
		'permission'	=> 0,
		'prepend'		=> null
	),
	
	'/signup' => array(
		'file'		=> 'signup.php',
		'header'		=> true,
		'permission'	=> 0,
		'prepend'		=> null
	),
	
	'/about' => array(
		'file'		=> 'about.php',
		'header'		=> true,
		'permission'	=> 0,
		'prepend'		=> null
	),
	
	//******************** STYLES *********************//

	'/styles/default.css' => array(
		'file'		=> 'css/default.css',
		'header'	=> false,
		'permission'=> 0,
		'prepend'	=> null
	),
);

$pageswithsubdirs = array(

	//******************** USERS *********************//
	'/users' => array(
		'file'		=> 'userviewer.php',
		'header'	=> true,
		'permission'=> 0,
		'prepend'	=> null

	),
	
	'/upload' => array(
		'file'		=> 'upload.php',
		'header'		=> true,
		'permission'	=> 1,
		'prepend'		=> null
	),
	
	'/view' => array(
		'file'		=> 'view.php',
		'header'		=> true,
		'permission'	=> 0,
		'prepend'		=> null
	),
	
	'/settings' => array(
		'file'		=> 'settings.php',
		'header'		=> true,
		'permission'	=> 0,
		'prepend'		=> null
	),
);

foreach ($pages as $key => $val) {
	$pages[$key . '/'] = $val;
}