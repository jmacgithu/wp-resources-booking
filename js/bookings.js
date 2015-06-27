/**
 * Created by Fabio on 26/06/2015.
 */
jQuery(document).ready(function($) {
    var _dateTimePickerOptions = {
        dayViewHeaderFormat: 'MMMM YYYY',
        format: "YYYY-MM-DD",};
    var _now = moment();
    var _past = moment().subtract(1, 'month');

    $('#select_client_id').change(function(){
        var user_id = $(this).val();
        if(user_id == ""){
            return;
        }
        var data = {
            action: 'res_user_bookings',
            client_id: user_id,
            start: $('#datetimepickerFrom').data('DateTimePicker').date().format('YYYY-MM-DD'),
            end: $('#datetimepickerTo').data('DateTimePicker').date().format('YYYY-MM-DD'),
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
    });

    $('#datetimepickerFrom').datetimepicker(_dateTimePickerOptions);
    $('#datetimepickerTo').datetimepicker(_dateTimePickerOptions);
    $("#datetimepickerFrom").data("DateTimePicker").date(_past);
    $("#datetimepickerTo").data("DateTimePicker").date(_now);

    $('#currentWeek').click(function(){
        var monday = moment().startOf('isoweek');
        var sunday = moment(monday).add(7, 'days');
        $("#datetimepickerTo").data("DateTimePicker").date(sunday);
        $("#datetimepickerFrom").data("DateTimePicker").date(monday);
        $('#select_client_id').change();
    });

    $('#currentMonth').click(function(){
        var first = moment(moment().format('YYYY-MM-') + "01", 'YYYY-MM-DD');
        var last = moment(moment().format('YYYY-MM-') + moment().daysInMonth(), 'YYYY-MM-DD');
        $("#datetimepickerTo").data("DateTimePicker").date(last);
        $("#datetimepickerFrom").data("DateTimePicker").date(first);
        $('#select_client_id').change();
    });

    $('#lastMonth').click(function(){
        var lastMonth = moment().subtract(1, 'month');
        var first = moment(lastMonth.format("YYYY-MM-") + "01", 'YYYY-MM-DD');
        var last = moment(lastMonth.format("YYYY-MM-") + lastMonth.daysInMonth(), 'YYYY-MM-DD');
        $("#datetimepickerTo").data("DateTimePicker").date(last);
        $("#datetimepickerFrom").data("DateTimePicker").date(first);
        $('#select_client_id').change();
    });

    $('#select_client_id').change();

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
                .append('<td>' + booking.resource_type + '</td>');
            tbody.append(tr);
        });
        table.append(tbody);
        $('#bookings').append(table);
        table.tablesorter();
    }
});
