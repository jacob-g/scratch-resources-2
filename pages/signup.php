<?php
$page_title = 'Sign up - Scratch Resources';
//based on the Mod Share signup code
function form() {
	global $basepath;
	?>
	<form action="<?php echo $basepath; ?>/signup" method="post" enctype="multipart/form-data">
		<table border="0">
			<tr>
				<td>Username</td>
				<td><?php echo htmlspecialchars($_SESSION['username']); ?></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type="password" name="password" /></td>
			</tr>
			<tr>
				<td style="padding-right:5px">Confirm password</td>
				<td><input type="password" name="password2" /></td>
			</tr>
		</table>
		<p><input type="submit" name="form_sent" value="Finish registration" /></p>
	</form>
	<?php
}
echo '<h2>Sign up</h2>';
$project_id = '10135908/';
$project_url = 'http://scratch.mit.edu/projects/' . $project_id;
$api_url = 'http://scratch.mit.edu/site-api/comments/project/' . $project_id . '?page=1&salt=' . md5(time()); //salt is to prevent caching
if (isset($_POST['form_sent']) && isset($_SESSION['verified']) && isset($_SESSION['username'])) {
	if ($_POST['password'] != $_POST['password2']) {
		echo '<p style="color:#F00">Passwords do not match!</p>';
		form();
		return;
	}
	//let the fun begin!
	$result = $db->query('SELECT id FROM users WHERE username=\'' . $db->escape($_SESSION['username']) . '\' AND password_hash=\'reset\'') or error('Failed to check if user already exists', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) {
		list($_SESSION['uid']) = $db->fetch_row($result);
		$db->query('UPDATE users SET password_hash=\'' . $db->escape(ms_hash($_POST['password'])) . '\' WHERE username=\'' . $db->escape($_SESSION['username']) . '\'') or error('Failed to update password', __FILE__, __LINE__, $db->error());
	} else {
		$db->query('INSERT INTO users(username,password_hash,registered,registration_ip) VALUES(\'' . $db->escape($_SESSION['username']) . '\',\'' . ms_hash($_POST['password']) . '\',' . time() . ',\'' . $db->escape($_SERVER['REMOTE_ADDR']) . '\')') or error('Failed to insert user', __FILE__, __LINE__, $db->error());
		$_SESSION['uid'] = $db->insert_id();
	}
	echo '<p>Registration is complete! Click <a href="' . $basepath . '">here</a> to log in.</p>';
} else if (isset($_POST['verifycodesent'])) {
	$data = file_get_contents($api_url);
	if (!$data) {
		echo '<p>API access failed. Please try again later.</p>';
		return;
	}
	$success = false;
	preg_match_all('%<div id="comments-\d+" class="comment.*?" data-comment-id="\d+">.*?<a href="/users/(.*?)">.*?<div class="content">(.*?)</div>%ms', $data, $matches);
	foreach ($matches[2] as $key => $val) {
		$user = $matches[1][$key];
		$comment = trim($val);
		if ($user == $_SESSION['username'] && $comment == $_SESSION['verifycode']) {
			$success = true;
			$_SESSION['verified'] = true;
			form();
			break;
		}
	}
	if (!$success) {
		echo '<p>Verification failed. It does not appear you commented the code on the project. Note that you must comment the code <i>exactly</i> as it appears, with nothing extra.</p><form action="' . $basepath . '/signup" method="post" enctype="multipart/form-data"><p><input type="submit" name="verifycodesent" value="Try again" /><br /><a href="' . $basepath . '/signup">Try again with a different user</a></p></form>';
	}
} else if (isset($_POST['username'])) {
	$result = $db->query('SELECT status,password_hash FROM users WHERE username=\'' . $db->escape($_POST['username']) . '\'') or error('Failed to check if user already exists', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) {
		list($status,$pass) = $db->fetch_row($result);
		if ($pass != 'reset') {
			echo '<p>That user is already registered.</p>';
			return;
		}
		if ($status != 'normal') {
			echo '<p style="color:#F00">' . htmlspecialchars('Sorry, you aren\'t allowed to register on this site yet.') . '</p>';
			return;
		}
	} else {
        echo '<p style="color:#F00">' . htmlspecialchars('Sorry, you aren\'t allowed to register on this site yet.') . '</p>';
        return;
    }
	$_SESSION['verifycode'] = 'SR' . sha1(rand(1,1000) . $_POST['username']);
	$_SESSION['username'] = $_POST['username'];
	echo '<p>To verify your identity, please comment the following code on the <a href="' . $project_url . '" target="_BLANK">user verification project</a><br /><b>' . $_SESSION['verifycode'] . '</b></p>';
	?>
	<form action="<?php echo $basepath; ?>/signup" method="post" enctype="multipart/form-data">
		<p><input type="submit" name="verifycodesent" value="I have commented the code, continue" /></p>
	</form>
	<?php
} else {
	?>
	<form action="<?php echo $basepath; ?>/signup" method="post" enctype="multipart/form-data">
		<p>Username: <input type="text" name="username"<?php if (isset($_GET['username'])) echo ' value="' . htmlspecialchars($_GET['username']) . '"'; ?> /> <input type="submit" value="Continue" /></p>
	</form>
	<?php
}
?>