jQuery(document).ready(function() {
$=jQuery;
var $captcha_container = $('.captcha-container');
if ($captcha_container.length > 0) {
        var $image = $('img', $captcha_container),
        $anchor = $('a', $captcha_container);
        $anchor.bind('click', function(e) {
                e.preventDefault();
                $image.attr('src', $image.attr('src').replace(/nocache=[0-9]+/, 'nocache=' + +new Date()));
        });
}
$.validator.setDefaults({
    ignore: [],
});
$.extend($.validator.messages,contact_submit_vars.validate_msg);
$.validator.addMethod('uniqueAttr',function(val,element){
  var unique = true;
  var data_input = $("form").serialize();
  $.ajax({
    type: 'GET',
    url: contact_submit_vars.ajax_url,
    cache: false,
    async: false,
    data: {action:'emd_check_unique',data_input:data_input, ptype:'emd_contact',myapp:'wp_easy_contact'},
    success: function(response)
    {
      unique = response;
    },
  });
  return unique;                
}, contact_submit_vars.unique_msg);
$('#contact_submit').validate({
onfocusout: false,
onkeyup: false,
onclick: false,
errorClass: 'text-danger',
rules: {
  emd_contact_first_name:{
},
emd_contact_last_name:{
},
emd_contact_email:{
email  : true,
},
emd_contact_phone:{
},
emd_contact_address:{
},
emd_contact_city:{
},
emd_contact_zipcode:{
},
blt_title:{
},
blt_content:{
},
},
success: function(label) {
label.remove();
},
errorPlacement: function(error, element) {
if (typeof(element.parent().attr("class")) != "undefined" && element.parent().attr("class").search(/date|time/) != -1) {
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("radio") != -1){
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("select2-offscreen") != -1){
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("selectpicker") != -1 && element.parent().parent().attr("class").search("form-group") == -1){
error.insertAfter(element.parent().find('.bootstrap-select').parent());
} 
else if(element.parent().parent().attr("class").search("pure-g") != -1){
error.insertAfter(element);
}
else {
error.insertAfter(element.parent());
}
},
});
$(document).on('click','#singlebutton_contact_submit',function(event){
     var form_id = $(this).closest('form').attr('id');
     $.each(contact_submit_vars.contact_submit.req, function (ind, val){
         if(!$('input[name='+val+'],#'+ val).closest('.row').is(":hidden")){
             $('input[name='+val+'],#'+ val).rules("add","required"); 
         }
     });
     var valid = $('#' + form_id).valid();
     if(!valid) {
        event.preventDefault();
        return false;
     }
});
});
