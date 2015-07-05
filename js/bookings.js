/**
 * Created by Fabio on 26/06/2015.
 */
jQuery(document).ready(function($) {
    var _dateTimePickerOptions = {
        dayViewHeaderFormat: 'MMMM YYYY',
        format: "YYYY-MM-DD",};
    var _past = moment().subtract(1, 'month');
    var _now = moment();
    var _start = _past;
    var _end = _now;
    var _client_id = null;
    var _timeout = null;

    if(_labmanager){
        $('#select_client_id').chosen({});
    }

    $('#select_client_id').change(function(){
        var user_id = $(this).val();
        if(user_id == ""){
            return;
        }
        _client_id = user_id;
        send_data();
    });

    $('#datetimepickerFrom').datetimepicker(_dateTimePickerOptions);
    $('#datetimepickerTo').datetimepicker(_dateTimePickerOptions);
    $("#datetimepickerFrom").data("DateTimePicker").date(_past);
    $("#datetimepickerTo").data("DateTimePicker").date(_now);

    $('#datetimepickerFrom').on("dp.change", function(e) {
        if(_timeout != null){
            return;
        }
        _start = $(this).data("DateTimePicker").date();
        if(null == _end){
            var end = moment(_start).add(7, 'days');
            $('#datetimepickerTo').data("DateTimePicker").date(end);
        }
        send_data();
    });
    $('#datetimepickerTo').on("dp.change", function(e) {
        if(_timeout != null){
            return;
        }
        _end = $(this).data("DateTimePicker").date();
        if(null == _start){
            var start = moment(_end).subtract(7, 'days');
            $('#datetimepickerFrom').data("DateTimePicker").date(start);
        }
        send_data();
    });

    $('#currentWeek').click(function(){
        _timeout = setTimeout(function(){ _timeout = null; }, 500);
        var monday = moment().startOf('isoweek');
        var sunday = moment(monday).add(7, 'days');
        $("#datetimepickerFrom").data("DateTimePicker").date(monday);
        $("#datetimepickerTo").data("DateTimePicker").date(sunday);
        _start = monday;
        _end = sunday;
        send_data();
    });

    $('#currentMonth').click(function(){
        _timeout = setTimeout(function(){ _timeout = null; }, 500);
        var first = moment(moment().format('YYYY-MM-') + "01", 'YYYY-MM-DD');
        var last = moment(moment().format('YYYY-MM-') + moment().daysInMonth(), 'YYYY-MM-DD');
        $("#datetimepickerFrom").data("DateTimePicker").date(first);
        $("#datetimepickerTo").data("DateTimePicker").date(last);
        _start = first;
        _end = last;
        send_data();
    });

    $('#lastMonth').click(function(){
        _timeout = setTimeout(function(){ _timeout = null; }, 500);
        var lastMonth = moment().subtract(1, 'month');
        var first = moment(lastMonth.format("YYYY-MM-") + "01", 'YYYY-MM-DD');
        var last = moment(lastMonth.format("YYYY-MM-") + lastMonth.daysInMonth(), 'YYYY-MM-DD');
        $("#datetimepickerFrom").data("DateTimePicker").date(first);
        $("#datetimepickerTo").data("DateTimePicker").date(last);
        _start = first;
        _end = last;
        send_data();
    });

    $('#all-bookings').click(function(){
        _start = null;
        _end = null;
        send_data();
    });

    $('#select_client_id').change();

    function send_data(){
        if(_client_id == null){
            return;
        }
        var start = null;
        var end = null;
        if(_start != null && _end != null){
            if(_end.isBefore(_start)){
                return;
            }
        }
        if(_start != null){
            start = _start.format('YYYY-MM-DD');
        }
        if(_end != null){
            end = _end.format('YYYY-MM-DD');
        }
        var data = {
            action: _action,
            client_id: _client_id,
            start: start,
            end: end,
        };

        jQuery.post(ajax_object.ajax_url, data, function(response_json) {
            var response;
            try{
                response = JSON.parse(response_json);
            }catch (err){
                response = response_json;
            }

            if (response.success) {
                var userInfo = response.userInfo;
                $('#display_name-span').text(userInfo.display_name);
                $('#login-span').text(userInfo.login);
                $('#email-span').text(userInfo.email);
                var bookings = response.bookings;
                printTable(bookings);
            } else {
                alert("Error: Cannot get the info of this user\n" + response_json.data.message);
            }
        });
    }

    function printTable(bookings){
        // ds ts de te dur machine type
        $('#bookings').empty();
        var tr = $('<tr>').append('<th>Start day</th>')
            .append('<th>Start time</th>')
            .append('<th>End day</th>')
            .append('<th>End time</th>')
            .append('<th>Duration</th>')
            .append('<th>Machine name</th>')
            .append('<th>Machine type</th>');
        var thead = $('<thead>').append(tr);
        var table = $('<table id="booking-table" class="tablesorter">').append(thead);
        var tbody = $('<tbody>');
        $.each(bookings, function(pos, booking) {
            var m_start = moment(booking.start, 'YYYY-MM-DD HH:mm:ss');
            var m_end = moment(booking.end, 'YYYY-MM-DD HH:mm:ss');
            var duration_h = m_end.diff(m_start, 'hours');
            var duration_m = m_end.diff(m_start, 'minutes');
            duration_m = duration_m % 60;
            var duration = duration_h + ":" + duration_m;
            duration = moment(duration, "HH:mm");

            tr = $('<tr>').append('<td>' + m_start.format('YYYY-MM-DD') + '</td>')
                .append('<td>' + m_start.format('HH:mm') + '</td>')
                .append('<td>' + m_end.format('YYYY-MM-DD') + '</td>')
                .append('<td>' + m_end.format('HH:mm') + '</td>')
                .append('<td>' + duration.format('HH:mm') + '</td>')
                .append('<td>' + booking.resource_name + '</td>')
                .append('<td style="text-transform: capitalize">' + booking.resource_type + '</td>');
            tbody.append(tr);
        });
        table.append(tbody);
        $('#bookings').append(table);
        table.tablesorter();
    }
});