/*
    File: place/create.app.js
    Author: Crece Consultores
*/

jQuery.noConflict();

jQuery(document).ready(function() {
    /* select2 */
    jQuery(function(){
        jQuery('*[data-widget="select2"]').select2({
            placeholder: "Seleccione uno o más elementos"
        });
    });
});