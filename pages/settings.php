<?php
$page_title = 'Account settings - Scratch Resources';
if (!isset($dirs[2])) {
	$dirs[2] = '';
}
if (isset($_POST['form_sent'])) {
    if (isset($_POST['timezone'])) {
        $db->query('UPDATE users SET timezone=' . intval($_POST['timezone']) . ' WHERE id=' . $ms_user['id']) or error('Failed to update user info', __FILE__, __LINE__, $db->error());
    } else if (isset($_POST['curpwd'])) {
        if ($ms_user['password_hash'] == ms_hash($_POST['curpwd'])) {
            if ($_POST['pwd1'] == $_POST['pwd2']) {
                $db->query('UPDATE users SET password_hash=\'' . $db->escape(ms_hash($_POST['pwd1'])) . '\' WHERE id=' . $ms_user['id']) or error('Failed to update password', __FILE__, __LINE__, $db->error());
                header('Location: ' . $basepath . '/settings?success');
                return;
            } else {
                header('Location: ' . $basepath . '/settings?pwdnomatch');
                return;
            }
        } else {
            header('Location: ' . $basepath . '/settings?badoldpwd');
            return;
        }
    }
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}
?>
<div class="cols">
	<div class="col-4">
		<div class="box v-tabs">
			<div class="tabs-index"> 
				<ul>
					<li class="first<?php if ($dirs[2] == '') echo ' active'; ?>"><a href="<?php echo $basepath; ?>/settings">Change password </a></li>
					<li class="<?php if ($dirs[2] == 'email') echo ' active'; ?>"><a href="<?php echo $basepath; ?>/settings/email">Change email</a></li>
					<li class="<?php if ($dirs[2] == 'info') echo ' active'; ?>"><a href="<?php echo $basepath; ?>/settings/info">Change info</a></li>
					<li class="last<?php if ($dirs[2] == 'delete') echo ' active'; ?>"><a href="<?php echo $basepath; ?>/settings/delete" style="color:#F00">Delete account</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-12">
		<div class="box">
			<div class="box-head">
				<h2>Account Settings</h2>
			</div>
			<div class="box-content tabs-content" id="main-content" style="min-height:300px">
				<form id="password_change" action="<?php echo $basepath; ?>/settings" method="post" enctype="multipart/form-data" style="padding:5px">
					<table border="0">
						<?php
						switch ($dirs[2]) {
							case '':
                                if (isset($_GET['pwdnomatch'])) {
                                    echo '<tr><td>Error</td><td>Your passwords did not match.</td></tr>';
                                } else if (isset($_GET['badoldpwd'])) {
                                    echo '<tr><td>Error</td><td>Your old password was not correct.</td></tr>';
                                }
								?>
								<tr>
									<td>Current password</td>
									<td><input type="password" name="curpwd" /></td>
								</tr>
								<tr>
									<td>New password</td>
									<td><input type="password" name="pwd1" /></td>
								</tr>
								<tr>
									<td>Confirm password</td>
									<td><input type="password" name="pwd2" /></td>
								</tr>
								<?php
								break;
							case 'email':
								?>
								<tr>
									<td style="padding-right:5px">Current email</td>
									<td>Email addresses are not currently supported by this software architecture</td>
								</tr>
								<tr>
									<td>New email</td>
									<td><input type="text" name="email" /></td>
								</tr>
								<?php
								break;
							case 'info':
								?>
								<tr>
									<td>Time zone</td>
									<td><select name="timezone"><?php
									for ($i = -12; $i <= 12; $i++) {
									    echo '<option value="' . $i . '"';
									    if ($i == $ms_user['timezone']) {
									        echo ' selected="selected"';
									    }
									    echo '>GMT';
                                        if ($i >= 0) {
                                            echo '+';
                                        }
                                        echo $i . '</option>';
									} ?></select></td>
								</tr>
								<?php
								break;
							case 'delete':
								?>
								<p>Deleting your account is a major decision. Please think for a bit before doing so.</p>
								<p id="deletecountdown" style="display:none">You may continue in <span style="font-weight:bold" id="countdownnumber">30</span> seconds.</p>
								<p id="deletestuff">Enter password: <input type="pwd" name="pwd" /><br /><br /><input type="radio" name="howtodelete" value="close" id="closeacc" checked="checked" /><label for="closeacc" style="display:inline">Close account: disable logins, but keep all uploads</label><br /><input type="radio" name="howtodelete" value="delete" id="deleteacc" /><label for="deleteacc" style="display:inline">Delete account: remove everything related to this account</label><br /><input type="submit" name="delete_form_sent" style="background-color:#C00;border-radius:5px" value="Delete account" /></p>
								<script type="text/javascript">
								document.getElementById('deletestuff').style.display = 'none';
								document.getElementById('deletecountdown').style.display = 'block';
								var timeleft = 30;
								setTimeout(countdown, 1000);
								function countdown() {
									timeleft--;
									document.getElementById('countdownnumber').innerHTML = timeleft;
									setTimeout(countdown, 1000);
									if (timeleft == 0) {
										document.getElementById('deletecountdown').style.display = 'none';
										document.getElementById('deletestuff').style.display = 'block';
									}
								}
								</script>
								<?php
								break;
						}
						?>
					</table>
					<?php
					if ($dirs[2] != 'delete') {
					    echo '<p><input type="submit" name="form_sent" value="Submit" /></p>';
					}
                    ?>
				</form>
			</div>
		</div>
	</div>
</div>