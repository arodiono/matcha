"use strict";

$(function () {

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

    $('[data-time]').each(function () {
		$(this).html(moment($(this).data().time, "YYYY-MM-DD HH:mm:ss").fromNow())
    })
    scrollChat()

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

Dropzone.options.uploadWidget = {
	autoProcessQueue: false,
	uploadMultiple: true,
	parallelUploads: 5,
	maxFiles: 5,
	paramName: 'files',
	acceptedFiles: 'image/png, image/jpeg',
	dictDefaultMessage: 'Click or drop files here to upload',
	clickable: true,
	init: function () {
		var myDropzone = this;
		document.querySelector("button[type=submit]").addEventListener("click", function (e) {
			if ($('#profile-photo').val() !== "") {
				e.preventDefault();
				e.stopPropagation();
				myDropzone.processQueue();
			} else {
				$('.page-header').after("<div class=\"alert alert-info\">\n" +
					"    <div class=\"container\">\n" +
					"    <div class=\"alert-icon\">\n" +
					"    <i class=\"material-icons\">info_outline</i>\n" +
					"    </div>\n" +
					"    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
					"    <span aria-hidden=\"true\"><i class=\"material-icons\">clear</i></span>\n" +
					"</button>\n" +
					"\n" +
					"<b>Info alert:</b> Choose your profile photo\n" +
					"</div>\n" +
					"</div>");
			}
		});
		this.on("sendingmultiple", function () {
		});
		this.on("successmultiple", function (files, response) {
			window.location.href = '/';
		});
		this.on("errormultiple", function (files, response) {
		});
		this.on("addedfile", function (file) {
			file.previewElement.addEventListener('click', function () {
				if (!this.classList.contains('dz-error')) {
					$('.dz-preview').removeClass('choose');
					this.classList.add('choose');
					$('#profile-photo').val($(this).find('img').attr('alt'));
				}
			})
		});
	}
};
