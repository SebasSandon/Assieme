/*
    File: place/update.app.js
    Author: Crece Consultores
*/

jQuery.noConflict();

var sortableApp =
{
    initDraggableEntityRows: function() {
        var dragSrcEl = null; // the object being drug
        var startPosition = null; // the index of the row element (0 through whatever)
        var endPosition = null; // the index of the row element being dropped on (0 through whatever)
        var parent; // the parent element of the dragged item
        
        function handleDragStart(e) {
            dragSrcEl = this;
            dragSrcEl.style.opacity = '0.4';
            parent = dragSrcEl.parentNode;
            startPosition = Array.prototype.indexOf.call(parent.children, dragSrcEl);
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            saveInputs(); // save input data
        }
        
        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault(); // Necessary. Allows us to drop.
            }
            e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

            return false;
        }
        
        function handleDragEnter(e) {
            this.classList.add('over');
        }
        
        function handleDragLeave(e) {
            this.classList.remove('over');  // this / e.target is previous target element.
        }
        
        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation(); // stops the browser from redirecting.
            }

            // Don't do anything if dropping the same column we're dragging.
            if (dragSrcEl != this) {
                endPosition = Array.prototype.indexOf.call(parent.children, this);
                // Set the source column's HTML to the HTML of the column we dropped on.
                dragSrcEl.innerHTML = this.innerHTML;
                this.innerHTML = e.dataTransfer.getData('text/html');

                // do
                var count = 0;
                jQuery('table.sortable > tbody tr').each(function(key, row){
                    jQuery(row).find('input[name*="position"]').val(count);
                    count = count + 1;
                });
                
                // inputs
                keepInputs();
            }

            return false;
        }

        function handleDragEnd(e) {
            this.style.opacity = '1';  // this / e.target is the source node.
            [].forEach.call(rows, function (row) {
                row.classList.remove('over');
            });
        }
        
        var inputs = [];
        function saveInputs(){
            jQuery('table.sortable > tbody tr').each(function(key, row){
                var rel = jQuery(row).find('input[name*="name"]').data('id');
                inputs[rel] = jQuery(row).find('input[name*="name"]').val();
            });
        }
        
        function keepInputs(){
            inputs.forEach(function(name, rel){
                var input = jQuery('table.sortable > tbody tr input[data-id="'+rel+'"]');
                jQuery(input).val(name);
            });
        }
        
        var rows = document.querySelectorAll('table.sortable > tbody tr');
        [].forEach.call(rows, function(row) {
            row.addEventListener('dragstart', handleDragStart, false);
            row.addEventListener('dragenter', handleDragEnter, false);
            row.addEventListener('dragover', handleDragOver, false);
            row.addEventListener('dragleave', handleDragLeave, false);
            row.addEventListener('drop', handleDrop, false);
            row.addEventListener('dragend', handleDragEnd, false);
        });
    },

    /**
     * Primary Admin initialization method.
     * @returns {boolean}
     */
    init: function() {
        this.initDraggableEntityRows();

        return true;
    }
};

jQuery(document).ready(function() {
    /* select2 */
    jQuery(function(){
        jQuery('*[data-widget="select2"]').select2({
            placeholder: "Seleccione uno o más elementos"
        });
    });
    
    /* summernote */
    jQuery(function(){
        jQuery('*[data-widget="summernote"]').summernote({
            height: 150,
            minHeight: 150,
            maxHeight: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
              ]
        });
    });

    /* phones */
    jQuery(function(){
        var phonesCount = jQuery("#phones-app").attr('data-count');
        
        jQuery('.add-btn[data-target="#phones-app"]').click(function(e){
            e.preventDefault();

            var phonesApp = jQuery("#phones-app");
           
            var new_row = phonesApp.attr('data-row');
            new_row = new_row.replace(/__name__/g, phonesCount);
            phonesCount++;
            
            phonesApp.find("#phones-table tbody").append(new_row);
        });
        
        jQuery("#phones-app").delegate( ".remove-btn", "click", function() {
            jQuery(this).parents("tr").remove();
        });
    });
    
    /* social */
    jQuery(function(){
        var socialCount = jQuery("#social-app").attr('data-count');
        
        jQuery('.add-btn[data-target="#social-app"]').click(function(e){
            e.preventDefault();

            var socialApp = jQuery("#social-app");
           
            var new_row = socialApp.attr('data-row');
            new_row = new_row.replace(/__name__/g, socialCount);
            socialCount++;
            
            socialApp.find("#social-table tbody").append(new_row);
        });
        
        jQuery("#social-app").delegate( ".remove-btn", "click", function() {
            jQuery(this).parents("tr").remove();
        });
    });
    
    /* schedules */
    jQuery(function(){
        var schedulesCount = jQuery("#schedules-app").attr('data-count');
        
        jQuery('.add-btn[data-target="#schedules-app"]').click(function(e){
            e.preventDefault();

            var schedulesApp = jQuery("#schedules-app");
           
            var new_row = schedulesApp.attr('data-row');
            new_row = new_row.replace(/__name__/g, schedulesCount);
            schedulesCount++;
            
            schedulesApp.find("#schedules-table tbody").append(new_row);
        });
        
        jQuery("#schedules-app").delegate( ".remove-btn", "click", function() {
            jQuery(this).parents("tr").remove();
        });
    });
    
    /* 
     * images upload 
     * http://www.dropzonejs.com/
     * */
    var previewNode = document.querySelector("#dz-template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);
    
    var uploadBtn = jQuery('#dz-upload-btn');
    var removeBtn = jQuery('#dz-remove-btn');
    var uploadDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
        url: jQuery('form[data-form="dropzone"]').attr('action'), // Set the url
        thumbnailWidth: 50,
        thumbnailHeight: 50,
        parallelUploads: 20,
        previewTemplate: previewTemplate,
        autoQueue: false, // Make sure the files aren't queued until manually added
        previewsContainer: "#dz-previews", // Define the container to display the previews
        clickable: ".fileinput-btn", // Define the element that should be used as click trigger to select files.
        acceptedFiles: "image/*"
    });


    uploadDropzone.on("addedfile", function(file) {
        uploadBtn.prop('disabled', false);
        removeBtn.prop('disabled', false);
    });

    uploadDropzone.on("totaluploadprogress", function(progress) {
        jQuery("#dz-total-progress .progress-bar").css({'width' : progress + "%"});
    });

    uploadDropzone.on("sending", function(file) {
        document.querySelector("#dz-total-progress").style.opacity = "1";
    });

    uploadDropzone.on("queuecomplete", function(progress) {
        document.querySelector("#dz-total-progress").style.opacity = "0";
        location.reload();
    });

    uploadDropzone.on("success", function(item, data) {
        item.previewElement.querySelector(".dz-error").textContent = data.message; 
    });

    uploadBtn.on('click', function() {
        uploadDropzone.enqueueFiles(uploadDropzone.getFilesWithStatus(Dropzone.ADDED));
    });

    removeBtn.on('click', function() {
        uploadDropzone.removeAllFiles(true);
        uploadBtn.prop('disabled', true);
        removeBtn.prop('disabled', true);
    });
    
    /**
     * sortable
     */
    sortableApp.init();
    
    /**
     * remove
     */
    jQuery("#images-table").delegate( ".remove-btn", "click", function() {
        jQuery(this).parents("tr").remove();
        
        // do
        var count = 1;
            jQuery('table.sortable > tbody tr').each(function(key, row){
            jQuery(row).find('input[name*="position"]').val(count);
            count = count + 1;
        });
    });
});