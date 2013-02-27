var searchField = document.getElementById('searchfield');

function doSearch(event)
{
	if(window.event)
		event = window.event;

	if(event.keyCode == 13)
		alert("searching for "+searchField.value);
}

searchField.addEventListener("keypress",doSearch,false);