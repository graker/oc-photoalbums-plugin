/**
 * Dropzone multiupload support to upload photos to album
 */

+function ($) {

  /**
   *
   * File is being removed from the list
   * We need to remove it on the server
   *
   * @param file
   */
  var removeFile = function (file) {
     var $preview = $(file.previewElement);
     var fileData = {
       file_id: $preview.data('id'),
       _token: $('input[name="_token"]').attr('value')
     };
    $(this).request('onFileRemove', {data: fileData});
  };


  /**
   *
   * File is being sent to the server
   * Used to add CSRF token to the form
   *
   * @param file
   * @param xhr
   * @param formData
   */
  var sendingData = function (file, xhr, formData) {
    var token = $('input[name="_token"]').attr('value');
    formData.append('_token', token);
  };


  /**
   *
   * File upload success callback
   *
   * @param data
   * @param response
   */
  var uploadSuccess = function (file, response) {
    var $preview = $(file.previewElement);
    if (response.id) {
      $preview.data('id', response.id);
      // hidden value to pass file id when saving form
      $preview.append('<input type="hidden" name="file-id[' + response.id + ']" value="' + response.id + '">');
      $preview.append('<div class="form-group"><input type="text" name="file-title[' + response.id + ']" class="form-control"></div>');
    }
  };


  /**
   * Initializes Dropzone
   */
  var initDropzone = function () {
    // register removed file callback
    this.on('removedfile', removeFile);
    // register before-send callback
    this.on('sending', sendingData);
  };


  /**
   * Initialize file upload
   */
  $(document).ready(function () {
    $("div.field-fileupload").each(function () {
      var uploadUrl = $(this).attr('data-url');
      $(this).dropzone({
        url: uploadUrl,
        init: initDropzone,
        addRemoveLinks: true,
        previewsContainer: '#filesContainer',
        success: uploadSuccess
      });
    });
  });
} (window.jQuery);
