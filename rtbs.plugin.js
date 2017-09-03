var RTBSplugin = (function ($) {

    var opts;
    var $selectPeople;
    var $div;


    var isEmail = function(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    };


    var actionSelectDate = function selectDate() {
        var $date = $(this);
        var $content = $date.closest(".rtbs-container").find(".rtbs-tours-step");

        $content.html('<div style="height: 60px; margin: 50px auto; font-weight: bold;"><img src="' + myRtbsObject.loaderImgUrl + '"></div>');

        $.post(myRtbsObject.ajaxUrl, {"action": "rtbs_availability", "date": $date.val(), "tour_key": $date.data('tour-key')})
            .done(function (res) {
                $content.html(res);
                initDatePicker();
            })
            .fail(function (res) {
                $content.html("Failed Loading Tours");
            });
    };


    var actionPeopleChange = function() {
        var totalAmount= 0.00;
        var totalPax = 0;

        $selectPeople.each(function () {
            totalAmount += parseFloat($(this).data('rate')) * parseInt($(this).val(), 10);
            totalPax += parseInt($(this).data('pax'), 10) * parseInt($(this).val(), 10);
        });

        var numRemaining = $('#hd-remaining').val();
        var $htmlTotalPrice = $('#totalPrice');
        var errMsg = "";
        var plural = "";


        if (totalPax < opts.MinPaxPerBooking) {
            plural = (opts.MinPaxPerBooking == 1) ? "place" : "places";
            errMsg = "Minimum of  " + opts.MinPaxPerBooking + " " + plural + " required per booking";
        } else if(totalPax > opts.MaxPaxPerBooking) {
            plural = (opts.MaxPaxPerBooking == 1) ? "place" : "places";
            errMsg = "Maximum of  " + opts.MaxPaxPerBooking + " " + plural + " allowed per booking";
        } else if (totalPax > numRemaining) {
            plural = (numRemaining == 1) ? "place" : "places";
            errMsg = "Only " + numRemaining + " " + plural + " remaining";
        }

        if (errMsg) {
            $htmlTotalPrice.html(errMsg);
            $htmlTotalPrice.css({color: 'red'});
        } else {
            $htmlTotalPrice.css({color: 'black'});
            $htmlTotalPrice.html('Total: $' + totalAmount.toFixed(2));
        }
    };


    var actionSubmitForm = function() {
        var totalPax = 0;
        var errors = [];
        var numRemaining = $('#hd-remaining').val();

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

        if (totalPax < opts.MinPaxPerBooking) {
            errors.push("Minimum of  " + opts.MinPaxPerBooking + " places required per booking");
        }

        if (totalPax > opts.MaxPaxPerBooking) {
            errors.push("Maximum of  " + opts.MaxPaxPerBooking + " places allowed per booking");
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
    };


    var bindEvents = function() {
        $div = $(".rtbs-plugin-content");
        $selectPeople.on('change', actionPeopleChange);
        $('#details-form').on('submit', actionSubmitForm);
        $('#rtbs-checkbox-tandc').on('change', actionTandcChecked);
        $div.on('change', ".rtbs-plugin-datepicker", actionSelectDate);
    };


    var initDatePicker = function() {
        $(".rtbs-plugin-datepicker").datepicker({
            minDate: 0,
            dateFormat: 'yy-mm-dd'
        });
    };


    var init = function(options) {
        opts = options || {
            MinPaxPerBooking: 0,
            MaxPaxPerBooking: 999
        };

        cacheElements();
        bindEvents();
        initDatePicker();
    };


    return {
        init: init
    };

})(jQuery);