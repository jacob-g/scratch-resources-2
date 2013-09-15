<?php
$page_title = 'Explore - Scratch Resources';
include SRV_ROOT . '/includes/dispitems/resource.php';
?>
<script type="text/javascript" src="<?php echo $basepath; ?>/static/js/viewres.js"></script>
<table border="0">
<?php
$result = $db->query('SELECT r.id,r.filename AS title,r.type,r.upload_time,u.username AS uploaded_by FROM resources AS r LEFT JOIN users AS u ON u.id=r.uploaded_by ORDER BY r.upload_time DESC LIMIT 25') or error('Failed to find recent resources', __FILE__, __LINE__, $db->error());
while ($info = $db->fetch_assoc($result)) {
	$item = new ResourceItem($info['id'], $info);
	$item->asTR();
}
?>
</table>