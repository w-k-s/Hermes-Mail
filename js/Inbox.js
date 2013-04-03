/*
*Author: Tareq Ateik
*Date: 23-Feb-2013
*/

$(document).ready(function () {
    $('tr').click(function(){
        number = $(this).attr('number');
        window.location = 'message.php?n='+number;
    });
});