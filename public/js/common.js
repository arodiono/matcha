
$(function () {

    $('.datepicker').datetimepicker({
        format: 'MM/DD/YYYY',
        defaultDate: moment().format('l'),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-screenshot',
            clear: 'fa fa-trash',
            close: 'fa fa-remove',
            inline: true
        }
    });

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
    $(function () {
        $(".chat-body").length != 0 ? scrollChat() : null
    })

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
		$(this).html(moment.utc($(this).data().time).from(moment.utc()))
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
        let message = JSON.parse(e.data)
        if (message.message) {
            addNewIncomeMessage(e.data);
            setTimeout(function () {
                $.post(window.location.href + '/smhbr',
                    {
                        user: $('[data-user]').data().user
                    });
            }, 500)
        }
        else if (message.notification) {
            $.notify({
                icon: "notifications",
                title: "New "+message.notification.type+ " from <b>"+message.notification.from+"</b>",
                message: message.notification.text,
                url: '//' + window.location.host + '/messages/'+ message.notification.from,

            }, {
                type: 'rose',
                timer: 3000,
                placement: {
                    from: "bottom",
                    align: "left"
                }
            });
        }

    };
    $('.message-field').submit(function (e) {
        e.preventDefault();
        addNewOutcomeMessage(document.getElementById('new-message').value);
        document.getElementById('new-message').value = '';
    });

    function addNewIncomeMessage(message) {
        var newMessage = $('.left').first().clone().removeClass('hide');
        // console.log(message)
        newMessage.find('.text').html(JSON.parse(message).message.text);
        newMessage.find('.time').html(moment().fromNow());
        $('.chat-body').append(newMessage)
        scrollChat()
    }

    function addNewOutcomeMessage(message) {
        var newMessage = $('.right').first().clone().removeClass('hide');
        var url = window.location.href;
        var receiver = url.split('/')[url.split('/').length - 1];
        conn.send(JSON.stringify({
            auth: $('[data-user]').data().user,
            type: 'message',
            to: receiver,
            msg: message
        }));
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
    function showNotification(type, message) {
        console.log('test')
        // type = ['', 'info', 'success', 'warning', 'danger', 'rose', 'primary'];

        // color = Math.floor((Math.random() * 6) + 1);

        $.notify({
            icon: "notifications",
            message: message

        }, {
            type: type,
            timer: 3000,
            placement: {
                from: "bottom",
                align: "left"
            }
        });
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
