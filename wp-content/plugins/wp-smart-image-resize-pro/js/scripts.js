// Utils

var WP_SIR_UTIL = {
  setCookie: function (cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }
};

(function ($) {
  'use strict';

  // ELEMENTS
  var $colorPicker = $('#wpSirColorPicker');
  var $compressImageSlider = $('.wpSirSlider');

  // ------------------------------------------------------------------------------------------
  // INITILIAZE COLOR PICKER
  // ------------------------------------------------------------------------------------------

  $colorPicker.wpColorPicker();

  // ------------------------------------------------------------------------------------------
  // INITILIAZE COMPRESSION SLIDER.
  // ------------------------------------------------------------------------------------------

  $compressImageSlider.each(function () {
    var handle = $(this).find('.wpSirSliderHandler');
    var inputElement = $('.' + $(this).data('input'));
    $(this).slider({
      create: function () {
        $(this).slider('value', inputElement.val());
        handle.text($(this).slider('value') + '%');
      },
      slide: function (event, ui) {
        handle.text(ui.value + '%');
        inputElement.val(ui.value);
      },
      change: function (event, ui) {
        handle.text(ui.value + '%');
      },
    });
  });

  // we'll wait until the box is rendered, so we can move it to the top.
  var wpsirLoadIntervalId = setInterval(() => {
    if ($('.wpsirProcessMediaLibraryImageWraper').length) {
      clearInterval(wpsirLoadIntervalId);

      $('.wpsirProcessMediaLibraryImageWraper')
        .insertBefore($('#wp-media-grid > .media-frame'));

      handleProcessMediaLibraryChange($('#processMediaLibraryImage'));

      $(document).on('change', '#processMediaLibraryImage', function () {
        handleProcessMediaLibraryChange($(this));
      });

    }
  }, 100);


  /**
   * Allow user to decide whether to process image being uploaded.
   * We'll place a checkbox input where we cannot determine image attachment parent
   * under "Media > Library" and "Media > Add" new pages.
   */


  function handleProcessMediaLibraryChange($input) {
    var isProcessable = $input.is(':checked');

    WP_SIR_UTIL.setCookie(wp_sir_object.process_ml_upload_cookie, isProcessable.toString(), 365);
    // Normal HTML uploader.
    if ($('#html-upload-ui').length) {
      var $htmlProcessableInput = $('input[name="_processable_image"]');

      if ($htmlProcessableInput.length === 0) {
        $('#html-upload-ui').append(
          '<input type="hidden"  name="_processable_image" >'
        );
        $htmlProcessableInput = $($htmlProcessableInput.selector);
      }
      $htmlProcessableInput.val(isProcessable);
    }

    // Drag-and-drop uploader box.
    if (
      typeof wpUploaderInit === 'object' &&
      wpUploaderInit.hasOwnProperty('multipart_params')
    ) {
      wpUploaderInit.multipart_params._processable_image = isProcessable;
    }

    // Media library modal.
    if (
      wp.media &&
      wp.media.frame &&
      wp.media.frame.uploader &&
      wp.media.frame.uploader.uploader
    ) {
      wp.media.frame.uploader.uploader.param('_processable_image', isProcessable);
    }
  }



  // Reset "Image sizes" to default ones.
  $(document).on('click', '#wpsirResetDefaultSizes', function () {
    var preselectedSizes = $('#wp-sir-sizes-selector').data('defaults').split(',');
    $('.wpSirSelectSize').each(function () {
      if (preselectedSizes.indexOf($(this).val()) >= 0) {
        $(this).prop('checked', true).change();
      } else {
        $(this).prop('checked', false).change();
      }
    });
  });


  // Add filter to Media Library (grid view)

  if (typeof sir_vars != 'undefined') {
    var SIR_MediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
      id: 'media-attachment-sir-filter',
      createFilters() {
        this.filters = {
          all: {
            text: sir_vars.filter_strings.all,
            props: { _filter: 'all' },
            priority: 10,
          },
          processed: {
            text: sir_vars.filter_strings.processed,
            props: { _filter: 'processed' },
            priority: 20,
          },
          unprocessed: {
            text: sir_vars.filter_strings.unprocessed,
            props: { _filter: 'unprocessed' },
            priority: 30,
          },
        };
      },
    });

    var SIR_AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
      createToolbar() {
        // Make sure to load the original toolbar
        SIR_AttachmentsBrowser.prototype.createToolbar.call(this);
        this.toolbar.set(
          'SIR_MediaLibraryTaxonomyFilter',
          new SIR_MediaLibraryTaxonomyFilter({
            controller: this.controller,
            model: this.collection.props,
            priority: -75,
          }).render()
        );
      },
    });

  }

  // Handle the "Clear" button display.
  $('#wp-sir-clear-bg-color').on('click', function (e) {
    $colorPicker.find('.wp-picker-clear').click();
    $colorPicker.val('').trigger('change');
    $colorPicker.find('.wp-color-result').css({ 'background-color': '' });
    e.preventDefault();
    e.stopPropagation();
    $(this).hide();
  });

  if (!$colorPicker.val()) {
    $('#wp-sir-clear-bg-color').hide();
  }

  $('.wp-picker-container').on('click', function () {
    if ($(this).hasClass('wp-picker-active')) {
      $('#wp-sir-clear-bg-color').hide();
    } else if ($colorPicker.val()) {
      $('#wp-sir-clear-bg-color').show();
    }
  });


  $(document).on('click', function (e) {
    if ($colorPicker.val()) {
      $('#wp-sir-clear-bg-color').show();
    }
  });


  $(document).on('change', '.wpSirSelectSize', function () {
    
    $(this).closest('tr').find('input[type="number"]').prop('disabled', !$(this).is(':checked'));
    $(this).closest('tr').find('.wp-sir-fit-mode').prop('disabled', !$(this).is(':checked'));
    if ($(this).closest('tr').find('.wp-sir-fit-mode').is(':checked')) {
      $(this).closest('tr').find('input[type="number"]').prop('disabled', true);
    }
    var isAllSizesSelected = $('.wpSirSelectSize:checked').length === $('.wpSirSelectSize').length;
    $('#wp-sir-toggle-all-sizes').prop('checked', isAllSizesSelected);

    // Check if current selection matches defaults
    const defaultSizes = $('#wp-sir-sizes-selector').data('defaults').split(',');
    const currentSizes = $('.wpSirSelectSize:checked').map(function() {
        return $(this).val();
    }).get();
    
    const hasChanges = defaultSizes.length !== currentSizes.length || 
        defaultSizes.some(size => !currentSizes.includes(size)) ||
        currentSizes.some(size => !defaultSizes.includes(size));
    
    // Show/hide reset button based on changes
    $('#wpsirResetDefaultSizes').toggle(hasChanges);
  });

  $('.wpSirSelectSize').each(function () {
   
    $(this).closest('tr').find('input[type=number]').prop('disabled', !$(this).is(':checked')).change();
    $(this).closest('tr').find('.wp-sir-fit-mode').prop('disabled', !$(this).is(':checked')).change();
    $(this).closest('tr').find('.wp-sir-fit-mode').each(function () {
      if ($(this).is(':checked')) {
        $(this).closest('tr').find('input[type=number]').prop('disabled', true);
      }
    });
  });

  $('#wp-sir-toggle-all-sizes').on('change', function () {
    var $toggle = $(this);
    $('.wpSirSelectSize').each(function(){
      if(! $(this).is(':disabled')){
        $(this).prop('checked', $toggle.is(':checked'));

      }
    });
    $('.wpSirSelectSize').change();
  });

  $('.wp-sir-fit-mode').on('change', function () {
    if ($(this).is(':checked') ) {
      $(this).closest('tr').find('.wp-sir-custom-dimensions').find('input').prop('disabled', true);
    } else {
      $(this).closest('tr').find('.wp-sir-custom-dimensions').find('input').prop('disabled', false);
    }
  });


  $(document).on('click', '#wp-sir-open-media-uploader', function (e) {
    var frame;

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: 'Select or Upload Watermark image',
      multiple: false,
    });

    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var $watermarkImageInput = $('input[name="wp_sir_settings[watermark_image]"]');
      $watermarkImageInput.val(attachment.id);
      $watermarkImageInput.data('size', { w: attachment.width, h: attachment.height });

      var $watermarkPreview = $('.wp-sir-watermark-preview-container');

      if ($watermarkPreview.find('img').length) {
        $watermarkPreview.find('img').attr('src', attachment.url);
      } else {
        $watermarkPreview.append('<img src="' + attachment.url + '"/>');
        $watermarkPreview.find('img').css('position', 'absolute');
      }

      var previewSize = { w: $watermarkPreview.width(), h: $watermarkPreview.height() };
      var h,w;
      var size = +$('.wp-sir-watermark-size').val();

      if (attachment.width >= attachment.height) {
        w = previewSize.w * size /100;
        if (w >= previewSize.w) {
          w = previewSize.w;
        }
        h = attachment.height * w / attachment.width;
      } else {
        h = previewSize.h * size / 100;
        if (h >= previewSize.h) {
          h = previewSize.h
        }
        w = attachment.width * h / attachment.height;
      }

      if(w >= previewSize.w) {
        w = previewSize.w;
        h = attachment.height * w / attachment.width;
      }

      if(h >= previewSize.h) {
        h = previewSize.h;
        w = attachment.width * h / attachment.height;
      }
      
      $watermarkPreview.find('img').css({ width: w + 'px', height: h + 'px' });
      $watermarkPreview.find('img').css({ opacity: +$('.wp-sir-watermark-opacity').val()/100 });
      $('#wp-sir-watermark-position').trigger('change');
    });

    frame.open();

  });


  // Handle watermark slider change.
  $('.wp-sir-watermark-size').on('input', function() {
    var $slider = $(this);
    var $previewImageContainer = $('.wp-sir-watermark-preview-container');
    var $watermark = $previewImageContainer.find('img');
    var value = $slider.val();
    
    // Update the size input value
    
    if (!$watermark.length) {
      return;
    }

    var previewSize = { 
      w: $previewImageContainer.width(), 
      h: $previewImageContainer.height() 
    };
    var watermarkSize = {
      w: $watermark.width(), 
      h: $watermark.height()
    };

    var watermarkNewWidth, watermarkNewHeight;

    if (watermarkSize.w >= watermarkSize.h) {
      watermarkNewWidth = previewSize.w * value / 100;
      if (watermarkNewWidth >= previewSize.w) {
        watermarkNewWidth = previewSize.w;
      }
      watermarkNewHeight = watermarkSize.h * watermarkNewWidth / watermarkSize.w;
    } else {
      watermarkNewHeight = previewSize.h * value / 100;
      if (watermarkNewHeight >= previewSize.h) {
        watermarkNewHeight = previewSize.h;
      }
      watermarkNewWidth = watermarkSize.w * watermarkNewHeight / watermarkSize.h;
    }

    if (watermarkNewWidth >= previewSize.w) {
      watermarkNewWidth = previewSize.w;
      watermarkNewHeight = watermarkSize.h * watermarkNewWidth / watermarkSize.w;
    }

    if (watermarkNewHeight >= previewSize.h) {
      watermarkNewHeight = previewSize.h;
      watermarkNewWidth = watermarkSize.w * watermarkNewHeight / watermarkSize.h;
    }

    $watermark.css({ 
      width: watermarkNewWidth + 'px', 
      height: watermarkNewHeight + 'px' 
    });

  });

  $(document).on('change', '#wp-sir-watermark-position', function () {
    setWatermarkPosition($(this));
  });


  $('.wp-sir-watermark-opacity').on('input', function() {
    var $slider = $(this);
    var $opacityInput = $('.' + $slider.data('input'));
    var value = $slider.val();
    var $watermark = $('.wp-sir-watermark-preview-container').find('img');

    // Update the opacity input value and display
    $opacityInput.val(value);
    $('.wp-sir-watermark-opacity-slider-handler').text(value + '%');

    // Update watermark opacity if it exists
    if ($watermark.length) {
      $watermark.css({ opacity: value / 100 });
    }
  });

  // Initialize opacity on page load
  $(document).ready(function() {
    $('.wp-sir-watermark-opacity').each(function() {
      $(this).trigger('input');
    });
  });

  function setWatermarkPosition($element) {
    var $img = $('.wp-sir-watermark-preview-container').find('img');
    var $offset_y = $('#wp-sir-watermark-offset-y');
    var $offset_x = $('#wp-sir-watermark-offset-x');
    var offset_x = parseInt($offset_x.val()) || 0;
    var offset_y = parseInt($offset_y.val()) || 0;
    
    // Reset any previous positioning
    $img.css({
      'top': '',
      'left': '',
      'right': '',
      'bottom': '',
      'transform': ''
    });

    // Enable both inputs by default
    $offset_x.prop('disabled', false);
    $offset_y.prop('disabled', false);

    switch ($element.val()) {
      case 'top-left':
        $img.css({
          'top': offset_y + 'px',
          'left': offset_x + 'px'
        });
        break;
      case 'top-right':
        $img.css({
          'top': offset_y + 'px',
          'right': offset_x + 'px'
        });
        break;
      case 'bottom-left':
        $img.css({
          'bottom': offset_y + 'px',
          'left': offset_x + 'px'
        });
        break;
      case 'bottom-right':
        $img.css({
          'bottom': offset_y + 'px',
          'right': offset_x + 'px'
        });
        break;
      case 'center':
        $img.css({
          'top': '50%',
          'left': '50%',
          'transform': 'translate(-50%, -50%)'
        });
        // Disable both offsets for center position
        $offset_x.prop('disabled', true);
        $offset_y.prop('disabled', true);
        break;
      case 'left':
        $img.css({
          'top': '50%',
          'left': offset_x + 'px',
          'transform': 'translateY(-50%)'
        });
        // Disable Y offset for left position
        $offset_y.prop('disabled', true);
        break;
      case 'right':
        $img.css({
          'top': '50%',
          'right': offset_x + 'px',
          'transform': 'translateY(-50%)'
        });
        // Disable Y offset for right position
        $offset_y.prop('disabled', true);
        break;
      case 'top':
        $img.css({
          'top': offset_y + 'px',
          'left': '50%',
          'transform': 'translateX(-50%)'
        });
        // Disable X offset for top position
        $offset_x.prop('disabled', true);
        break;
      case 'bottom':
        $img.css({
          'bottom': offset_y + 'px',
          'left': '50%',
          'transform': 'translateX(-50%)'
        });
        // Disable X offset for bottom position
        $offset_x.prop('disabled', true);
        break;
    }

    // Add visual feedback for disabled inputs
    $('.wp-sir-offset-input').each(function() {
      $(this).closest('.wp-sir-offset-field').toggleClass('disabled', $(this).prop('disabled'));
    });
  }

  setWatermarkPosition($('#wp-sir-watermark-position'));

  // Update offset handlers
  $(document).on('change keyup paste', '#wp-sir-watermark-offset-x, #wp-sir-watermark-offset-y', function() {
    setWatermarkPosition($('#wp-sir-watermark-position'));
  });

  $(document).on('change', '#wp-sir-enable-watermark', function () {
    if ($(this).is(':checked')) {
      $('.wp-sir-watermark-settings').css('display', 'flex');
    } else {
      $('.wp-sir-watermark-settings').css('display', 'none');

    }
  }).change();


  jQuery(document).ready(function($) {
   

    // Update tolerance value display
    $('.wp-sir-range-input').on('input', function() {
        $('#' + $(this).data('value-display')).text($(this).val() + '%');
    });

    $('#wp-sir-enable-trim').on('change', function() {
      $('.wp-sir-trim-advanced-settings').toggle($(this).prop('checked'));
  });
    
    $('.wp-sir-watermark-size').trigger('input');
    
    // Initialize tooltips
    if($.fn.tipTip)
    {
      $('.wp-sir-help-tip').tipTip({
        'attribute': 'title',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    });
    }

    // Handle sizes section toggle
    $('.wp-sir-toggle-sizes').on('click', function() {
        const $button = $(this);
        const $wrapper = $('#wp-sir-sizes-options');
        const isExpanded = $button.attr('aria-expanded') === 'true';
        
        $wrapper.slideToggle(200);
        $button.attr('aria-expanded', !isExpanded);
        
        // Update button text
        const $text = $button.find('.wp-sir-toggle-text');
        $text.text(isExpanded ? 'Customize image sizes' : 'Hide image sizes');
    });

    // Update sizes summary when selections change
    $('#wp-sir-sizes-selector input[type="checkbox"]').on('change', function() {
        const totalSizes = $('#wp-sir-sizes-selector .wpSirSelectSize').length;
        const selectedSizes = $('#wp-sir-sizes-selector .wpSirSelectSize:checked').length;
        
        $('.wp-sir-sizes-summary').text(
            `${selectedSizes} of ${totalSizes} size${totalSizes !== 1 ? 's' : ''} selected`
        );
    });
});

function createCurveControl() {
  const positions = [
    ['top-left', 'top', 'top-right'],
    ['left', 'center', 'right'], 
    ['bottom-left', 'bottom', 'bottom-right']
  ];

  let html = '<div class="wp-sir-curve-control">';
  positions.forEach((row, i) => {
    html += '<div class="wp-sir-curve-row">';
    row.forEach((pos) => {
      html += `<button type="button" class="wp-sir-curve-point" data-position="${pos}">
                <span class="screen-reader-text">${pos}</span>
              </button>`;
    });
    html += '</div>';
  });
  html += '</div>';

  // Insert after position dropdown
  $('#wp-sir-watermark-position').after(html);

  // Style for the curve control
  const style = `
    <style>
      .wp-sir-curve-control {
        display: inline-block;
        margin: 10px 0;
        padding: 10px;
        border: 1px solid #ddd;
        background: #fff;
      }
      .wp-sir-curve-row {
        display: flex;
        gap: 5px;
        margin-bottom: 5px;
      }
      .wp-sir-curve-row:last-child {
        margin-bottom: 0;
      }
      .wp-sir-curve-point {
        width: 24px;
        height: 24px;
        padding: 0;
        border: 1px solid #ddd;
        background: #f7f7f7;
        cursor: pointer;
        border-radius: 3px;
      }
      .wp-sir-curve-point:hover {
        background: #e9e9e9;
        border-color: #999;
      }
      .wp-sir-curve-point.active {
        background: #2271b1;
        border-color: #2271b1;
      }
      .screen-reader-text {
        position: absolute;
        margin: -1px;
        padding: 0;
        height: 1px;
        width: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        border: 0;
        word-wrap: normal !important;
      }
    </style>
  `;
  $('head').append(style);

  // Handle curve point clicks
  $('.wp-sir-curve-point').on('click', function() {
    const position = $(this).data('position');
    
    // Update active state
    $('.wp-sir-curve-point').removeClass('active');
    $(this).addClass('active');
    
    // Set position dropdown
    let dropdownValue;
    switch(position) {
      case 'top-left':
        dropdownValue = 'top-left';
        break;
      case 'top':
        dropdownValue = 'top';
        break;
      case 'top-right':
        dropdownValue = 'top-right';
        break;
      case 'left':
        dropdownValue = 'left';
        break;
      case 'center':
        dropdownValue = 'center';
        break;
      case 'right':
        dropdownValue = 'right';
        break;
      case 'bottom-left':
        dropdownValue = 'bottom-left';
        break;
      case 'bottom':
        dropdownValue = 'bottom';
        break;
      case 'bottom-right':
        dropdownValue = 'bottom-right';
        break;
    }
    
    $('#wp-sir-watermark-position').val(dropdownValue).trigger('change');
  });

  // Set initial active point based on dropdown
  function updateCurveFromDropdown() {
    const currentPosition = $('#wp-sir-watermark-position').val();
    $('.wp-sir-curve-point').removeClass('active');
    $(`.wp-sir-curve-point[data-position="${currentPosition}"]`).addClass('active');
  }

  updateCurveFromDropdown();
  $('#wp-sir-watermark-position').on('change', updateCurveFromDropdown);
}

// Initialize curve control when document is ready
$(document).ready(function() {
  createCurveControl();
});

$('.wp-sir-tabs div').on('click', function(e) {
  e.preventDefault();
  $('.wp-sir-tabs div').removeClass('active');
  $(this).addClass('active');
  if($(this).data('tab') === 'general'){
    console.log('general');
    $('.sir-settings-general >table>tbody>tr:not(.wp-sir-is-advanced)').removeClass('hidden');
    $('.sir-settings-general >table>tbody>tr.wp-sir-is-advanced').addClass('hidden');
  }else{
    $('.sir-settings-general >table>tbody>tr:not(.wp-sir-is-advanced)').addClass('hidden');
    $('.sir-settings-general >table>tbody>tr.wp-sir-is-advanced').removeClass('hidden');
  }
});

// Handle Regenerate Thumbnails plugin installation
$('#sir-install-rt').on('click', function(e) {
    e.preventDefault();
    var $button = $(this);
    var $spinner = $('<span class="spinner is-active" style="float:none;margin-top:0;margin-left:5px"></span>');
    
    $button.prop('disabled', true).after($spinner);

    $.ajax({
        url: wp_sir_object.ajax_url,
        type: 'POST',
        data: {
            action: 'wp_sir_install_rt',
            nonce: wp_sir_object.nonce
        },
        success: function(response) {
            $spinner.remove();
            if (response.success) {
                var adminUrl = wp_sir_object.admin_url + 'tools.php?page=regenerate-thumbnails';
                
                // Update step 1 to completed state
                var $step1 = $button.closest('.wp-sir-step');
                $step1.addClass('completed').removeClass('active');
                $step1.find('.wp-sir-step-content').html(`
                    <h4>Install Regenerate Thumbnails</h4>
                    <p><span class="dashicons dashicons-yes-alt"></span> Plugin installed successfully!</p>
                `);
                
                // Activate step 2
                var $step2 = $step1.next('.wp-sir-step');
                $step2.addClass('active');
                
                // Add activated class to style all steps
                $('.wp-sir-bulk-regenerate').addClass('rt-activated');
            } else {
                $button.prop('disabled', false);
                $button.after('<span class="dashicons dashicons-warning" style="color:#d63638; vertical-align: middle; margin-left: 10px;"></span> ' + response.data.message);
            }
        },
        error: function(xhr, status, error) {
            $spinner.remove();
            $button.prop('disabled', false);
            $button.after('<span class="dashicons dashicons-warning" style="color:#d63638; vertical-align: middle; margin-left: 10px;"></span> ' + error);
        }
    });
});

})(jQuery);



