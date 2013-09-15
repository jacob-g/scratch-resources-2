<?php
if (!isset($dirs[2])) {
	header('HTTP/1.1 404 Not found');
	include SRV_ROOT . '/errorpages/404.php';
	die;
}
if ($dirs[2] == 'latest') {
	include SRV_ROOT . '/pages/explore.php';
	return;
}
$id = intval($dirs[2]);
$result = $db->query('SELECT r.type,r.type,r.filename,r.upload_time,r.extension,u.username AS uploaded_by FROM resources AS r LEFT JOIN users AS u ON u.id=r.uploaded_by WHERE r.id=' . $id) or error('Failed to find resource', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result)) {
	header('HTTP/1.1 404 Not found');
	include SRV_ROOT . '/errorpages/404.php';
	die;
}
$cur_resource = $db->fetch_assoc($result);
$page_title = htmlspecialchars($cur_resource['filename']) . ' - Scratch Resources';
if (isset($_GET['download'])) {
	ob_end_clean();
	header('Content-Disposition: attachment;filename="' . $cur_resource['filename']);
	echo file_get_contents(SRV_ROOT . '/static/resources/' . $id . '.' . $cur_resource['extension']);
	die;
}
//handle comment requests
if (isset($_POST['newcomment']) && $ms_user['valid']) {
    ob_end_clean();
    if ($_POST['newcomment'] == '') {
        header('HTTP/1.1 500');
        echo 'Blank comments are not allowed';
        die;
    }
    $db->query('INSERT INTO comments(resource,post_time,author,content) VALUES(' . $id . ',' . time() . ',' . $ms_user['id'] . ',\'' . $db->escape($_POST['newcomment']) . '\')') or error('Failed to insert comment', __FILE__, __LINE__, $db->error());
    echo $db->insert_id() . '^' . $ms_user['username'] . '^' . user_date(time()) . "\n" . parse_comment($_POST['newcomment']);
    die;
}
if (isset($_POST['reply']) && isset($_POST['origid']) && $ms_user['valid']) {
    ob_end_clean();
    if ($_POST['reply'] == '') {
        header('HTTP/1.1 500');
        echo 'Blank comments are not allowed';
        die;
    }
    $db->query('INSERT INTO comments(resource,post_time,author,content,parent) VALUES(' . $id . ',' . time() . ',' . $ms_user['id'] . ',\'' . $db->escape($_POST['reply']) . '\',' . $_POST['origid'] . ')') or error('Failed to insert comment', __FILE__, __LINE__, $db->error());
    echo $db->insert_id() . '^' . $ms_user['username'] . '^' . user_date(time()) . "\n" . parse_comment($_POST['reply']);
    die;
}
?>
<h2>Viewing resource: <?php echo htmlspecialchars($cur_resource['filename']); ?></h2>
<h3>By <?php echo $cur_resource['uploaded_by']; ?></h3>
<p style="font-size:20px"><a href="?download">Download</a></p>
<p><?php
switch ($cur_resource['type']) {
	case 'image':
		echo '<img src="' . $basepath . '/static/resources/' . $id . '.' . $cur_resource['extension'] . '" />';
		break;
	case 'sound':
		echo '<audio controls="controls"><source src="' . $basepath . '/static/resources/' . $id . '.' . $cur_resource['extension'] . '" />Your browser does not support HTML5. You may still download thi sresource but are not able to play it in your browser.</audio>';
}
?></p>
<h3>Comments</h3>
<script type="text/javascript">
//<![CDATA[
    var firstcomment;
    function submitComment(sender) {
        sender.disabled = true;
        var req;
        if (window.XMLHttpRequest) {
            req = new XMLHttpRequest();
        } else {
            req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        req.open('POST', '<?php echo $basepath; ?>/view/<?php echo $id; ?>', true);
        req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        req.send('newcomment=' + encodeURIComponent(document.getElementById('comment').value));
        
        req.onreadystatechange = function() {
            if (req.readyState==4) {
                sender.disabled = false;
                if (req.status == 200) {
                    //display the new comment!
                    var respparts = req.responseText.split("\n", 2);
                    var headparts = respparts[0].split('^');
                    //alert(req.responseText);
                                        
                    var newH4 = document.createElement('h4');
                    newH4.innerHTML = headparts[1] + ' at ' + headparts[2];
                    
                    var newP = document.createElement('p');
                    newP.innerHTML = respparts[1];
                    
                    document.getElementById('comments').insertBefore(newP, firstcomment);
                    document.getElementById('comments').insertBefore(newH4, newP);
                    
                    firstcomment = newH4;
                    
                    document.getElementById('comment').value = '';
                } else {
                    alert('Error (' + req.status + ') - comment not posted');
                    if (req.status == 500) {
                        alert(req.responseText);
                    }
                }
            }
        }
    }
    var replyBoxes = [];
    function replyToComment(id) {
        if (replyBoxes.indexOf(id) == -1) {
            var newDiv = document.createElement('div');
            newDiv.style.textIndent = '10px';
            newDiv.id = 'replyDiv_' + id;
            
            var newP = document.createElement('p');
            
            var newTextArea = document.createElement('textarea');
            newTextArea.id = 'reply_' + id;
            newP.appendChild(newTextArea);
            
            var newBr = document.createElement('br');
            newP.appendChild(newBr);
            
            var newSubmit = document.createElement('input');
            newSubmit.type = 'submit';
            newSubmit.value = 'Post';
            newSubmit.id = 's_' + id;
            newSubmit.onclick = function(e,self) {
                var id = this.id.replace('s_', '');
                submitReply(id);
            };
            newSubmit.style.left = '10px';
            newP.appendChild(newSubmit);
            
            newDiv.appendChild(newP);
            
            document.getElementById('comment_' + id + '_replies').appendChild(newDiv);
            replyBoxes.push(id);
        }
    }
    
    function submitReply(id) {
        document.getElementById('replyDiv_' + id).style.display = 'none';
        
        var req;
        if (window.XMLHttpRequest) {
            req = new XMLHttpRequest();
        } else {
            req = new ActiveXObject("Microsoft.XMLHTTP");
        }
        req.open('POST', '<?php echo $basepath; ?>/view/<?php echo $id; ?>', true);
        req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        req.send('reply=' + encodeURIComponent(document.getElementById('reply_' + id).value) + '&origid=' + id);
        
        req.onreadystatechange = function() {
            if (req.readyState==4) {
                if (req.status == 200) {
                    //display the new comment!
                    var respparts = req.responseText.split("\n", 2);
                    var headparts = respparts[0].split('^');
                    //alert(req.responseText);
                                        
                    var newH4 = document.createElement('h4');
                    newH4.innerHTML = headparts[1] + ' at ' + headparts[2];
                    newH4.style.textIndent = '10px';
                    
                    var newP = document.createElement('p');
                    newP.innerHTML = respparts[1];
                    newP.style.textIndent = '10px';
                    
                    document.getElementById('comment_' + id + '_replies').appendChild(newH4);
                    document.getElementById('comment_' + id + '_replies').appendChild(newP);
                    document.getElementById('comment_' + id + '_replies').removeChild(document.getElementById('replyDiv_' + id));
                    
                    alert(id);
                } else {
                    alert('Error (' + req.status + ') - comment not posted');
                    if (req.status == 500) {
                        alert(req.responseText);
                    }
                }
            }
        }
    }
//]]>
</script>
<?php
if ($ms_user['valid']) { ?>
<p><b>Add a comment</b><br /><textarea id="comment" rows="3" cols="50"></textarea><br /><input type="submit" value="Add" onclick="submitComment(this);" /></p>
<?php } ?>
<div id="comments">
<?php
$comments = array();
$result = $db->query('SELECT c.id,c.post_time,c.content,c.parent,u.username AS author FROM comments AS c LEFT JOIN users AS u ON u.id=c.author ORDER BY c.post_time ASC') or error('Failed to get comments', __FILE__, __LINE__, $db->error());
while ($cur_comment = $db->fetch_assoc($result)) {
    if ($cur_comment['parent'] == null) {
        $comments[$cur_comment['id']][0] = $cur_comment;
    } else {
        $comments[$cur_comment['parent']][] = $cur_comment;
    }
}
ksort($comments);
$comments = array_reverse($comments);
$first = true;
foreach ($comments as $val) {
    echo '<h4';
    if ($first) {
        $first = false;
        echo ' id="firstcomment"';
    }
    echo '>' . $val[0]['author'] . ' at ' . user_date($val[0]['post_time']) . '</h4><p>' . $val[0]['content'] . '<br /><span style="font-size:10px"><a onclick="replyToComment(' . $val[0]['id'] . ');" style="cursor:pointer">Reply</a> / Report</span></p><div id="comment_' . $val[0]['id'] . '_replies">';
    foreach ($val as $key2 => $val2) {
        if ($key2 != 0) {
            echo '<h4 style="text-indent:10px">' . $val2['author'] . ' at ' . user_date($val2['post_time']) .  '</h4><p style="text-indent:10px">' . $val2['content'] . '</p>';
        }
    }
    echo '</div>';
}
if ($first) {
    echo '<div id="firstcomment"></div>';
}
?>
</div>
<script type="text/javascript">
    firstcomment = document.getElementById('firstcomment');
</script>
<?php
/*
$orig_comments = array();
//first pass: get everything into array
while ($cur_comment = $db->fetch_assoc($result)) {
    $orig_comments[$cur_comment['id']] = $cur_comment;
}
$comments = $orig_comments;
$comment_levels = array();
//second pass: show relationship
$level = 0;
while (sizeof($orig_comments)) {
    foreach ($orig_comments as $key => $val) {
        if ($level == 0) {
            if ($val['parent'] == null) {
                unset($orig_comments[$key]);
                $comment_levels[$key] = 0;
            }
        } else {
            if (isset($comment_levels[$val['parent']]) && $comment_levels[$val['parent']] < $level) {
                unset($orig_comments[$key]);
                $comment_levels[$key] = $level;
            }
        }
    }
    $level++;
    if ($level > 20) {
        break;
    }
}
foreach ($comments as $key => $val) {
    if ($val['parent'] == null) {
        dispcomment($key, 0);
    }
}
//third pass: display everything according to level
function dispcomment($id, $level) {
    global $comment_levels,$comments;
    $out = $comments[$id]['content'];
    foreach ($comment_levels as $val) {
        
    }
    echo $out;
}
?>
</form>
*/