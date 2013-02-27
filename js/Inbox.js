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

    for (var i = 0; i < 20; i++)
    $('#table_inbox tr:last').after('<tr><td class="td_check"><input onmouseover="link = false;" type="checkbox" /></td><td class="td_sender" onmouseover="link = true;">Sender McSender</td><td class="td_subject" onmouseover="link = true;">I\'ve sent you this email</td><td class="td_date" onmouseover="link = true;">26 Feb 2013 20:03</td></tr>');
});