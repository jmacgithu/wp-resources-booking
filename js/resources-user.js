/**
 * Created by Fabio on 24/06/2015.
 */
jQuery(document).ready(function($) {
    // Calendar configuration
    // Declared in resource-booking/js/calendarOpts.js
    _calendarOpts.eventSources = [{
        // Get events from the db
        events: getEvents
    }];
    _calendarOpts.select        = newEvent;
    _calendarOpts.eventResize   = updateEvent;
    _calendarOpts.eventDrop     = updateEvent;
    _calendarOpts.eventClick    = deleteEvent;

    var _calendar = null;
    var _resource_id = null;
    var _resource_name = null;

    $('#buttonSubmit').on('click', function(e){
        // We don't want this to act as a link so cancel the link action
        e.preventDefault();
        doSubmit();
    });

    $('#buttonDelete').on('click', function(e){
        // We don't want this to act as a link so cancel the link action
        e.preventDefault();
        doDelete();
    });

    if( typeof _resourceInfo !== 'undefined' ){
        $('#select-resources').val(_resourceInfo.resource_id);
    }else{
        _resourceInfo = {};
    }

    $('#select-resources').change(function(){
        var resource_id = $(this).val();
        if(resource_id == ""){
            return;
        }
        _resource_id = resource_id;
        var data = {
            'action': 'res_resource_info',
            'resource_id': _resource_id,
        };
        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }

            if (response.success) {
                var resourceInfo = response.resourceInfo;
                _resource_id = resourceInfo.resource_id;
                _resource_name = resourceInfo.title;

                if("" != resourceInfo.page_description_id){
                    var link = "<a href='" + resourceInfo.page_description_id + "' target='_blank'>" + resourceInfo.title + "</a>";
                    $('#info_name').html(link);
                }else{
                    $('#info_name').html(resourceInfo.title);
                }
                $('#resource_type').html(resourceInfo.resource_type).css("text-transform", "capitalize");
                $('#open_from').html(resourceInfo.open_from);
                $('#open_till').html(resourceInfo.open_till);
                $('#works_overnight').html(resourceInfo.works_overnight ? "Yes" : "No");
                $('#works_holidays').html(resourceInfo.works_holidays ? "Yes" : "No");
                $('#slot_min').html(resourceInfo.slot_min);
                $('#slot_max').html(resourceInfo.slot_max);
                $('#slot_length').html(resourceInfo.slot_length);

                _calendarOpts.minTime = resourceInfo.open_from;
                _calendarOpts.maxTime = resourceInfo.open_till;
                _calendarOpts.slotDuration = resourceInfo.slot_length + ':00';

                _resourceInfo.resource_type = resourceInfo.resource_type;
                _resourceInfo.page_description_id = resourceInfo.page_description_id;
                _resourceInfo.open_from = resourceInfo.open_from;
                _resourceInfo.open_till = resourceInfo.open_till;
                _resourceInfo.works_overnight = resourceInfo.works_overnight ? true : false;
                _resourceInfo.works_holidays = resourceInfo.works_holidays ? true : false;
                _resourceInfo.slot_min = resourceInfo.slot_min;
                _resourceInfo.slot_max = resourceInfo.slot_max;
                _resourceInfo.slot_length = resourceInfo.slot_length;

                if(_resourceInfo.works_overnight){
                    _calendarOpts.minTime = '00:00';
                    _calendarOpts.maxTime = '24:00';
                }

                if(null != _calendar) {
                    $('#calendar').fullCalendar('destroy');
                    $('#calendar').remove();
                    $('#calendar-holder').prepend('<div id="calendar"></div>');
                }
                _calendar = $('#calendar').fullCalendar(_calendarOpts);
            } else {
                $('#errorMessageDiv').html("Error: Cannot get the info of this resource<br />" +
                    response.data.message);
                $('#modalAlert').modal("show");
            }
        });
    });

    $('#select-resources').change();

    function newEvent(start, end, jsEvent, view) {
        if (! validateStartEndDate(start, end)){
            return;
        }
        if("month" == view.name){
            return;
        }
        var mywhen = $.fullCalendar.formatRange(start, end,'ddd, MMM d, HH:mm');

        $('#inputResourceName').html(_resource_name);
        $('#inputResourceId').val(_resource_id);

        $('#apptStartTime').val(start);
        $('#apptEndTime').val(end);
        $('#when').text(mywhen);

        $('#buttonDelete').addClass('hidden');
        $('#buttonSubmit').removeClass('hidden');

        $('#myModal').modal('show');
    }

    // Makes a POST request to the server to update a reservation
    function updateEvent(event, delta, revertFunc) {
        if (! validateStartEndDate(event.start, event.end)){
            revertFunc();
            return;
        }
        var data = {
            'action': 'res_user_update_booking',
            'id': event.id,
            'resource_id': _resource_id,
            'start': event.start.format(),
            'end': event.end.format()
        };
        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }

            if (response.success) {
                var booking = response.booking;
                $('#calendar').fullCalendar('updateEvent', bookingToEvent(booking), true);
            } else {
                $('#errorMessageDiv').html("Cannot update the booking for this resource<br />" +
                response.data.message);
                $('#modalAlert').modal("show");
                revertFunc()
            }
        });
    }

    function deleteEvent(event, jsEvent, view) {
        if (! validateStartEndDate(event.start, event.end)){
            return;
        }
        $('#inputResourceName').html(_resource_name);
        $('#inputResourceId').val(_resource_id);

        var mywhen = $.fullCalendar.formatRange(event.start, event.end,'ddd, MMM d, HH:mm');
        $('#apptStartTime').val(event.start);
        $('#apptEndTime').val(event.end);
        $('#when').text(mywhen);

        $('#buttonSubmit').addClass('hidden');
        $('#buttonDelete').removeClass('hidden');

        $('#myModal').data('event', event);

        $('#myModal').modal('show');
    }

    function getEvents(start, end, timezone, callback) {
        $('#calendar').fullCalendar( 'removeEvents' );
        var data = {
            'action': 'res_list_bookings_by_resource_id_start_end',
            'resource_id': _resource_id,
            'start': start.format('YYYY-MM-DD'),
            'end': end.format('YYYY-MM-DD'),
        };
        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }
            if (response.success) {
                var events = $.map(response.events, bookingToEvent);
                callback(events);
            } else {
                $('#errorMessageDiv').html("Cannot download this resource's events<br />" +
                response.data.message);
                $('#modalAlert').modal("show");
            }
        });
    }

    function doSubmit(){
        $("#myModal").modal('hide');
        var start   = moment($('#apptStartTime').val()).format(); //'YYYY-MM-DD HH:mm:ss'
        var end     = moment($('#apptEndTime').val()).format();

        var data = {
            'action': 'res_user_insert_booking',
            'resource_id': _resource_id,
            'start': start,
            'end': end,
        };
        // Post the new event to the server
        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }
            if (response.success) {
                var booking = response.booking;
                $("#calendar").fullCalendar('renderEvent', bookingToEvent(booking), true);
            } else {
                $('#errorMessageDiv').html("Cannot insert the booking for this resource<br />" +
                    response.data.message);
                $('#modalAlert').modal("show");
            }
        });
    }

    function doDelete(){
        $("#myModal").modal('hide');

        var event = $("#myModal").data('event');

        var data = {
            'action': 'res_user_delete_booking',
            'id': event.id,
            'resource_id': _resource_id,
            'start': event.start.format(),
            'end': event.end.format(),
        };
        // Post the new event to the server
        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }
            if (response.success) {
                $("#calendar").fullCalendar( 'removeEvents', event.id );
            } else {
                $('#errorMessageDiv').html("Cannot delete the booking for this resource<br />" +
                    response.data.message);
                $('#modalAlert').modal("show");
            }
        });
    }

    function validateStartEndDate(start, end){
//        console.log(start);
//        console.log(end);
        if(end.isBefore(start)){
            return false;
        }
        if(start.isBefore(new Date())){
            $('#errorMessageDiv').html("Can not book on past days / hours");
            $('#modalAlert').modal("show");
            return false;
        }
        var dow = start.format('d');
        if( 0 == dow || 6 == dow){
            $('#errorMessageDiv').html("Can not book resource during holidays");
            $('#modalAlert').modal("show");
            return false;
        }
        if(! _resourceInfo.works_overnight){
            // If can't work overnight,
            // check end is not on weekends
            dow = end.format('d');
            if( 0 == dow || 6 == dow){
                $('#errorMessageDiv').html("Can not book a resource during holidays");
                $('#modalAlert').modal("show");
                return false;
            }

            // End time < open_till
            var end_h_m = end.format("HH:mm");
            var end_h_m_parsed = moment(end_h_m, "HH:mm")
            var open_till_parsed = moment(_resourceInfo.open_till, "HH:mm");
            if(end_h_m_parsed.isAfter(open_till_parsed)){
                $('#errorMessageDiv').html("Booking end must be before closing time");
                $('#modalAlert').modal("show");
                return false;
            }

            // If can't work overnight,
            // start & end on the same day
            if(start.format('YYYY-MM-DD') != end.format('YYYY-MM-DD')){
                $('#errorMessageDiv').html("Booking start / end must be on the same day");
                $('#modalAlert').modal("show");
                return false;
            }
        }
        var duration = end.diff(start, 'minutes');
        var min_duration = hours_min_to_min(_resourceInfo.slot_min);
        var max_duration = hours_min_to_min(_resourceInfo.slot_max);
        if ( duration < min_duration ){
            $('#errorMessageDiv').html("Booking duration must be longer than " + _resourceInfo.slot_min + " (hh:mm)");
            $('#modalAlert').modal("show");
            return false;
        }
        if( duration > max_duration){
            $('#errorMessageDiv').html("Booking duration must be shorter " + _resourceInfo.slot_max + " (hh:mm)");
            $('#modalAlert').modal("show");
            return false;
        }
        return true;
    }

    function hours_min_to_min(h_m){
        // parse the string
        var m = moment(h_m, "HH:mm");
        return m.hours() * 60 + m.minutes();
    }

    function bookingToEvent(booking) {
        var view = $('#calendar').fullCalendar('getView');
        if('month' == view.name && !booking.personal){
            return null;
        }

        var color, editable;
        if(booking.personal){
            color = "#a4bdfc"; //Blueish
            editable = true;
        }else{
            color = "#ff887c"; //Red
            editable = false;
        }

        return {
            id: booking.id,
            title: booking.username,
            start: booking.start,
            end: booking.end,
            color: color,
            editable: editable,
            allDay: false,
            overlap: false,
            personal: booking.personal,
        }
    }
});