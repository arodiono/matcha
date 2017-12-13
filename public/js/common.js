$(function() {

    // Limit number of characters in limited textarea
    $('.textarea-limited').keyup(function () {
        var max = $(this).attr('maxlength');
        var len = $(this).val().length;
        if (len >= max) {
            $('#textarea-limited-message').text(' you have reached the limit');
        } else {
            var char = max - len;
            $('#textarea-limited-message').text(char + ' characters left');
        }
    });

});