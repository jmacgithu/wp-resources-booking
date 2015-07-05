/**
 * Created by Fabio on 30/06/2015.
 */
jQuery(document).ready(function($) {
    var _dateTimePickerOptions = {
        dayViewHeaderFormat: 'MMMM YYYY',
        format: "YYYY-MM-DD HH:mm",
        sideBySide: true,};
    var _tomorrow = moment().add(1, 'day');
    var _twodays = moment().add(2, 'day');

    $('#select-resources').chosen({allow_single_deselect: true});
    $('#select-types').chosen({allow_single_deselect: true});
    $('#select-reasons').chosen({allow_single_deselect: true});

    $('#datetimepickerFrom').datetimepicker(_dateTimePickerOptions);
    $('#datetimepickerTo').datetimepicker(_dateTimePickerOptions);
    $("#datetimepickerFrom").data("DateTimePicker").date(_tomorrow);
    $("#datetimepickerTo").data("DateTimePicker").date(_twodays);

    $('#select-resources').chosen().change(function(a, value){
        // selected: 360
        console.log(a,value);
    });

    $('#datetimepickerFrom').on("dp.change", function(e) {
        showDuration();
    });
    $('#datetimepickerTo').on("dp.change", function(e) {
        showDuration();
    });


    $('#buttonSubmit').click(function(){
        var start =  $("#datetimepickerFrom").data("DateTimePicker").date();
        var end =  $("#datetimepickerTo").data("DateTimePicker").date();
        if( end.isBefore(start)){
            return;
        }

        var resource_id = $('#select-resources').val();
        var type = $('#select-types').val();
        if( 'none' != resource_id && 'none' != type){
            alert("Error, select either a resource or a type");
        }
        if( 'none' == resource_id && 'none' == type){
            alert("Error, select either a resource or a type");
        }
        var reason = $('#select-reasons').val();
        var notify = $('#notify-users').val();
        var data = {
            'action': 'res_admin_insert_out_of_work',
            'resource_id': resource_id,
            'type': type,
            'reason': reason,
            'notify': notify,
            'start': start.format(),
            'end': end.format(),
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
                $('#myModalLabel').html("Message");
                $('#errorMessageDiv').html("Operation successful");
            } else {
                $('#myModalLabel').html("Error");
                $('#errorMessageDiv').html("Cannot perform the requested operation<br />" +
                response.data.message);
            }
            $('#modalAlert').modal("show");
        });
    });

    function showDuration(){
        var start =  $("#datetimepickerFrom").data("DateTimePicker").date();
        var end =  $("#datetimepickerTo").data("DateTimePicker").date();
        if( end.isBefore(start)){
            return;
        }
        var duration_m = (end.diff(start, 'minutes') % 60);
        var duration_h = (end.diff(start, 'hours') % 24);
        var duration_d = end.diff(start, 'days');
        var duration = duration_d + " days, " + duration_h + " hours, " + duration_m + " minutes";
        $('#duration').html(duration);
    }
});