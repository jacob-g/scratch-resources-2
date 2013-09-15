<?php
class ResourceItem {
	var $info;
	function __construct($id, $info) {
		$info['id'] = $id;
		$this->info = $info;
	}
	
	function asTR() {
		global $basepath;
		echo '<tr>
		  <td style="border-top:1px solid #000;">
		      <a href="' . $basepath . '/view/' . $this->info['id'] . '" style="font-weight:bold">' . $this->info['title'] . '</a>
		      <br />Uploaded by <a href="' . $basepath . '/users/' . $this->info['uploaded_by'] . '">' . $this->info['uploaded_by'] . '</a> on ' . date('M d Y', $this->info['upload_time']) . '
		      <br /><a href="' . $basepath . '/view/' . $this->info['id'] . '?download">Download</a> <a style="cursor:pointer" onclick="viewResource(' . $this->info['id'] .')">View</a>
		  </td>
	   </tr>';
	}
}