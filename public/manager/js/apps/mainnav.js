/*
    File: mainnav.js
    Author: Crece Consultores
*/

jQuery.noConflict();

jQuery(document).ready(function() {
    /* navbar */
    jQuery(function(){
        jQuery('.collapse').on('show.bs.collapse', function() {
            jQuery(this).parents('li').toggleClass('open');
        });
        
        jQuery('.collapse').on('hide.bs.collapse', function() {
            jQuery(this).parents('li').toggleClass('open');
        });
    });
    
    /* navspy */
    jQuery(function(){
        var pathname = window.location.pathname;
	var splits = pathname.split('/');
	var last = parseInt(splits.length - 2);
	var actual = splits[last];
        
        jQuery('#sidenav a[href*="'+ actual +'"]').parent("li").addClass("active");
	jQuery('#sidenav a[href*="'+ actual +'"]').parent("a").removeClass("collapsed");
        jQuery('#sidenav a[href*="'+ actual +'"]').parents("li").addClass("open");
        jQuery('#sidenav a[href*="'+ actual +'"]').parents("li").addClass("active-sub");
	jQuery('#sidenav a[href*="'+ actual +'"]').parents("ul").addClass("in");
	jQuery('#sidenav a[href*="'+ actual +'"]').parents("ul").attr("aria-expanded",true);
    });
});