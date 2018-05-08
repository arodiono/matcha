
$(function () {

    $( ":input" ).each(function () {
        if($(this).val()) {
            $(this).parent().removeClass('is-empty')
        }
    })

	function toggleLoader() {
		$(document.body).append('<div class="loader-fade">\n' +
            '        <div class="loader">\n' +
            '            <div class="circle"></div>\n' +
            '            <div class="circle"></div>\n' +
            '            <div class="circle"></div>\n' +
            '        </div>\n' +
            '    </div>')
		$('.loader-fade').animate({opacity: 0.875}, 500)
    }

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

    $(".chat-body").niceScroll();

    $(document).ready(function () {
		$(".label-floating").find('input').each(function (index, value ) {
			if (value.value) {
				$(this).parent().removeClass('is-empty')
			}
    	})
    });

	$(document).ready(function () {
        $( 'input:file[name="files"]' ).change(function() {
        	$('input:hidden').val($(this)[0].files[0].name)
			$('[name="update-profile-photo"]').submit()
        });
    })

    $('[data-time]').each(function () {
		$(this).html(moment($(this).data().time, "YYYY-MM-DD HH:mm:ss").fromNow())
    })

    var conn = new WebSocket('ws://' + window.location.hostname + ':8000');
    conn.onopen = function (e) {
    	// console.log(conn)
        conn.send(JSON.stringify({
            auth: $('[data-user]').data().user,
            type: $('[data-connection]').data() == null ? 'ntf' : $('[data-connection]').data().connection,
            host: window.location.protocol + '//' + window.location.host + '/user/online'
        }));
    };
    conn.onmessage = function (e) {
    	console.log(e.data)
        addNewIncomeMessage(e.data);
        $.post(window.location.href + '/smhbr',
            {
                user: $('[data-user]').data().user
            });
    };
    $('.message-field').submit(function (e) {
        e.preventDefault();
        addNewOutcomeMessage(document.getElementById('new-message').value);
        document.getElementById('new-message').value = '';
    });

    function addNewIncomeMessage(message) {
        var newMessage = $('.left').first().clone().removeClass('hide');
        newMessage.find('.text').html(JSON.parse(message).text);
        newMessage.find('.time').html(moment().fromNow());
        $('.chat-body').append(newMessage)
        scrollChat()
    }

    function addNewOutcomeMessage(message) {
        var newMessage = $('.right').first().clone().removeClass('hide');
        var url = window.location.href;
        var receiver = url.split('/')[url.split('/').length - 1];
        conn.send(JSON.stringify({to: receiver, msg: message}));
        newMessage.find('.text').html(message);
        newMessage.find('.time').html(moment().fromNow());
        $('.chat-body').append(newMessage);
        scrollChat()
        $.post(url,
            {
                text: message
            });
    }

    function scrollChat() {
        $('.chat-body').animate({scrollTop: document.querySelector(".chat-body").scrollHeight});
    }
});

window.onload = function () {
	//Searching geolocation using browser or IP address
	navigator.geolocation.getCurrentPosition(function (position) {
		sendLocation({
			latitude: position.coords.latitude,
			longitude: position.coords.longitude,
            csrf_name: $('meta[name="csrf_name"]').attr("content"),
            csrf_value: $('meta[name="csrf_value"]').attr("content")
		});
	}, function () {
		$.ajax({
			url: 'http://freegeoip.net/json/',
			method: 'GET',
			dataType: 'json'
		}).done(function (data) {
            sendLocation({
				latitude: data.latitude,
				longitude: data.longitude,
                csrf_name: $('meta[name="csrf_name"]').attr("content"),
                csrf_value: $('meta[name="csrf_value"]').attr("content")
			});
		});
	}, {enableHighAccuracy: true});

	// Sending data to server
	function sendLocation(pos) {
        $.ajax({
			url: '/user/location',
			method: 'POST',
			dataType: 'json',
			async: 'true',
			data: pos
		})
	}
};
