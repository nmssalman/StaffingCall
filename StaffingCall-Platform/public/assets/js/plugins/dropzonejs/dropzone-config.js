var photo_counter = 0;
$(document).ready(function () {
    Dropzone.autoDiscover = false;
    $("#real-dropzone").dropzone({
//Dropzone.options.realDropzone = {
    url: dataUrl,
    headers: {
         'x-csrf-token': $('#csrf-token').val(),
    },
    uploadMultiple: true,
    parallelUploads: 5,
    maxFiles: 7,
    maxFilesize: 2,
    previewsContainer: '#dropzonePreview',
    previewTemplate: document.querySelector('#preview-template').innerHTML,
    addRemoveLinks: true,
    dictRemoveFile: 'Remove',
    dictRemoveFileConfirmation: 'Are you sure to remove this photo?',
    dictFileTooBig: 'Image is bigger than 2MB',
    acceptedFiles: "image/*",

    // The setting up of the dropzone
    init:function() {
        
        var myDropzone = this;

        $.get(getAlertImgUrl, function(data) {
            $.each(data.images, function (key, value) {

                var file = {name: value.original, size: value.size};
                myDropzone.options.addedfile.call(myDropzone, file);
                myDropzone.options.thumbnail.call(myDropzone, file, '' + value.server);
                myDropzone.emit("complete", file);
                photo_counter++;
                $("#photoCounter").text( "(" + photo_counter + ")");
            });
        });

        this.on("removedfile", function(file) {
            var file_name = $(file.previewElement).find('[data-dz-name]').html();
            $.ajax({
                type: 'POST',
                url: removeImagesUrl,
                data: {fileName: file_name, _token: $('#csrf-token').val()},
                dataType: 'html',
                success: function(data){
                    console.log(data);
                    var rep = JSON.parse(data);
                    if(rep.code == 200)
                    {
                        photo_counter--;
                        $("#photoCounter").text( "(" + photo_counter + ")");
                    }

                }
            });

        } );
    },
    error: function(file, response) {
        //console.log(response);
        if($.type(response) === "string")
            var message = response; //dropzone sends it's own error messages in string
        else
            var message = response.message;
        file.previewElement.classList.add("dz-error");
        _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            _results.push(node.textContent = message);
        }
        return _results;
    },
    success: function(file,done) {
        $.each(done.images, function (key, value) {
            if(file.name == value.oldName){
               $(file.previewElement).find('[data-dz-name]').html(value.original); 
            }
        });
        
       // $(file.previewElement).find('[data-dz-name]').html(done.images[0].original);
           
        photo_counter++;
        $("#photoCounter").text( "(" + photo_counter + ")");
    }
});
});