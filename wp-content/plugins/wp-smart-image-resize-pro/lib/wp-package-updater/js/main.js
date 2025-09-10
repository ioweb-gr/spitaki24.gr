/* version 1.4.0 */
/* global WP_Package_Updater_SIR */
jQuery(document).ready(function ($) {

  // Activate/deactivate license key.
  $('#wrap_license_' + WP_Package_Updater_SIR.package_slug + ' button').on('click', function (e) {
    
    e.preventDefault();

    // Activation form
    var $form = $(this).closest('form');

    // Submit button
    var $button = $(this);

    var requestType = $button.val(); // activate/deactivate

    var buttonInitialTxt = $button.text(); 

    $button.prop('disabled', true);

    var data = {
      nonce: WP_Package_Updater_SIR.nonce,
      license_key: $form.find('.license').val(),
      action: WP_Package_Updater_SIR.action_prefix + '_' + requestType + '_license',
    };

    // Clear up any message.
    $form.find('.license-message').html('').css('color', '');

    // Button is busy.
    $button.text($button.data('pending-text'));

    $.ajax({
      url: WP_Package_Updater_SIR.ajax_url,
      data: data,
      type: 'POST',
      success: function (response) {
        $form.find('.license').val(response.data.license_key);
        $form.find('.license-message').html(response.data.message).css('color', 'green');
        $button.hide();
        if (requestType === 'activate') {
          $form.find('button[value="deactivate"]').show();
        }
        else {
          $form.find('button[value="activate"]').show();
        }
      },
      error: function (xhr, status, error) {
        $form.find('.license-message').html(xhr.responseJSON.data[0].message);
      },
      complete: function () {
        $button.prop('disabled', false);
        $button.text(buttonInitialTxt);
      },
    }); // Ajax request.
  }); // Click event.

});
