var searchField = document.getElementById('searchfield');
searchField.addEventListener("keypress",doSearch,false);

function doSearch(event)
{
	//get search term
	searchTerm = searchField.value.trim();
	if(searchTerm != "")
	{
		//hide messages that dont contain search term
		$('.message:not(:contains("'+searchTerm+'"))').hide();
	}else
	{
		//show all messages
		$('.message').show();
	}
	
}

