"use strict";

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

    //Searching geolocation using browser or IP address
    navigator.geolocation.getCurrentPosition(function(position) {
        sendLocation({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
        });
        // sendLocation(pos);
    }, function() {
        $.ajax({
            url: 'http://freegeoip.net/json/',
            method: 'GET',
            dataType: 'json'
        }).done(function (data) {
            sendLocation({
                latitude: data.latitude,
                longitude: data.longitude
            });
        });
    }, { enableHighAccuracy: true });

    // Sending data to server
    function sendLocation(pos) {
        $.ajax({
            url: '/user/location',
            method: 'POST',
            dataType: 'json',
            data: pos
        }).done(function () {
            console.log(pos);
            console.log('location send');
        });
    }

});


Dropzone.options.uploadWidget = {
    autoProcessQueue: false,
    uploadMultiple: true,
    parallelUploads: 5,
    maxFiles: 5,
    paramName: 'files',
    acceptedFiles: 'image/png, image/jpeg',
    dictDefaultMessage: 'Click or drop files here to upload',
    clickable: true,
    init: function() {
        var myDropzone = this;
        document.querySelector("button[type=submit]").addEventListener("click", function(e) {
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
        this.on("sendingmultiple", function() {
        });
        this.on("successmultiple", function(files, response) {
            // window.location.href = "";
        });
        this.on("errormultiple", function(files, response) {
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
