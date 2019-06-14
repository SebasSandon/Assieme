/*
    File: forms.js
    Author: Crece Consultores
*/

jQuery.noConflict();

jQuery(document).ready(function() {
    /* upload form */
    jQuery(function(){
        var $uploadForm;
        jQuery('form[data-form="upload"]').ajaxForm({
            resetForm: false,
            dataType: 'json',
            beforeSerialize: function($form) {
		var percentVal = '0%';
		jQuery($form).find(".progress-bar").data('valuenow',percentVal);
		jQuery($form).find(".progress-bar").css('width',percentVal);
		jQuery($form).find(".progress-bar").parent(".progress").fadeIn("fast");
				
		$uploadForm = $form;
            },
            uploadProgress: function(event, position, total, percentComplete) {
		var percentVal = percentComplete + '%';
		jQuery($uploadForm).find(".progress-bar").data('valuenow',percentVal);
		jQuery($uploadForm).find(".progress-bar").css('width',percentVal);
            },
            success: function(data, statusText, xhr, $form){
		var percentVal = '100%';
		jQuery($uploadForm).find(".progress-bar").data('valuenow',percentVal);
		jQuery($uploadForm).find(".progress-bar").css('width',percentVal);
				
		jQuery($uploadForm).find(".progress-bar").parent(".progress").fadeOut("fast");
                
                location.href = jQuery($uploadForm).data('redirect');
            },
            error: function(){
                location.reload();
            }
        });
    });
});