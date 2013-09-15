function viewResource(id) {
	var newDiv = document.createElement('div');
	newDiv.style.position = 'fixed';
	newDiv.style.marginLeft = '30px';
	newDiv.style.marginRight = '30px';
	newDiv.style.marginTop = '30px';
	newDiv.style.marginBottom = '30px';
	newDiv.style.backgroundColor = '#333';
	document.getElementById('content').appendChild(newDiv);
}
