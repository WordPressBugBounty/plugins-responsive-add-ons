jQuery(document).ready(function ($) {
  $('.upload-image-button').on('click', function (e) {
    e.preventDefault();

    const button = $(this);
    const targetInputId = button.data('target');
    const previewId = button.data('preview');

    const mediaUploader = wp.media({
      title: 'Select or Upload Image',
      button: { text: 'Use this image' },
      multiple: false
    });

    mediaUploader.on('select', function () {
      const attachment = mediaUploader.state().get('selection').first().toJSON();
      $('#' + targetInputId).val(attachment.url);
      const preview = $('#' + previewId);
      preview.attr('src', attachment.url).show();
    });

    mediaUploader.open();
  });

  // ✅ LIVE preview on manual input
  $('#resp_wl_theme_screenshot_url, #resp_wl_theme_icon_url').on('input paste', function () {
    const input = $(this);
    const url = input.val().trim();
    const previewId = input.attr('id') === 'resp_wl_theme_screenshot_url'
      ? 'theme_screenshot_preview'
      : 'theme_icon_preview';

    if (url.match(/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)$/i)) {
      $('#' + previewId).attr('src', url).show();
    } else {
      $('#' + previewId).hide();
    }
  });
});
