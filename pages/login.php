<?php
$page_title = 'Log in - Scratch Resources';
if ($ms_user['valid']) {
	if (isset($_GET['out'])) {
		session_destroy();
	}
	header('Location: ' . $basepath);
	return;
}
$bad = false;
if (isset($_POST['form_sent'])) {
	$result = $db->query('SELECT id,password_hash FROM users WHERE username=\'' . $db->escape($_POST['username']) . '\' AND (password_hash=\'' . ms_hash($_POST['password']) . '\' OR password_hash=\'reset\') AND status=\'normal\'') or error('Failed to check user', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) {
		list($uid,$pw) = $db->fetch_row($result);
		if ($pw == 'reset') {
			header('Location: ' . $basepath . '/signup?username=' . rawurlencode($_POST['username']));
			return;
		}
		$_SESSION['uid'] = $uid;
		header('Location: ' . $basepath);
	} else {
		$bad = true;
	}
}
?>
<form action="<?php echo $basepath; ?>/login" method="post" enctype="multipart/form-data">
	<h2>Log in</h2>
	<?php
	if ($bad) {
		echo '<p style="color:#F00">Incorrect username or password!</p>';
	}
	?>
	<table border="0">
		<tr>
			<td>Username</td>
			<td><input type="text" name="username" /></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input type="password" name="password" /></td>
		</tr>
	</table>
	<p><input type="submit" name="form_sent" value="Log in" /></p>
</form>