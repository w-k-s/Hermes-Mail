/*
*Author: Tareq Ateik
*Date: 23-Feb-2013
*/

$(document).ready(function () {

    //in the inbox table
    //if the user clicks anywhere except on the checkbox, 
    //load the message.
    $('td').click(function(){
        clickable = $(this).attr('clickable');
        if(clickable == 'true')
        {
        	number = $(this).parent().attr('number');
       		window.location = 'message.php?n='+number;
        }
    });

    //when the user marks a message for deletion
    //push the number of message into array
    //convert array to comma seperated string
    //pass string to delete php
    $('#btn_delete').click(function(){
    	numbers = new Array();
    	$(':checked').each(function(){
    		number = $(this).closest('tr').attr('number');
    		numbers.push(number);
    	})
    	deleteList = new Array();
    	deleteList['delete_list'] = numbers.join(',');

        if(deleteList=="")
        {
            alert('You haven\'t selected any messages to delete.');
            return;
        }

    	post("delete.php",deleteList);
    });

    $('#notification_panel').click(function(){
        $(this).hide();
    });
});