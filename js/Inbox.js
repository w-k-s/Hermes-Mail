/*
*Author: Tareq Ateik
*Date: 23-Feb-2013
*/

$(document).ready(function () {
    $('table tr').click(function () {
        if (link) {
            window.location = "message.html";
            return false;
        }
    });

    $('#delete').click(function () {
        $('#table_inbox tr').each(function () {
            if ($(this).find('input[type=checkbox]').is(':checked')) $(this).remove();
        });
    });

});