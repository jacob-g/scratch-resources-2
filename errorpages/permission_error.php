<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Access denied</title>
<style type="text/css">
body {font-family: arial; text-align:center}
</style>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<h1 style="font-style: italic; color: #E00;">Access denied!</h1>
<p>
<?php if (isset($page_info['permission'])) {
	echo 'You need to be ';
	switch ($page_info['permission']) {
	case 1:
		echo 'logged in'; break;
	case 2:
		echo 'a moderator'; break;
	case 3:
		echo 'an administrator'; break;
	}
	echo ' to access this page.';
} else {
	echo '<p>You do not have permission to access this document.</p>';
}
?>
</p>
<p>Would you like to go to your <a href="javascript:history.go(-1)">last visited page</a> or the <a href="<?php echo $basepath; ?>">home page</a>?</p>
</body>
</html>