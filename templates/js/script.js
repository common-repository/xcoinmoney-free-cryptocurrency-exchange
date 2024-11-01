var $ = jQuery;
$(function () {

 $('#system_from_select').change(function(){
   jQuery.ajax({
     url: AJAX_URL ,
     type:'POST',
     dataType: 'json',
     data:'action=get_ways&pid=' + $('#system_from_select').val(),
     success: function (data) {
       var counter = 0, stringSelect = '<option value="0">Select </option>';
       for(counter; counter < data.length; counter++)
       {
          stringSelect = stringSelect+
            '<option value="' + data[counter].system_to + '">' + data[counter].label + '</option>';
       }
       $('#system_to_select').html(stringSelect);
     }
   });
   });
});
