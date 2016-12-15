var RTBSplugin = (function ($) {

    var $selectPeople,
        $datePicker;

    var isEmail = function(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    };

    var actionSelectDate = function selectDate() {
        var $date = $(this),
            $content = $date.closest(".rtbs-container").find(".rtbs-tours-step");

        $content.html('<div style="height: 60px; margin: 50px auto; font-weight: bold;"><img src="' + myRtbsObject.loaderImgUrl + '"></div>');

        $.post(myRtbsObject.ajaxUrl, {"action": "rtbs_availability", "date": $date.val(), "tour_key": $date.data('tour-key')})
            .done(function (res) {
                $content.html(res);
            })
            .fail(function (res) {
                $content.html("Failed Loading Tours");
            });
    };

    var actionPeopleChange = function() {
        var totalAmount= 0.00,
            totalPax = 0;

        $selectPeople.each(function () {
            totalAmount += parseFloat($(this).data('rate')) * parseInt($(this).val(), 10);
            totalPax += parseInt($(this).data('pax'), 10) * parseInt($(this).val(), 10);
        });

        var numRemaining = $('#hd-remaining').val(),
            $htmlTotalPrice = $('#totalPrice');

        if (totalPax > numRemaining) {
            $htmlTotalPrice.css({color: 'red'});
            $htmlTotalPrice.html("Only " + numRemaining + " places remaining");
        } else {
            $htmlTotalPrice.css({color: 'black'});
            $htmlTotalPrice.html('Total: $' + totalAmount.toFixed(2));
        }
    };

    actionSubmitForm = function() {
        var totalPax = 0,
            errors = [],
            numRemaining = $('#hd-remaining').val();

        $('select.nPeople').each(function () {
            totalPax += parseInt($(this).data('pax'), 10) * parseInt($(this).val(), 10);
        });

        if (!$('#rtbsFname').val()) {
            errors.push('First Name is required');
        }

        if (!$('#rtbsLname').val()) {
            errors.push('Last Name is required');
        }

        if (!$('#rtbsEmail').val()) {
            errors.push('Email is required');
        } else if (!isEmail($('#rtbsEmail').val())) {
            errors.push('Email is not valid');
        }

        if (!$('#rtbsPhone').val()) {
            errors.push('Phone is required');
        }

        if (totalPax == 0) {
            errors.push("No prices selected");
        }

        if (totalPax > numRemaining) {
            errors.push("Only " + numRemaining + " places remaining");
        }

        if (errors.length) {
            $('.alert-danger').show().html(errors.join('<br>'));
            return false;
        } else {
            return true;
        }
    };

    var actionTandcChecked = function() {
        $('#confirm_pay').prop('disabled', !$(this).is(':checked'));
    };

    var cacheElements = function() {
        $selectPeople = $('select.nPeople');
        $datePicker = $('.rtbs-plugin-datepicker');
    };

    var bindEvents = function() {
        $selectPeople.on('change', actionPeopleChange);
        $('#details-form').on('submit', actionSubmitForm);
        $('#rtbs-checkbox-tandc').on('change', actionTandcChecked);
        $datePicker.on('change', actionSelectDate);
    };

    var init = function() {
        cacheElements();
        bindEvents();

        $datePicker.datepicker({dateFormat: 'yy-mm-dd'});
    };

    return {
        init: init
    };

})(jQuery);


jQuery(document).ready(function() {
    RTBSplugin.init()
});