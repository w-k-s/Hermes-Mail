/*
*Author: Tareq Ateik
*Date: 23-Feb-2013
*/

$(document).ready(function () {
    $('td').click(function(){
        clickable = $(this).attr('clickable');
        if(clickable == 'true')
        {
        	number = $(this).parent().attr('number');
       		window.location = 'message.php?n='+number;
        }
        
    });
    $('#btn_delete').click(function(){
    	numbers = new Array();
    	$(':checked').each(function(){
    		number = $(this).closest('tr').attr('number');
    		numbers.push(number);
    	})
    	deleteList = new Array();
    	deleteList['delete_list'] = numbers.join(',');

    	post("php/delete.php",deleteList);
    });
});