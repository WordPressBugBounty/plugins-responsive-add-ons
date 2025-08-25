<?php
/**
 * Provide a admin area view for the plugin
 *
 * Getting Started Settings Tab
 *
 * @link       https://cyberchimps.com/
 * @since      4.8.8
 *
 * @package responsive-add-ons
 */

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php
$wl_settings = get_option( 'rpro_elementor_settings' );
?>

	<?php
	if ( empty( $wl_settings ) || ( ! empty( $wl_settings ) && 'off' === $wl_settings['hide_wl_settings'] ) ) {
		?>
	<div id="responsive-theme-raddons-setting-wl-section">
		<div class="responsive-theme-single-setting-section">
			<div class="mb-2">
				<p class="responsive-theme-setting-title"><?php esc_html_e( 'White Label Settings', 'responsive-add-ons' ); ?></p>
				<label for="resp_wl_author_name" class="responsive-theme-setting-input-label"><?php esc_html_e( 'Author Name', 'responsive-add-ons' ); ?></label>
				<input type="text" class="form-control responsive-theme-setting-form-control" autocomplete="off" 
				<?php
				if ( ! empty( $wl_settings ) ) {
					?>
					value="<?php echo esc_attr( $wl_settings['plugin_author'] ); ?>"
					<?php
				}
				?>
				id="resp_wl_author_name">
			</div>
		</div>
		<div class="responsive-theme-single-setting-section">
			<div class="mb-2">
				<label for="resp_wl_website_url" class="responsive-theme-setting-input-label"><?php esc_html_e( 'Website URL', 'responsive-add-ons' ); ?></label>
				<input type="url" pattern="https?://.+" class="form-control responsive-theme-setting-form-control" autocomplete="off" 
				<?php
				if ( ! empty( $wl_settings ) ) {
					?>
					value="<?php echo esc_attr( $wl_settings['plugin_website_uri'] ); ?>"
					<?php
				}
				?>
				id="resp_wl_website_url">
			</div>
		</div>
		<div class="responsive-theme-single-setting-section">
			<div class="mb-2">
				<label for="resp_wl_theme_name" class="responsive-theme-setting-input-label"><?php esc_html_e( 'Theme Name', 'responsive-add-ons' ); ?></label>
				<input type="text" class="form-control responsive-theme-setting-form-control" autocomplete="off"
				<?php
				if ( ! empty( $wl_settings ) ) {
					?>
					value="<?php echo esc_attr( $wl_settings['theme_name'] ); ?>"
					<?php
				}
				?>
				id="resp_wl_theme_name">
			</div>
		</div>
		<div class="responsive-theme-single-setting-section">
			<div class="mb-2">
				<label for="resp_wl_theme_desc" class="responsive-theme-setting-input-label"><?php esc_html_e( 'Theme Description', 'responsive-add-ons' ); ?></label>
				<?php
				$theme_desc = '';
				if ( ! empty( $wl_settings ) ) {
					$theme_desc = $wl_settings['theme_desc'];
				}
				?>
				<textarea type="text" class="form-control responsive-theme-setting-form-control" id="resp_wl_theme_desc"><?php echo esc_html( $theme_desc ); ?></textarea>
			</div>
		</div>
		<div class="responsive-theme-single-setting-section">
  <div class="mb-2">
    <label for="resp_wl_theme_screenshot_url" class="responsive-theme-setting-input-label">
      <?php esc_html_e( 'Theme Screenshot', 'responsive-add-ons' ); ?>
    </label>

    <div class="screenshot-preview mb-2">
      <img id="theme_screenshot_preview"
           src="<?php echo esc_url( $wl_settings['theme_screenshot_url'] ?? '' ); ?>"
           style="max-width: 200px; <?php echo empty( $wl_settings['theme_screenshot_url'] ) ? 'display:none;' : ''; ?>" />
    </div>

    <div class="input-group" style="width: 60%;">
      <input type="url"
             id="resp_wl_theme_screenshot_url"
             class="form-control responsive-theme-setting-form-control"
             placeholder="<?php esc_attr_e( 'Enter image URL...', 'responsive-add-ons' ); ?>"
             value="<?php echo esc_url( $wl_settings['theme_screenshot_url'] ?? '' ); ?>">

      <button class="btn btn-secondary upload-image-button"
              type="button"
              data-target="resp_wl_theme_screenshot_url"
              data-preview="theme_screenshot_preview">
        <?php esc_html_e( 'Upload', 'responsive-add-ons' ); ?>
      </button>
    </div>

    <p class="mt-2 responsive-theme-setting-form-control-hint">
      <?php esc_html_e( 'The recommended image size is 1200px wide and 900px tall.', 'responsive-add-ons' ); ?>
    </p>
  </div>
</div>
<div class="responsive-theme-single-setting-section">
  <div class="mb-2">
    <label for="resp_wl_theme_icon_url" class="responsive-theme-setting-input-label">
      <?php esc_html_e( 'Theme Icon', 'responsive-add-ons' ); ?>
    </label>

    <div class="icon-preview mb-2">
      <img id="theme_icon_preview"
           src="<?php echo esc_url( $wl_settings['theme_icon_url'] ?? '' ); ?>"
           style="max-width: 100px; <?php echo empty( $wl_settings['theme_icon_url'] ) ? 'display:none;' : ''; ?>" />
    </div>

    <div class="input-group" style="width: 60%;">
      <input type="text"
             id="resp_wl_theme_icon_url"
             class="form-control responsive-theme-setting-form-control"
             placeholder="<?php esc_attr_e( 'Enter icon URL...', 'responsive-add-ons' ); ?>"
             value="<?php echo esc_url( $wl_settings['theme_icon_url'] ?? '' ); ?>">

      <button class="btn btn-secondary upload-image-button"
              type="button"
              data-target="resp_wl_theme_icon_url"
              data-preview="theme_icon_preview">
        <?php esc_html_e( 'Upload', 'responsive-add-ons' ); ?>
      </button>
    </div>

    <p class="mt-2 responsive-theme-setting-form-control-hint">
      <?php esc_html_e( 'The recommended icon should have some background to get adjusted properly.', 'responsive-add-ons' ); ?>
    </p>
  </div>
</div>


		<div class="responsive-theme-single-setting-section">
			<div>
				<input type="checkbox" name="resp_wl_hide_settings" id="resp_wl_hide_settings"  />
				<label class="responsive-theme-setting-checkbox-label" for="resp_wl_hide_settings"><?php esc_html_e( 'Hide White Label Settings', 'responsive-add-ons' ); ?></label>
			</div>
			<p class="responsive-theme-setting-note"><strong><?php esc_html_e( 'Note', 'responsive-add-ons' ); ?></strong> : <?php esc_html_e( 'Enable this option to hide White Label settings. Re-activate the Responsive Starter Templates to enable this settings tab again.', 'responsive-add-ons' ); ?></p>
		</div>
		<div class="responsive-theme-single-setting-section">
			<button id="resp-theme-wl-settings-submit" class="button button-primary responsive-theme-setting-primary-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'white_label_settings' ) ); ?>"><?php esc_html_e( 'Save Changes', 'responsive-add-ons' ); ?></button>
		</div>
	</div>
		<?php
	}
	?>
