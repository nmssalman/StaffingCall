$(function() {
    
    
        $('.image-editor').cropit({
            exportZoom: 1.25,
            imageBackground: true,
            imageBackgroundBorderWidth: 20,
            imageState: {
              src: defaultGroupIcon,
            },
            allowDragNDrop:true,
            onFileChange: function(e) {
                
                  $('#getCroppedImg').attr('disabled', false);
                  
            },
            onImageError: function(object) {
                console.log(object.message);
            },
            smallImage: 'allow'
        });

        $('.rotate-cw').click(function() {
          $('.image-editor').cropit('rotateCW');
        });
        $('.rotate-ccw').click(function() {
          $('.image-editor').cropit('rotateCCW');
        });

        $('.export').click(function() {
          var imageData = $('.image-editor').cropit('export');
          $('#croppedImage').val(imageData);
          $("#logo_preview").attr("src", imageData);
          $('#cancelBtn').click();
          
        });
        
});