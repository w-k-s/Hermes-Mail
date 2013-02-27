var deleteButton = document.getElementById('btn_delete');

function deleteMail(){
	if(confirm('Are you sure you want to delete this message?'))
		alert('Message deleted!');
}


deleteButton.addEventListener('click',deleteMail,false);