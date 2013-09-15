<?php
$page_title = 'Upload - Scratch Resources';
if (!isset($dirs[2])) {
	$dirs[2] = '';
}
switch ($dirs[2]) {
	case 'script':
		echo '<p>This section is still under construction.</p>'; break;
		?>
		<h2>Upload script</h2>
		<?php
		if (!isset($dirs[3])) {
			$dirs[3] = '';
		}
		switch ($dirs[3]) {
			case 'show':
				include SRV_ROOT . '/includes/blockslib.php';
				$data = file_get_contents('http://scratch.mit.edu/internalapi/backpack/jvvg/get?salt=' . rand(1,100000));
				echo '<link rel="stylesheet" href="' . $basepath . '/static/scratchblocks/scratchblocks2.css" />
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.js"></script> 
				<script type="text/javascript" src="' . $basepath . '/static/scratchblocks/scratchblocks2.js"></script>';
				$backpack = json_to_blocks($data);
				if (!sizeof($backpack)) {
					echo '<p>No procedure definitions were found in your backpack.</p>';
				} else {
					$i = 0;
					foreach ($backpack as $script) {
						if (substr($script, 0, 6) == 'define') {
							echo '<p style="font-weight:bold"><input type="radio" name="script" value="' . $i . '" />This script</p>
							<pre class="blocks">';
							echo $script;
							echo '</pre>';
							$i++;
						}
					}
				}
				echo '<script type="text/javascript">
				scratchblocks2.parse(\'pre.blocks\');
				</script>';
				break;
			default:
			?>
			<p>Please place the script in your backpack. <a href="<?php echo $basepath; ?>/upload/script/show">Continue</a></p>
			<?php
		}
		break;
	case 'sound':
		if (isset($_POST['form_sent'])) {
			$basename = basename($_FILES['file']['name']);
			$ext = pathinfo($basename, PATHINFO_EXTENSION);
			if ($ext != 'mp3' && $ext != 'wav' && $ext != 'mid' && $ext != 'm4a') {
				echo '<p>Invalid file type: ' . $ext . '<br /><a href="' . $basepath . '/upload/sound">Try again</a></p>';
				return;
			}
			if ($_FILES['file']['error']) {
				echo '<p>Something went wrong uploading the file. Not sure what. (Error number ' . $_FILES['file']['error'] . ')</p>';
				return;
			}
			$db->query('INSERT INTO resources(type,filename,uploaded_by,upload_time,extension) VALUES(\'sound\',\'' . $db->escape($basename) . '\',' . $ms_user['id'] . ',' . time() . ',\'' . $ext . '\')') or error('Failed to insert resource', __FILE__, __LINE__, $db->error());
			move_uploaded_file($_FILES['file']['tmp_name'], SRV_ROOT . '/static/resources/' . $db->insert_id() . '.' . $ext);
			echo '<p>Uploaded successfully! <a href="' . $basepath . '/view/' . $db->insert_id() . '">View it</a></p>';
		} else {
			echo '
			<h2>Upload sound</h2>
			<form action="' . $basepath . '/upload/sound" method="post" enctype="multipart/form-data">
				<p>Select a file: <input type="file" name="file" style="height:30px" accept="audio/*" /> <input type="submit" name="form_sent" value="Upload" /></p>
			</form>';
		}
		break;
	case 'sprite':
		if (isset($_POST['form_sent'])) {
			$basename = basename($_FILES['file']['name']);
			$ext = pathinfo($basename, PATHINFO_EXTENSION);
			if ($ext != 'sprite' && $ext != 'sprite2') {
				echo '<p>Invalid file type: ' . $ext . '<br /><a href="' . $basepath . '/upload/sprite">Try again</a></p>';
				return;
			}
			$db->query('INSERT INTO resources(type,filename,uploaded_by,upload_time,extension) VALUES(\'sprite\',\'' . $db->escape($basename) . '\',' . $ms_user['id'] . ',' . time() . ',\'' . $ext . '\')') or error('Failed to insert resource', __FILE__, __LINE__, $db->error());
			move_uploaded_file($_FILES['file']['tmp_name'], SRV_ROOT . '/static/resources/' . $db->insert_id() . '.' . $ext);
			echo '<p>Uploaded successfully! <a href="' . $basepath . '/view/' . $db->insert_id() . '">View it</a></p>';
		} else {
			echo '
			<h2>Upload sprite</h2>
			<form action="' . $basepath . '/upload/sprite" method="post" enctype="multipart/form-data">
				<p>Select a file: <input type="file" name="file" style="height:30px" /> <input type="submit" name="form_sent" value="Upload" /></p>
			</form>';
		}
		break;
	case 'image':
		if (isset($_POST['form_sent'])) {
			$basename = basename($_FILES['file']['name']);
			$ext = pathinfo($basename, PATHINFO_EXTENSION);
			if ($ext != 'jpg' && $ext != 'png' && $ext != 'bmp' && $ext != 'tiff') {
				echo '<p>Invalid file type: ' . $ext . '<br /><a href="' . $basepath . '/upload/image">Try again</a></p>';
				return;
			}
			if ($_FILES['file']['error']) {
				echo '<p>Something went wrong uploading the file. Not sure what. (Error number ' . $_FILES['file']['error'] . ')</p>';
				return;
			}
			$db->query('INSERT INTO resources(type,filename,uploaded_by,upload_time,extension) VALUES(\'image\',\'' . $db->escape($basename) . '\',' . $ms_user['id'] . ',' . time() . ',\'' . $ext . '\')') or error('Failed to insert resource', __FILE__, __LINE__, $db->error());
			move_uploaded_file($_FILES['file']['tmp_name'], SRV_ROOT . '/static/resources/' . $db->insert_id() . '.' . $ext);
			echo '<p>Uploaded successfully! <a href="' . $basepath . '/view/' . $db->insert_id() . '">View it</a></p>';
		} else {
			echo '
			<h2>Upload image</h2>
			<form action="' . $basepath . '/upload/image" method="post" enctype="multipart/form-data">
				<p>Select a file: <input type="file" name="file" style="height:30px" accept="audio/*" /> <input type="submit" name="form_sent" value="Upload" /></p>
			</form>';
		}
		break;
	default:
?>
<h2>Upload</h2>
<p>What do you want to upload?<ul><li><a href="<?php echo $basepath; ?>/upload/sound">Sound</a></li><li><a href="<?php echo $basepath; ?>/upload/sprite">Sprite</a></li><li><a href="<?php echo $basepath; ?>/upload/script">Script/procedure</a></li><li><a href="<?php echo $basepath; ?>/upload/image">Image</a></li></ul></p>
<?php
}