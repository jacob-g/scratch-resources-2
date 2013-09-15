<?php
/*
						   ######
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						  #      #
						   ######
						   
						     ##
						    ####
						   ######
						    ####
							##
							 
							 
WARNING: This file is not to be viewed by anyone who isn't mature enough.
This file is the inappropriate word filter (and nothing else). For stuff like the clearHTML function, see global_functions.php
If you are mature enough to see the words we censor and the code used to censor them, go ahead and scroll down. Otherwise, close this. There is nothing else to see.
How the old filter worked is that it replaced the bad words with "[censored]", while the new one will prevent the message from being posted at all (also applies on the forums).







































						
*/
$badwordlist = array('damn*', '*fuck*', '*shit*', 'crap*', 'rape*', 'rapist', 'cunt*', 'ass', 'asshole', '*cialis*', '*viagra*', 'penis*', 'vagina*');
function censor($text) {
	global $badwordlist;
	$pattern = $badwordlist;
	$text = ' ' . $text . ' ';
	foreach ($pattern as &$val) {
		$val = '%(?<=[^\p{L}\p{N}])('.str_replace('\*', '[\p{L}\p{N}]*?', preg_quote($val, '%')).')(?=[^\p{L}\p{N}])%iu';
	}
	$text = preg_replace($pattern, '[censored]', $text);
	$text = substr($text, 1, strlen($text) - 2);
	if (strstr($text, '[censored]')) {
		addlog('Bad word in comment "' . $text . '"');
	}
	return $text;
}
function containsBadWords($text) {
	global $ms_user, $badwordlist, $db;
	define('PUN_DEBUG', 1);
	$pattern = $badwordlist;
	$text = ' ' . $text . ' ';
	foreach ($pattern as &$val) {
		$val = '%(?<=[^\p{L}\p{N}])('.str_replace('\*', '[\p{L}\p{N}]*?', preg_quote($val, '%')).')(?=[^\p{L}\p{N}])%iu';
	}
	foreach ($pattern as $val) {
		if (preg_match($val, $text)) {
			$db->query('INSERT INTO notifications(user,type,message)
			VALUES(' . $ms_user['id'] . ',1,\'' . $db->escape('Your comment, project description, or forum post "' . clearHTML($text, true)) . '" was automatically detected to be inappropriate and was not posted. Please remember that this is a site for all ages, and therefore language appropriate for all ages must be used. If you believe this is a mistake, please appeal this notification.\')') or error('Failed to report bad word', __FILE__, __LINE__, $db->error());
			return true;
		}
	}
	return false;
}