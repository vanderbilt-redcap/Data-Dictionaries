$(function () {
$body = $("body");
var ajaxLoadTimeout;
    $("html").on("dragover", function (e) {
      e.preventDefault();
      e.stopPropagation();
    });
 
    $("html").on("drop", function (e) {
      e.preventDefault();
      e.stopPropagation();
    });
 
    $('#drop_file_area').on('dragover', function () {
      $(this).addClass('drag_over');
      return false;
    });
 
    $('#drop_file_area').on('dragleave', function () {
      $(this).removeClass('drag_over');
      return false;
    });
 
  $('#drop_file_area').on('drop', function (e) {
      e.preventDefault();
      $(this).removeClass('drag_over');
   
        var file = e.originalEvent.dataTransfer.files;
        var formData = new FormData();

    formData.append('file', file[0]);
    
     $('#inputFile').data('title',file[0].name).attr('data-title',file[0].name);        



      uploadFormData(formData);
  });
  
  $("#inputFile").change(function(){
      var formData = new FormData();

        var files = $('#inputFile')[0].files[0];
            

        formData.append('file', files);
    
        $('#inputFile').data('title',files.name).attr('data-title',files.name);        

    
         uploadFormData(formData);
  });
 
  function uploadFormData(form_data) {
    
      $.ajax({  
        url: ajax.ajaxpage,
        method: "POST",
        data: form_data,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
          $body.addClass("loading");
        },
        success: function (data) {
          $("#uploaded_file").empty();
          setTimeout(function() { 
          $('#uploaded_file').append(data);
          }, 3000);

          // $('#uploaded_file').append(data);

         
        },
        complete: function (form_data) {
          setTimeout(function() { 
            $body.removeClass("loading"); 
          }, 3000);
        }
      });
    
        

  }
 


});
  
