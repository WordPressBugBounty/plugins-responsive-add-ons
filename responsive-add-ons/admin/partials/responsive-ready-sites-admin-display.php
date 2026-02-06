<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://cyberchimps.com/
 * @since      2.6.6
 *
 * @package    Responsive Ready Sites
 */

?>

<?php
require_once RESPONSIVE_ADDONS_DIR . 'includes/class-responsive-add-ons-app-auth.php';
require_once RESPONSIVE_ADDONS_DIR . 'includes/settings/class-responsive-add-ons-settings.php';
$cc_app_auth                = new Responsive_Add_Ons_App_Auth();
$settings                   = get_option( 'reads_app_settings' );
$responsive_addons_settings = new Responsive_Add_Ons_Settings();
$plan_status                = $responsive_addons_settings->get_plan();
$responsive_sites_header_after_connection_success = false;
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="responsive-ready-site-preview"></div>
<div id="responsive-ready-site-pages-preview"></div>
<div id="responsive-ready-sites-import-options"></div>
<div id="responsive-ready-sites-import-progress"></div>
<div id="responsive-ready-sites-page-import-progress"></div>
<div id="responsive-ready-sites-import-done-congrats"></div>
<div id="responsive-ready-sites-admin-page">
	<?php
	if ( get_transient( 'responsive_ready_sites_display_connect_success' ) ) :
		$responsive_sites_header_after_connection_success = true
		?>
		<div class="responsive-templates-app-auth-sucess-msg">
			<p class="auth-success-msg"><span class="auth-success-msg">
			<?php esc_html_e( 'Congratulations! Your website is now connected to Cyberchimps Responsive. You can start importing Templates.', 'responsive-add-ons' ); ?>
			</span><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'responsive-add-ons' ); ?></span></button></p>
		</div>
		<?php delete_transient( 'responsive_ready_sites_display_connect_success' ); ?>
	<?php endif; ?>
	<div class="<?php echo esc_attr( 'responsive-sites-header' . ( $responsive_sites_header_after_connection_success ? ' responsive-sites-header-after-connection-success' : '' ) ); ?>" id="responsive-sites-header">
		<span class="ready-site-list-title"><?php esc_html_e( 'Responsive Starter Templates', 'responsive-add-ons' ); ?></span>
	</div>
	<div id="responsive-addons-user-consent-message">
		<div class="responsive-addons-consent-wrapper">
			<!-- Consent Toggle -->
			<label class="switch">
				<input type="checkbox" id="responsive-addons-consent-toggle" <?php echo 'yes' === get_option( 'responsive_addons_contribution_consent', 'yes' ) ? 'checked' : ''; ?>>
				<span class="slider round"></span>
			</label>
			<div class="responsive-addons-consent-text">
				<h3><?php esc_html_e( 'Contribute to Responsive Starter Templates', 'responsive-addons' ); ?></h3>
				<p><?php esc_html_e( "Allow Responsive Starter Templates to collect non-sensitive data like templates used and website's PHP version to improve features and fix issues faster.", 'responsive-addons' ); ?></p>
			</div>
		</div>
	</div>
	<?php

	$responsive_sites_header_after_connection_success = false;
	$business_subcategories                           = array(
		array(
			'name' => 'Advertising & Marketing',
			'slug' => 'advertising-marketing',
		),
		array(
			'name' => 'Real Estate & Construction',
			'slug' => 'real-estate-construction',
		),
		array(
			'name' => 'Cars & Automotive',
			'slug' => 'cars-automotive',
		),
		array(
			'name' => 'Consulting & Coaching',
			'slug' => 'consulting-coaching',
		),
		array(
			'name' => 'Finance & Law',
			'slug' => 'finance-law',
		),
		array(
			'name' => 'Farming & Gardening',
			'slug' => 'farming-gardening',
		),
		array(
			'name' => 'Transport',
			'slug' => 'transport',
		),
		array(
			'name' => 'Pet & Animal',
			'slug' => 'pet-animal',
		),
		array(
			'name' => 'Architecture & Interior',
			'slug' => 'architecture-interior',
		),
		array(
			'name' => 'Technology & Apps',
			'slug' => 'technology-apps',
		),
	);
	$health_subcategories                             = array(
		array(
			'name' => 'Doctor',
			'slug' => 'doctor',
		),
		array(
			'name' => 'Hospital',
			'slug' => 'hospital',
		),
		array(
			'name' => 'Dentist & Dental',
			'slug' => 'dentist-dental',
		),
		array(
			'name' => 'Medical & Clinic',
			'slug' => 'medical-clinic',
		),
		array(
			'name' => 'Therapist & Psychologist',
			'slug' => 'therapist-psychologist',
		),
		array(
			'name' => 'Gym & Fitness',
			'slug' => 'gym-fitness',
		),
		array(
			'name' => 'Yoga',
			'slug' => 'yoga',
		),
	);
	$fashion_subcategories                            = array(
		array(
			'name' => 'Fashion',
			'slug' => 'fashion',
		),
		array(
			'name' => 'Shoes & Footwear',
			'slug' => 'shoes-footwear',
		),
		array(
			'name' => 'Salon & Spa',
			'slug' => 'salon-spa',
		),
		array(
			'name' => 'Makeup & Cosmetics',
			'slug' => 'makeup-cosmetics',
		),
	);
	$restaurant_subcategories                         = array(
		array(
			'name' => 'Food',
			'slug' => 'food',
		),
		array(
			'name' => 'Cafe & Bakery',
			'slug' => 'cafe-bakery',
		),
		array(
			'name' => 'Bar & Club',
			'slug' => 'bar-club',
		),
		array(
			'name' => 'Restaurant',
			'slug' => 'restaurant',
		),
		array(
			'name' => 'Catering & Chef',
			'slug' => 'catering-chef',
		),
	);
	$travel_subcategories                             = array(
		array(
			'name' => 'Apartments & Hostels',
			'slug' => 'apartments-hostels',
		),
		array(
			'name' => "Hotels & BB's",
			'slug' => 'hotels-b&bs',
		),
	);
	$services_subcategories                           = array(
		array(
			'name' => 'Accounting',
			'slug' => 'accounting',
		),
		array(
			'name' => 'Insurance',
			'slug' => 'insurance',
		),
		array(
			'name' => 'Roofing',
			'slug' => 'roofing',
		),
		array(
			'name' => 'Cleaning',
			'slug' => 'cleaning',
		),
		array(
			'name' => 'Electrician',
			'slug' => 'electrician',
		),
		array(
			'name' => 'Plumbing',
			'slug' => 'plumbing',
		),
		array(
			'name' => 'Courier',
			'slug' => 'courier',
		),
		array(
			'name' => 'Author & Writer',
			'slug' => 'author-writer',
		),
		array(
			'name' => 'Landscaping',
			'slug' => 'landscaping',
		),
		array(
			'name' => 'Hair & Stylist',
			'slug' => 'hair-stylist',
		),
	);
	$creative_subcategories                           = array(
		array(
			'name' => 'Photography',
			'slug' => 'photography',
		),
		array(
			'name' => 'Artist',
			'slug' => 'artist',
		),
		array(
			'name' => 'Musician & Singer',
			'slug' => 'musician-singer',
		),
		array(
			'name' => 'Travel',
			'slug' => 'travel-documentary',
		),
		array(
			'name' => 'Art & Illustration',
			'slug' => 'art-illustration',
		),
		array(
			'name' => 'Designing',
			'slug' => 'designing',
		),
		array(
			'name' => 'Graphic & Web',
			'slug' => 'graphic-web',
		),
		array(
			'name' => 'Podcast',
			'slug' => 'podcast',
		),
		array(
			'name' => 'Music & Industry',
			'slug' => 'music-industry',
		),
		array(
			'name' => 'Film & TV',
			'slug' => 'film-tv',
		),

	);
	$community_subcategories = array(
		array(
			'name' => 'Church',
			'slug' => 'church',
		),
		array(
			'name' => 'Charity & NonProfit',
			'slug' => 'charity-nonprofit',
		),
		array(
			'name' => 'Schools & Universities',
			'slug' => 'schools-universities',
		),
		array(
			'name' => 'Preschool & Kindergarten',
			'slug' => 'preschool-kindergarten',
		),
		array(
			'name' => 'Online Education',
			'slug' => 'online-education',
		),
		array(
			'name' => 'Wedding',
			'slug' => 'wedding',
		),
		array(
			'name' => 'Holidays & Celebrations',
			'slug' => 'holidays-celebrations',
		),
		array(
			'name' => 'Conferences & Meetups',
			'slug' => 'conferences-meetups',
		),
	);
	$ecommerce_subcategories = array(
		array(
			'name' => 'Book Store',
			'slug' => 'book-store',
		),
		array(
			'name' => 'Beauty & Wellness',
			'slug' => 'beauty-wellness',
		),
		array(
			'name' => 'Fashion & Clothing Store',
			'slug' => 'fashion-clothing-store',
		),
		array(
			'name' => 'Home & Furniture',
			'slug' => 'home-furniture',
		),
		array(
			'name' => 'Electronics',
			'slug' => 'electronics',
		),
		array(
			'name' => 'Jewellery & Accessories',
			'slug' => 'jewellery-accessories',
		),
		array(
			'name' => 'Sports & Outdoors',
			'slug' => 'sports-outdoors',
		),
		array(
			'name' => 'Membership',
			'slug' => 'membership',
		),
		array(
			'name' => 'Online Course & LMS',
			'slug' => 'online-course-lms',
		),
		array(
			'name' => 'Food Ordering',
			'slug' => 'food-ordering',
		),
	);
	$blog_subcategories      = array(
		array(
			'name' => 'Personal & Blog',
			'slug' => 'personal-blog',
		),
		array(
			'name' => 'News',
			'slug' => 'news',
		),
		array(
			'name' => 'Landing Page',
			'slug' => 'landing-page',
		),
		array(
			'name' => 'Magazine',
			'slug' => 'magazine',
		),
		array(
			'name' => 'Portfolios',
			'slug' => 'portfolios',
		),
		array(
			'name' => "Resumes & CV's",
			'slug' => 'resumes-cvs',
		),
		array(
			'name' => 'Multipurpose',
			'slug' => 'multipurpose',
		),
		array(
			'name' => 'One Page',
			'slug' => 'one-page',
		),
	);
	$expand_more_svg         = '
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<mask id="mask0_5372_12373" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20">
							<rect width="20" height="20" fill="#D9D9D9"/>
							</mask>
							<g mask="url(#mask0_5372_12373)">
								<path d="M13.825 7.15834L10 10.975L6.175 7.15834L5 8.33334L10 13.3333L15 8.33334L13.825 7.15834Z" fill="#4B5563"/>
							</g>
						</svg>';

	$svg_args = array(
		'svg'   => array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-labelledby' => true,
			'role'            => true,
			'xmlns'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true, // <= Must be lower case!
		),
		'g'     => array( 'fill' => true ),
		'title' => array( 'title' => true ),
		'path'  => array(
			'd'    => true,
			'fill' => true,
		),
	);
	?>
	<div id="responsive-sites__category-filter" class="dropdown-check-list" tabindex="100">
		<span class="responsive-sites__category-filter-anchor" data-slug="" style="display: none;"><?php esc_html_e( 'All', 'responsive-add-ons' ); ?></span>
		<div id="rst-category-parent">
			<div class="rst-business-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="business">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Business', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $business_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-health-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="health-wellness">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Health', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $health_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-health-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="fashion-style">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Fashion', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $fashion_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-restaurant-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="restaurants-food">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Restaurants', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $restaurant_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-travel-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="travel-tourism">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Travel', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $travel_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-services-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="services">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Services', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $services_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-creative-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="creative">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Creative', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $creative_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-community-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="community">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Community', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $community_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-ecommerce-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="ecommerce">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Ecommerce', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $ecommerce_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="rst-blog-category-group rst-menu-parent-category-group">
				<div class="rst-menu-parent-category responsive-sites_category" data-slug="blog">
					<span class="rst-menu-parent-category-title">
						<span><?php esc_html_e( 'Blog', 'responsive-add-ons' ); ?></span><?php echo wp_kses( $expand_more_svg, $svg_args ); ?>
					</span>
				</div>
				<div class="rst-menu-child-category-group">
					<?php
					foreach ( $blog_subcategories as $key => $value ) {
						?>
						<div class="rst-menu-child-category responsive-sites_category" data-slug="<?php echo esc_attr( $value['slug'] ); ?>"><span><?php echo esc_html( $value['name'] ); ?></span></div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<div id="responsive-sites__type-filter" class="dropdown-check-list" tabindex="100">
			<div id="responsive-sites__type-filter-selected">
				<span class="responsive-sites__type-filter-anchor" data-slug="">
					<?php esc_html_e( 'All', 'responsive-add-ons' ); ?>
				</span>
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11.06 5.72666L8 8.77999L4.94 5.72666L4 6.66666L8 10.6667L12 6.66666L11.06 5.72666Z" fill="#4B5563"/>
				</svg>
			</div>
			<ul class="responsive-sites__type-filter-items">
				<li class="responsive-sites__filter-wrap-checkbox first-wrap" data-slug="all">
					<label>
						<input id="radio-all" type="radio" name="responsive-sites-radio" class="checkbox active" value="" checked /><?php esc_html_e( 'All', 'responsive-add-ons' ); ?>
					</label>
				</li>
				<li class="responsive-sites__filter-wrap-checkbox" data-slug="free">
					<label>
						<input id="radio-free" type="radio" name="responsive-sites-radio" class="checkbox" value="free" /><?php esc_html_e( 'Free', 'responsive-add-ons' ); ?>
					</label>
				</li>
				<li class="responsive-sites__filter-wrap-checkbox" data-slug="premium">
					<label>
						<input id="radio-premium" type="radio" name="responsive-sites-radio" class="checkbox" value="premium" /><?php esc_html_e( 'Premium', 'responsive-add-ons' ); ?>
					</label>
				</li>
			</ul>
		</div>
	</div>
	<div class="responsive-sites-separator"></div>
	<div class="theme-browser rendered">
		<div id="responsive-sites-loading" class="themes wp-clearfix">
			<div class="responsive-sites-loading-skeleton">
				<div class="responsive-sites-loading-skeleton-image"></div>
				<div class="responsive-sites-loading-skeleton-content">
					<div class="responsive-sites-loading-skeleton-line responsive-sites-loading-skeleton-title"></div>
				</div>
			</div>
			<div class="responsive-sites-loading-skeleton">
				<div class="responsive-sites-loading-skeleton-image"></div>
				<div class="responsive-sites-loading-skeleton-content">
					<div class="responsive-sites-loading-skeleton-line responsive-sites-loading-skeleton-title"></div>
				</div>
			</div>
			<div class="responsive-sites-loading-skeleton">
				<div class="responsive-sites-loading-skeleton-image"></div>
				<div class="responsive-sites-loading-skeleton-content">
					<div class="responsive-sites-loading-skeleton-line responsive-sites-loading-skeleton-title"></div>
				</div>
			</div>
			<div class="responsive-sites-loading-skeleton">
				<div class="responsive-sites-loading-skeleton-image"></div>
				<div class="responsive-sites-loading-skeleton-content">
					<div class="responsive-sites-loading-skeleton-line responsive-sites-loading-skeleton-title"></div>
				</div>
			</div>
		</div>
		<!-- Overlay -->
		<div id="responsive-sites-sync-overlay" class="responsive-sites-sync-overlay">
			<div class="responsive-sites-sync-modal">
				<div class="responsive-sites-sync-header">
					<span class="responsive-sites-sync-title"><?php esc_html_e( 'Syncing Templates Library...', 'responsive-add-ons' ); ?></span>
					<span class="sync-icon">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="rotate"><path d="M17.5 1.6665V6.6665H12.5" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.5 10C2.50132 8.55277 2.92133 7.13682 3.70938 5.92295C4.49743 4.70909 5.61985 3.74914 6.94126 3.15891C8.26267 2.56868 9.72662 2.37338 11.1566 2.59655C12.5865 2.81973 13.9213 3.45185 15 4.41667L17.5 6.66667" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.5 18.3335V13.3335H7.5" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.5 10C17.4987 11.4472 17.0787 12.8632 16.2906 14.0771C15.5026 15.2909 14.3802 16.2509 13.0587 16.8411C11.7373 17.4313 10.2734 17.6266 8.84345 17.4035C7.41352 17.1803 6.07871 16.5482 5 15.5833L2.5 13.3333" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</span>
				</div>
				<p class="responsive-sites-sync-message"><?php esc_html_e( 'Updating the library to include all the latest templates.', 'responsive-add-ons' ); ?></p>
			</div>
		</div>
		<div id="responsive-sites" class="themes wp-clearfix"></div>
	</div>
</div>

<?php
/**
 * TMPL - List
 */
?>

<script type="text/template" id="tmpl-responsive-sites-list">
	<# for ( key in data ) { #>
		<div class="theme inactive ra-site-single {{ data[ key ].status }} {{ data[ key ].class }}" tabindex="0" aria-describedby="responsive-theme-action responsive-theme-name"
			data-demo-id="{{{ data[ key ].id }}}"
			data-demo-url="{{{ data[ key ]['site_url'] }}}"
			data-demo-slug="{{{  data[ key ].slug }}}"
			data-demo-name="{{{  data[ key ].title.rendered }}}"
			data-active-site="{{{  data.active_site }}}"
			data-demo-type="{{{ data[ key ].demo_type }}}"
			data-wpforms-path="{{{ data[ key ].wpforms_path }}}"
			data-allow-pages="{{{ data[ key ].allow_pages }}}"
			data-check_plugins_installed="{{{ data[ key ].check_plugins_installed }}}"
			data-screenshot="{{{ data[ key ]['featured_image_url'] }}}"
			data-required-plugins="{{ JSON.stringify(data[ key ]['required_plugins']) }}"
			data-pages="{{ JSON.stringify(data[ key ]['pages'] )}}"
			data-required-pro-plugins="{{ JSON.stringify(data[ key ]['required_pro_plugins']) }}"
			data-require-flex-box-container="{{{data[key].container_template}}}"
			data-favorite-status="{{{ data[ key ].favorite_status }}}"
			data-page-builder="{{{ data[ key ].page_builder }}}">
			<input type="hidden" class="site_options_data" value="{{ JSON.stringify(data[ key ][ 'site_options_data' ]) }}">
		<div class="inner">
					<span class="site-preview" data-href="{{ data[ key ]['responsive-site-url'] }}?TB_iframe=true&width=600&height=550" data-title="data title">
						<div class="theme-screenshot" style="background-image: url('{{ data[ key ]['featured_image_url'] }}');"></div>
					</span>
			<span class="demo-type {{{ data[ key ].demo_type }}}">{{{ data[ key ].demo_type }}}</span>
			<# if (data[ key ].slug === data.active_site ) { #>
				<span class="current_active_site"><?php esc_html_e( 'Currently Active', 'responsive-add-ons' ); ?></span>
			<# } #>
			<div class="theme-id-container">
				<h3 class="theme-name" id="responsive-theme-name">{{{ data[ key ].title.rendered }}}</h3>
					<div class="responsive-single-site-favorite-div">
						<div id="rst-favorite-btn" class="rst-favorite-btn {{{ data[ key ].favorite_status }}}" data="false">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M6.245 2.50498C4.975 2.43748 3.70125 2.86248 2.76375 3.79998C0.887495 5.67998 1.08375 8.83498 3.09125 10.845L3.7325 11.4862L9.56 17.3187C9.67715 17.4355 9.83582 17.5011 10.0012 17.5011C10.1667 17.5011 10.3253 17.4355 10.4425 17.3187L16.2675 11.4862L16.9087 10.845C18.9162 8.83498 19.1112 5.67998 17.2337 3.80123C15.3575 1.92248 12.2087 2.12248 10.2025 4.13123L10 4.33373L9.79749 4.13123C8.79375 3.12498 7.51625 2.57248 6.245 2.50498Z" fill="#9CA3AF"/>
							</svg>
							<# if ( data[ key ].favorite_status === 'active' ) { #>
								<span id="rst-favorite-btn-tooltip-text" class="tooltip-text favourite"><?php esc_html_e( 'Remove from favourites', 'responsive-add-ons' ); ?> </span>
							<# } else { #>
								<span id="rst-favorite-btn-tooltip-text" class="tooltip-text"><?php esc_html_e( 'Add to favourites', 'responsive-add-ons' ); ?> </span>
							<# } #>
						</div>
					</div>
			</div>
			<div class="guided-overlay step-three" id="step-three">
				<p class="guide-text">Click the "Preview" button to view the website template and click import.</p>
				<div class="guided-overlay-buttons">
					<button id="step-three-previous">Previous</button>
					<button id="step-three-finish" class="finish-tour">Finish Tour</button>
				</div>
			</div>
		</div>
	</div>
	<# } #>
</script>
<?php
/** Site suggestion block */
?>
<script type="text/template" id="tmpl-responsive-sites-suggestions">
	<div class="responsive-sites-suggestions">
		<div class="inner">
			<h3><?php esc_html_e( 'Sorry No Results Found.', 'responsive-add-ons' ); ?></h3>
			<div class="content">
				<div class="description">
					<p>
						<?php
						esc_html_e( 'Can\'t find a Responsive Starter Template that suits your purpose ?' );
						?>
						<br><a target="_blank" href="mailto:support@cyberchimps.com?Subject=New%20Site%20Suggestion">
						<?php
						esc_html_e( 'Suggest A Site' )
						?>
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</script>
<?php
/** Single Demo Preview */
?>

<script type="text/template" id="tmpl-responsive-ready-site-preview">
	<div class="responsive-ready-site-preview theme-install-overlay wp-full-overlay collapsed"
		data-demo-id="{{{data.id}}}"
		data-demo-url="{{{data.demo_url}}}"
		data-demo-api="{{{data.demo_api}}}"
		data-demo-name="{{{data.name}}}"
		data-active-site="{{{data.active_site}}}"
		data-demo-type="{{{data.demo_type}}}"
		data-wpforms-path="{{{data.wpforms_path}}}"
		data-check_plugins_installed="{{{data.check_plugins_installed}}}"
		data-demo-slug="{{{data.slug}}}"
		data-screenshot="{{{data.screenshot}}}"
		data-required-plugins="{{data.required_plugins}}"
		data-required-pro-plugins="{{data.required_pro_plugins}}"
		data-require-flexbox-container="{{data.require_flexbox_container}}"
		data-pages="{{data.pages}}"
		data-app-auth="{{data.has_app_auth}}"
		data-page-builder="{{data.page_builder}}">
		<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
		<div class="wp-full-overlay-header">
			<div class="responsive-single-demo-preview">
				<div class="responsive-sites-demo-details">
						<div class="responsive-sites-demo-preview-logo-wrap">
							<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
						</div>
					<span class="responsive-site-demo-name">{{data.name}}</span>
					<span class="responsive-site-demo-type responsive-site-demo-type-{{{data.demo_type}}}">{{data.demo_type}}</span>
				</div>
				<div class="responsive-addons-import-btns">
					<button class="responsive-addons-go-back-btn responsive-addons"><span class="responsive-addons-go-back-btn-text"><?php esc_html_e( 'Go Back', 'responsive-add-ons' ); ?></span></button>
					<button id="responsive-addons-demo-import-options" class="button button-primary responsive-addons responsive-addons-demo-import-options"><?php esc_html_e( 'Import Site', 'responsive-add-ons' ); ?></button>
					<# if ( data.allow_pages ) { #>
					<button class="button button-primary responsive-addons responsive-addons-page-import-options"><?php esc_html_e( 'Import Template', 'responsive-add-ons' ); ?></button>
					<# } #>
				</div>
				<div class="responsive-addons-modal responsive-addons-app-connect-modal" style="display: none;">
					<div class="responsive-addons-app-modal-content">
						<span id="responsive-addons-app-modal-close"><img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/modal-close.svg' ); ?>"></span>
						<h2><?php esc_html_e( 'Connect Your Website to Cyberchimps Responsive', 'responsive-add-ons' ); ?></h2>
						<p><?php esc_html_e( 'Create a free account to connect with Cyberchimps Responsive.', 'responsive-add-ons' ); ?></p>
						<button type="button" class="rst-start-auth rst-start-auth-new"><?php esc_html_e( 'New? Create a free account', 'responsive-add-ons' ); ?><span id="loader"></span></button>
						<p class=""><?php esc_html_e( 'Already have an account on CyberChimps Responsive? ', 'responsive-add-ons' ); ?><span class="rst-start-auth rst-start-auth-exist"><?php esc_html_e( 'Connect your existing account', 'responsive-add-ons' ); ?><span id="loader"></span></span></p>
					</div>
				</div>

				<!-- Unlock Premium Template Access Popup -->
				<div class="responsive-addons-modal responsive-addons-app-unlock-access-modal" style="display: none;">
					<div class="responsive-addons-app-unlock-access-modal-content">
						<div id="responsive-addons-app-unlock-template-header">
							<p class="responsive-addons-app-unlock-template-heading"><?php esc_html_e( 'Premium template requires a Personal plan subscription or higher!', 'responsive-add-ons' ); ?></p>
							<span id="responsive-addons-app-unlock-template-modal-close"><img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/unlock-template-close-btn.svg' ); ?>"></span>
						</div>
						<div class="responsive-addons-app-unlock-access-modal-body">
							<h2><?php esc_html_e( 'Connect Your Website to Cyberchimps Responsive', 'responsive-add-ons' ); ?></h2>
							<# if ( 'free' === data.demo_type ) { #>
								<p><?php esc_html_e( 'Create a free account to connect with Cyberchimps Responsive.', 'responsive-add-ons' ) ?></p>
							<# } else { #>
								<p><?php esc_html_e( 'Create an account to connect with Cyberchimps Responsive.', 'responsive-add-ons' ) ?></p>
							<# } #>
							<button type="button" class="raddons-upgrade-the-plan"><?php esc_html_e( 'Unlock Premium Template Access at just $1.97/month', 'responsive-add-ons' ); ?><span style="margin-left: 8px" class="dashicons dashicons-lock"></span><span id="loader"></span></button>
							<p style="color:#000000; padding-bottom: 15px;"class=""><?php esc_html_e( 'Already have an account on CyberChimps Responsive? ', 'responsive-add-ons' ); ?><span class="rst-start-auth rst-start-auth-exist"><?php esc_html_e( 'Connect your existing account', 'responsive-add-ons' ); ?><span id="loader"></span></span></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="wp-full-overlay-main">
			<iframe src="{{{data.demo_url}}}" title="<?php esc_attr_e( 'Preview', 'responsive-add-ons' ); ?>"></iframe>
		</div>
	</div>
</script>

<?php
/** Theme Import Options Page */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-progress-page">
	<div class="responsive-ready-sites-advanced-options-wrap ready-site-import-progress-step wp-full-overlay collapsed"
			data-demo-id="{{{data.id}}}"
			data-demo-url="{{{data.demo_url}}}"
			data-demo-api="{{{data.demo_api}}}"
			data-demo-name="{{{data.name}}}"
			data-demo-type="{{{data.demo_type}}}"
			data-demo-slug="{{{data.slug}}}"
			data-screenshot="{{{data.screenshot}}}"
			data-required-plugins="{{data.required_plugins}}"
			data-pages="{{data.pages}}"
			data-require-flexbox-container="{{data.require_flexbox_container}}"
			data-required-pro-plugins="{{data.required_pro_plugins}}">
			<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
			<input type="hidden" class="demo_site_id" value="{{{ data.id }}}">
			<div class="wp-full-overlay-header">
				<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
					<div class="responsive-addons-import-btns">
						<a href="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ); ?>" class="rst-exit-to-dashboard">
							<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M28.5 2.5L2.5 28.5M28.5 28.5L2.5 2.50002" stroke="black" stroke-width="4" stroke-linecap="round"/>
							</svg>
						</a>
					</div>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<div class="responsive-ready-sites-import-progress-container">
					<div class="site-import-options">
						<div class="responsive-ready-sites-advanced-options">
						<h2 class="ready-sites-import-progress-title"><?php esc_html_e( 'We are Building your Website', 'responsive-add-ons' ); ?></h2>
							<div class="sites-import-process-errors" style="display: none">
								<div class="import-process-error">
									<div class="current-importing-status-error-title"></div>
								</div>
							</div>
							<div class="ready-sites-import-progress-info">
							<div class="ready-sites-import-progress-info-text"><?php echo esc_html_e( 'Pre-Checking and Starting Up Import Process', 'responsive-add-ons' ); ?></div>
							<div class="ready-sites-import-progress-info-percent"><?php echo esc_html_e( '0%', 'responsive-add-ons' ); ?></div>
							</div>
							<div class="ready-sites-import-progress-bar-wrap">
								<div class="ready-sites-import-progress-bar-bg">
									<div class="ready-sites-import-progress-bar"></div>
								</div>
								<div class="ready-sites-import-progress-gap"><span></span><span></span><span></span></div>
							</div>
						</div>
						<div class="ready-sites-import-progress-img-wrapper">
							<div class="ready-sites-import-progress-img">
								<img class="ready-sites-import-progress-image" src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/import-progress.gif' ); ?>">
							</div>
						</div>
					</div>
				</div>
				<div class="result_preview" style="display: none">
				</div>
			</div>
		</div>
</script>


<script type="text/template" id="tmpl-responsive-ready-sites-import-options-page">
	<div class="responsive-ready-sites-advanced-options-wrap wp-full-overlay collapsed"
			data-demo-id="{{{data.id}}}"
			data-demo-url="{{{data.demo_url}}}"
			data-demo-api="{{{data.demo_api}}}"
			data-demo-name="{{{data.name}}}"
			data-demo-type="{{{data.demo_type}}}"
			data-demo-slug="{{{data.slug}}}"
			data-screenshot="{{{data.screenshot}}}"
			data-required-plugins="{{data.required_plugins}}"
			data-pages="{{data.pages}}"
			data-require-flexbox-container="{{data.require_flexbox_container}}"
			data-required-pro-plugins="{{data.required_pro_plugins}}"
			data-demo-name="{{data.name}}">
			<input type="hidden" class="responsive-site-options" value="{{data.site_options_data}}" >
			<input type="hidden" class="demo_site_id" value="{{{ data.id }}}">
			<input type="hidden" class="demo_page_builder" value="{{{ data.page_builder }}}">
			<input type="hidden" class="demo_site_name" value="{{{ data.name }}}">
			<div class="wp-full-overlay-header">
				<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
					<div class="responsive-addons-import-btns">
						<a href="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ); ?>" class="rst-exit-to-dashboard">
							<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M28.5 2.5L2.5 28.5M28.5 28.5L2.5 2.50002" stroke="black" stroke-width="4" stroke-linecap="round"/>
							</svg>
						</a>
					</div>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<div class="ready-site-import-options-subscription-wrapper">
					<div class="sites-import-process-errors" style="display: none">
						<div class="import-process-error">
							<div class="current-importing-status-error-title"></div>
						</div>
					</div>
					<#
						var requireContainer= data.require_flexbox_container;

						if(data.required_plugins.includes('Elementor') && requireContainer){ #>
						<?php
						$elementor_option_value = get_option( 'elementor_experiment-container' );

						if ( 'inactive' === $elementor_option_value ) {
							?>
						<div class="elementor-sites-error-msg-container">
							<div class="elementor-sites-error-msg">
								<div>
									<svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:-4px" width="16" height="16" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><g fill="currentColor"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588l-2.29.287l-.082.38l.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319c.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246c-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0a1 1 0 0 1 2 0z"/></g></svg>
								</div>
								<div class="elementor-error-msg">
									<span>
										<?php esc_html_e( 'This starter template is built using Elementor flexbox containers. Please mark the Flexbox Container setting as \'active\' in Elementor Features settings.', 'responsive-add-ons' ); ?>
									</span>
									<a href="
									<?php
									$elementor_settings_url = admin_url( 'admin.php?page=elementor#tab-experiments' );
									echo esc_url( $elementor_settings_url );
									?>
										">
										<?php esc_html_e( 'Go to settings', 'responsive-add-ons' ); ?>
									</a>
								</div>
							</div>
						</div>
						<?php } ?>
					<# } #>
					<div class="site-import-options">
						<div class="responsive-ready-sites-advanced-options">
							<h2 class="responsive-import-ready-site-title">Import {{data.name}}</h2>
							<# if ( data.slug === data.active_site ) { #>
								<p><?php esc_html_e( 'This will delete previously imported site', 'responsive-add-ons' ); ?></p>
							<# } #>
							<ul class="responsive-ready-site-contents">
								<ul class="responsive-ready-sites-import-dependencies-wrapper">
								<?php
								$current_theme = wp_get_theme();
								if ( ! ( 'Responsive' === $current_theme->get( 'Name' ) ) || ( is_child_theme() && 'Responsive' === $current_theme->parent()->get( 'Name' ) ) ) {
									?>
								<li class="responsive-ready-sites-install-responsive">
									<label class="ready-site-single-import-option">
											<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="reset" checked="checked" id="install_responsive_checkbox">
											<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
									</label>
									<span class="ready-site-single-import-option-title"><?php esc_html_e( 'Install Responsive Theme', 'responsive-add-ons' ); ?></span>
								</li>
									<?php
								}
								?>
								<li class="responsive-ready-sites-import-plugins">
									<label class="ready-site-single-import-option disabled">
										<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="plugins" checked="checked" readonly>
										<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
									</label>
									<span class="ready-site-single-import-option-title"><?php esc_html_e( 'Install Required Plugins', 'responsive-add-ons' ); ?></span>
									<ul class="required-plugins-list" style="display: none;"></ul>
								</li>
								<li class="responsive-ready-sites-import-xml">
									<label class="ready-site-single-import-option">
										<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="content" checked="checked" class="checkbox">
										<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
									</label>
									<span class="ready-site-single-import-option-title"><?php esc_html_e( 'Import Content', 'responsive-add-ons' ); ?></span>
								</li>
								<li class="responsive-ready-sites-import-customizer">
									<label class="ready-site-single-import-option">
										<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="customizer" checked="checked" class="checkbox">
										<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
									</label>
									<span class="ready-site-single-import-option-title"><?php esc_html_e( 'Import Customizer Settings', 'responsive-add-ons' ); ?></span>
								</li>
								</ul>
								<li class="responsive-ready-sites-reset-data">
									<label class="ready-site-single-import-option">
										<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="reset" checked="checked" class="checkbox">
										<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
									</label>
									<span class="ready-site-single-import-option-title"><?php esc_html_e( 'Delete Previous Import', 'responsive-add-ons' ); ?></span>
								</li>
								<p class="responsive-ready-sites-reset-data-message">
									<?php esc_html_e( 'This option will remove the previous imported content and will perform a fresh and clean install.', 'responsive-add-ons' ); ?>
								</p>
							</ul>
						</div>
					</div>
					<div class="ready-sites-import-subscription">
						<h3 class="ready-sites-import-subscription-title"><?php esc_html_e( 'Subscribe and Import', 'responsive-add-ons' ); ?></h3>
						<input type="email" id="ready-sites-subscriber-email" name="subscriber_emmail" placeholder="Enter Your Email Address" value="<?php echo isset( $settings['account']['email'] ) ? esc_html( $settings['account']['email'] ) : ''; ?>">
						<input type="hidden" id="ready-sites-importing-template-name" name="ready-sites-template-name" value="{{data.name}}">
						<div class="ready-sites-subscription-user-consent">
							<input type="checkbox" id="ready-sites-subscription-check" name="subscription_check">
							<label for="ready-sites-subscription-check"><?php esc_html_e( 'Yes, count me in!', 'responsive-add-ons' ); ?></label>
						</div>
					</div>
					<div class="responsive-ready-sites-import-button-wrap">
						<?php if ( $cc_app_auth->has_auth() ) : ?>
							<button class="button responsive-ready-site-import-without-sub responsive-addons-ready-site-import">
								<?php esc_html_e( 'Skip Email', 'responsive-add-ons' ); ?>
							</button>
							<button class="button responsive-ready-site-import-with-sub responsive-addons-ready-site-import">
								<?php esc_html_e( 'Start Importing', 'responsive-add-ons' ); ?>
							</button>
						<?php else : ?>
							<button class="button responsive-ready-site-import-without-sub responsive-ready-site-import-{{{data.demo_type}}}">
								<?php esc_html_e( 'Skip Email', 'responsive-add-ons' ); ?>
							</button>
							<button class="button responsive-ready-site-import-with-sub responsive-ready-site-import-{{{data.demo_type}}}">
								<?php esc_html_e( 'Start Importing', 'responsive-add-ons' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
				<div class="result_preview" style="display: none">
				</div>
			</div>
			<div class="wp-full-overlay-footer">
				<div class="responsive-ready-sites-advanced-options-wrap-footer-btn-wrapper">
					<button class="responsive-addons-go-back-btn responsive-addons"><span class="responsive-addons-go-back-btn-text"><?php esc_html_e( 'Go Back', 'responsive-add-ons' ); ?></span></button>
				</div>
			<div>
		</div>
		</div>
</script>

<?php
/** Single Template Preview Screen Page */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-page-preview-page">
	<div class="responsive-ready-sites-advanced-options-wrap template-preview-page wp-full-overlay collapsed"
		data-demo-api="{{{data.demo_api}}}"
		data-demo-name="{{{data.name}}}"
		data-screenshot="{{{data.screenshot}}}"
		data-demo-type="{{{data.demo_type}}}"
		data-wpforms-path="{{{data.wpforms_path}}}"
		data-required-plugins="{{data.required_plugins}}"
		data-required-pro-plugins="{{data.required_pro_plugins}}"
		data-page-builder="{{data.page_builder}}">
		<div class="wp-full-overlay-main">
			<div class="sites-import-process-errors" style="display: none">
				<div class="import-process-error">
					<div class="current-importing-status-error-title"></div>
				</div>
			</div>

			<div class="theme-browser rendered">
				<div id="site-pages" class="themes wp-clearfix">
			<div class="single-site-wrap">
				<div class="single-site">
					<div class="single-site-preview-wrap">
						<div class="single-site-preview">
							<img class="theme-screenshot" data-src="" src="{{data.screenshot}}" />
						</div>
					</div>
					<div class="single-site-pages-wrap">
						<div class="responsive-pages-title-wrap">
							<span class="responsive-pages-title"><?php esc_html_e( 'Page Templates', 'responsive-add-ons' ); ?></span>
						</div>
						<div class="single-site-pages">
							<div id="single-pages">
								<# for (page_id in data.pages)  { #>
								<#
								var required_plugins = [];
								for( id in data.pages[page_id]['free_plugins']) {
									JSON.parse( data.required_plugins ).forEach( function( single_plugin ) {
										if ( data.pages[page_id]['free_plugins'][id] == single_plugin.slug ) {
											required_plugins.push( single_plugin );
										}
									}
								);
								}
								var required_pro_plugins = [];
								for( id in data.pages[page_id]['pro_plugins']) {
									JSON.parse( data.required_pro_plugins ).forEach( function( single_plugin ) {
										if ( data.pages[page_id]['pro_plugins'][id] == single_plugin.slug ) {
											required_pro_plugins.push( single_plugin );
										}
									}
								);
								}
								#>
								<div class="theme responsive-theme site-single" data-page-id="{{data.pages[page_id]['page_id']}}" data-required-pro-plugins="{{ JSON.stringify( required_pro_plugins )}}" data-required-plugins="{{ JSON.stringify( required_plugins )}}" data-includes-wp-forms="{{ data.pages[page_id]['includes_wp_forms'] }}" >
									<div class="inner">
										<#
										var featured_image_class = '';
										var featured_image = data.pages[page_id]['featured_image'] || '';
										if( '' === featured_image ) {
										featured_image = '<?php echo esc_url( RESPONSIVE_ADDONS_DIR . 'inc/assets/images/placeholder.png' ); ?>';
										featured_image_class = ' no-featured-image ';
										}

										var thumbnail_image = data.pages[page_id]['thumbnail-image-url'] || '';
										if( '' === thumbnail_image ) {
										thumbnail_image = featured_image;
										}
										#>
										<span class="site-preview" data-title="{{ data.pages[page_id]['page_title'] }}">
										<div class="theme-screenshot one loading {{ featured_image_class }}" data-src="{{ featured_image }}" data-featured-src="{{ featured_image }}" data-demo-type="{{ data.demo_type }}" style="background-image: url('{{ featured_image }}');"></div>
									</span>
										<div class="theme-id-container">
											<h3 class="theme-name">
												{{{ data.pages[page_id]['page_title'] }}}
											</h3>
										</div>
									</div>
								</div>
								<# } #>
							</div>
						</div>
					</div>
					<div class="single-site-footer">
						<div class="site-action-buttons-wrap">
							<span class="responsive-site-demo-name">{{data.name}}</span>
							<div class="site-action-buttons-right">
								<# if ( ( data.demo_type == "pro" && data.is_responsive_addons_pro_installed && data.is_responsive_addons_pro_license_active ) ) { #>
										<button class="responsive-addons-go-back-btn-pro responsive-addons"><span class="responsive-addons-go-back-btn-text"><?php esc_html_e( 'Go Back', 'responsive-add-ons' ); ?></span></button>
									<# } else { #>
										<button class="responsive-addons-go-back-btn responsive-addons"><span class="responsive-addons-go-back-btn-text"><?php esc_html_e( 'Go Back', 'responsive-add-ons' ); ?></span></button>
								<# } #>
								<a href="{{{data.demo_api}}}" class="button button-hero site-preview-button" target="_blank">Preview "{{data.name}}" Site <i class="dashicons dashicons-external"></i></a>
								<?php if ( $cc_app_auth->has_auth() ) : ?>
									<div class="button button-hero button-primary single-page-import-button disabled"><?php esc_html_e( 'Select Template', 'responsive-add-ons' ); ?></div>
								<?php else : ?>
									<div class="button button-hero button-primary single-page-import-button-{{{ data.demo_type }}} disabled"><?php esc_html_e( 'Select Template', 'responsive-add-ons' ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
				</div>
			</div>
			<div class="result_preview" style="display: none">
			</div>
		</div>
	</div>
</script>
<?php
/** Single Template Import Options Screen */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-single-page-options-page">
	<div class="responsive-ready-sites-advanced-options-wrap single-page-import-options-page wp-full-overlay collapsed"
		data-page-id="{{{data.page_id}}}"
		data-demo-type = "{{{data.demo_type}}}"
		data-is-responsive-addons-pro-installed = "{{{data.is_responsive_addons_pro_installed}}}"
		data-is-responsive-addons-pro-license-active = "{{{data.is_responsive_addons_pro_license_active}}}"
		data-demo-api="{{{data.demo_api}}}"
		data-includes-wp-forms="{{{data.includes_wp_forms}}}"
		data-wpforms-path="{{{data.wpforms_path}}}"
		data-required-plugins="{{ JSON.stringify( data.required_plugins )}}"
		data-require-flexbox-container="{{data.require_flexbox_container}}"
		data-required-pro-plugins="{{ JSON.stringify( data.required_pro_plugins )}}"
		data-demo-name="{{ JSON.stringify( data.name )}}"
		data-page-builder="{{ JSON.stringify( data.page_builder )}}">
		<div class="wp-full-overlay-header">
			<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
					<div class="responsive-addons-import-btns">
						<a href="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ); ?>" class="rst-exit-to-dashboard">
							<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M28.5 2.5L2.5 28.5M28.5 28.5L2.5 2.50002" stroke="black" stroke-width="4" stroke-linecap="round"/>
							</svg>
						</a>
					</div>
				</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="ready-site-import-options-subscription-wrapper">
				<div class="sites-import-process-errors" style="display: none">
					<div class="import-process-error">
						<div class="current-importing-status-error-title"></div>
					</div>
				</div>
				<#
					var hasElementorPlugin = data.required_plugins.some(function(plugin) {
					return plugin.name === 'Elementor';
					});

					var requireContainer= data.require_flexbox_container;

					if(hasElementorPlugin && requireContainer){ #>
					<?php
					$elementor_option_value = get_option( 'elementor_experiment-container' );

					if ( 'inactive' === $elementor_option_value ) {
						?>
					<div class="elementor-sites-error-msg-container">
						<div class="elementor-sites-error-msg">
							<div>
								<svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:-4px" width="16" height="16" preserveAspectRatio="xMidYMid meet" viewBox="0 0 16 16"><g fill="currentColor"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588l-2.29.287l-.082.38l.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319c.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246c-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0a1 1 0 0 1 2 0z"/></g></svg>
							</div>
							<div class="elementor-error-msg">
								<span>
									<?php esc_html_e( 'This starter template is built using Elementor flexbox containers. Please mark the Flexbox Container setting as \'active\' in Elementor Features settings.', 'responsive-add-ons' ); ?>
								</span>
								<a href="
								<?php
								$elementor_settings_url = admin_url( 'admin.php?page=elementor#tab-experiments' );
								echo esc_url( $elementor_settings_url );
								?>
									">
									<?php esc_html_e( 'Go to settings', 'responsive-add-ons' ); ?>
								</a>
							</div>
						</div>
					</div>
					<?php } ?>
				<# } #>
				<div class="site-import-options">
					<div class="responsive-ready-sites-advanced-options">
						<h2 class="responsive-import-ready-site-title">Importing {{data.page_title}} Template</h2>
						<ul class="responsive-ready-site-contents">
							<li class="responsive-ready-sites-import-plugins">
								<label class="ready-site-single-import-option disabled">
									<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="plugins" checked="checked" readonly>
									<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
								</label>
								<span class="ready-site-single-import-option-title"><strong><?php esc_html_e( 'Install Required Plugins', 'responsive-add-ons' ); ?></strong></span>
								<ul class="required-plugins-list" style="display: none;"></ul>
							</li>
							<li class="responsive-ready-sites-import-xml">
								<label class="ready-site-single-import-option">
									<input class="ready-site-import-option-input-checkbox checkbox" type="checkbox" name="content" checked="checked" class="checkbox">
									<span class="ready-site-import-option-input-slider ready-site-import-option-input-round"></span>
								</label>
								<span class="ready-site-single-import-option-title"><strong><?php esc_html_e( 'Import Content', 'responsive-add-ons' ); ?></strong></span>
							</li>
						</ul>
					</div>
				</div>
				<div class="ready-sites-import-subscription">
					<h3 class="ready-sites-import-subscription-title"><?php esc_html_e( 'Subscribe and Import', 'responsive-add-ons' ); ?></h3>
					<input type="email" id="ready-sites-subscriber-email" name="subscriber_emmail" placeholder="Enter Your Email Address" value="<?php echo isset( $settings['account']['email'] ) ? esc_html( $settings['account']['email'] ) : ''; ?>">
					<input type="hidden" id="ready-sites-importing-template-name" name="ready-sites-template-name" value="{{data.name}}">
					<div class="ready-sites-subscription-user-consent">
						<input type="checkbox" id="ready-sites-subscription-check" name="subscription_check">
						<label for="ready-sites-subscription-check"><?php esc_html_e( 'Yes, count me in!', 'responsive-add-ons' ); ?></label>
					</div>
				</div>
				<div class="responsive-ready-sites-import-button-wrap">
					<?php if ( $cc_app_auth->has_auth() ) : ?>
						<button class="button responsive-ready-site-import-without-sub import-page responsive-ready-page-import">
							<?php esc_html_e( 'Skip Email', 'responsive-add-ons' ); ?>
						</button>
						<button class="button responsive-ready-site-import-with-sub import-page responsive-ready-page-import">
							<?php esc_html_e( 'Start Importing', 'responsive-add-ons' ); ?>
						</button>
					<?php else : ?>
						<button class="button responsive-ready-site-import-without-sub import-page responsive-ready-page-import-{{{data.demo_type}}}">
							<?php esc_html_e( 'Skip Email', 'responsive-add-ons' ); ?>
						</button>
						<button class="button responsive-ready-site-import-with-sub import-page responsive-ready-page-import-{{{data.demo_type}}}">
							<?php esc_html_e( 'Start Importing', 'responsive-add-ons' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<div class="result_preview" style="display: none">
			</div>
		</div>
		<div class="wp-full-overlay-footer">
			<div class="responsive-ready-sites-advanced-options-wrap-footer-btn-wrapper">
				<button class="responsive-addons-go-back-btn responsive-addons"><span class="responsive-addons-go-back-btn-text"><?php esc_html_e( 'Go Back', 'responsive-add-ons' ); ?></span></button>
			</div>
			<div></div>
		</div>
	</div>
</script>

<?php
/** Single Template Import Progress Screen */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-single-page-progress-page">
	<div class="responsive-ready-sites-advanced-options-wrap single-page-import-progress-page wp-full-overlay collapsed">
		<div class="wp-full-overlay-header">
			<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
					<div class="responsive-addons-import-btns">
						<a href="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ); ?>" class="rst-exit-to-dashboard">
							<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M28.5 2.5L2.5 28.5M28.5 28.5L2.5 2.50002" stroke="black" stroke-width="4" stroke-linecap="round"/>
							</svg>
						</a>
					</div>
				</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="responsive-ready-sites-import-progress-container">
				<div class="site-import-options">
					<div class="responsive-ready-sites-advanced-options">
						<h2 class="ready-sites-import-progress-title"><?php esc_html_e( 'We are Building your Website', 'responsive-add-ons' ); ?></h2>
						<div class="sites-import-process-errors" style="display: none">
							<div class="import-process-error">
								<div class="current-importing-status-error-title"></div>
							</div>
						</div>
							<div class="ready-sites-import-progress-info">
							<div class="ready-sites-import-progress-info-text"><?php echo esc_html_e( 'Pre-Checking and Starting Up Import Process', 'responsive-add-ons' ); ?></div>
							<div class="ready-sites-import-progress-info-percent"><?php echo esc_html_e( '0%', 'responsive-add-ons' ); ?></div>
							</div>
							<div class="ready-sites-import-progress-bar-wrap">
								<div class="ready-sites-import-progress-bar-bg">
									<div class="ready-sites-import-progress-bar"></div>
								</div>
								<div class="ready-sites-import-progress-gap"><span></span><span></span><span></span></div>
							</div>
						</div>
						<div class="ready-sites-import-progress-img-wrapper">
							<div class="ready-sites-import-progress-img">
								<img class="ready-sites-import-progress-image" src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/import-progress.gif' ); ?>">
							</div>
						</div>
					</div>
				</div>
				<div class="result_preview" style="display: none">
				</div>
		</div>
	</div>
</script>

<?php
/** Congratulations Screen */
?>
<script type="text/template" id="tmpl-responsive-ready-sites-import-done-congrats-page">
	<div class="responsive-ready-sites-advanced-options-wrap responsive-site-import-done-congrats wp-full-overlay collapsed">
		<div class="wp-full-overlay-header">
			<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
				</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="responsive-ready-sites-import-progress-container">
				<div class="site-import-options">
					<div class="responsive-ready-sites-advanced-options">
						<div class="responsive-ready-sites-import-done-congrats-title-wrap">
							<h2 class="ready-sites-import-done-congrats-title"><?php esc_html_e( 'Congratulations', 'responsive-add-ons' ); ?></h2>
							<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1.97894 32.7436C2.58191 33.397 5.0655 32.2575 7.02581 31.3437C8.49472 30.6611 14.373 28.2492 17.2949 26.9981C18.0838 26.6608 19.2313 26.2198 20.0574 25.1334C20.7905 24.1666 22.7349 20.06 18.8196 15.9003C14.8458 11.6769 10.7525 12.843 9.22253 13.9267C8.32206 14.5642 7.58894 16.0012 7.25956 16.7317C5.86769 19.8183 3.88347 25.4734 3.07597 27.6967C2.48362 29.3356 1.38128 32.0955 1.97894 32.7436Z" fill="#FFC107"/>
								<path d="M6.86647 17.6614C6.90365 18.1236 6.99662 18.8753 7.31537 20.323C7.53318 21.3164 7.88912 22.3577 8.17865 23.0456C9.04725 25.1148 10.2665 25.9489 11.5016 26.6183C13.6001 27.7552 15.0265 27.9677 15.0265 27.9677L13.3158 28.6663C13.3158 28.6663 12.2799 28.4511 10.8668 27.7552C9.52006 27.0911 8.11756 25.9675 7.09225 23.7681C6.64865 22.8145 6.391 21.8902 6.24225 21.157C6.05897 20.2486 6.02975 19.7333 6.02975 19.7333L6.86647 17.6614ZM4.76537 23.0483C4.76537 23.0483 4.97787 24.7722 6.40162 26.9477C8.06975 29.4923 10.3993 29.9094 10.3993 29.9094L8.85068 30.5469C8.85068 30.5469 7.12147 30.0183 5.47725 27.7738C4.45193 26.3739 4.16506 24.7005 4.16506 24.7005L4.76537 23.0483ZM3.29115 27.1336C3.29115 27.1336 3.67896 28.6211 4.52896 29.7314C5.541 31.0569 6.82928 31.442 6.82928 31.442L5.64193 31.968C5.64193 31.968 4.74147 31.7794 3.756 30.5309C3.00693 29.5827 2.79443 28.4936 2.79443 28.4936L3.29115 27.1336Z" fill="#FF8F00"/>
								<path opacity="0.44" d="M2.64572 30.9108C2.59259 30.7913 2.59259 30.6558 2.64838 30.5389L9.41384 16.5086L10.5268 20.6922L3.40806 30.9745C3.21681 31.2614 2.7865 31.2242 2.64572 30.9108Z" fill="#FFFDE7"/>
								<path d="M11.0633 22.0973C14.2243 25.7948 17.8235 25.3326 19.0214 24.3976C20.2221 23.46 21.1703 20.238 18.0227 16.6015C14.7236 12.7925 10.9889 13.8789 10.1257 14.7023C9.26238 15.5258 8.16269 18.7053 11.0633 22.0973Z" fill="url(#paint0_linear_81_1846)"/>
								<path d="M21.9194 23.6194C20.7666 22.6525 20.153 22.8252 19.3296 23.1678C18.2671 23.6087 16.5963 23.9355 14.3279 23.1678L15.0105 21.5236C16.3573 21.9778 17.3321 21.7573 18.1741 21.2606C19.2579 20.6231 20.7401 19.7492 23.0457 21.6856C24.0073 22.4931 24.9927 23.0297 25.7152 22.7853C26.2412 22.61 26.5201 21.8264 26.6609 21.2022C26.6741 21.1464 26.6954 20.987 26.7113 20.8462C26.8388 19.8714 27.0513 17.7677 28.6185 16.6919C30.2946 15.5417 32.0557 15.5417 32.0557 15.5417L32.3744 18.708C31.5643 18.5884 31.0012 18.7531 30.5257 19.0161C28.7354 20.0122 30.2946 23.8372 27.5082 25.1228C24.828 26.3686 22.6366 24.2197 21.9194 23.6194Z" fill="#03A9F4"/>
								<path d="M12.0593 19.5819L10.9065 18.5486C13.0235 16.1845 12.4657 14.4473 12.0593 13.183C11.9769 12.928 11.8999 12.6862 11.8494 12.4552C11.6688 11.637 11.6316 10.9252 11.6874 10.3036C10.8746 9.29156 10.516 8.23172 10.4921 8.16C9.99803 6.66453 10.3699 5.20625 11.2226 3.83828C12.9465 1.0625 16.0676 1.0625 16.0676 1.0625L17.1088 3.84891C16.3172 3.81703 13.7221 3.85687 12.9252 5.11328C11.9185 6.69641 12.5799 7.67391 12.6277 7.78547C12.8216 7.53312 13.0182 7.33125 13.1935 7.17453C14.4658 6.04563 15.5708 5.88359 16.2747 5.94734C17.0663 6.01906 17.7835 6.4175 18.2962 7.07094C18.8566 7.78813 19.0877 8.72047 18.9097 9.56781C18.7371 10.3939 18.1872 11.0925 17.3612 11.5361C15.9188 12.3117 14.7182 12.2055 13.9133 11.9372C13.9187 11.9558 13.9213 11.977 13.9266 11.9956C13.9558 12.1284 14.0143 12.3144 14.0833 12.5295C14.5535 13.9852 15.4274 16.2961 12.0593 19.5819ZM14.0116 9.61297C14.1657 9.72453 14.3277 9.8175 14.4951 9.88391C15.0529 10.107 15.6612 10.0327 16.3518 9.66078C16.7582 9.44297 16.806 9.20922 16.8219 9.13219C16.8697 8.90109 16.7901 8.60625 16.6174 8.38578C16.466 8.19187 16.2907 8.09094 16.0808 8.06969C15.6824 8.03516 15.1432 8.2875 14.604 8.76828C14.3463 8.99937 14.1497 9.28359 14.0116 9.61297Z" fill="#F44336"/>
								<path d="M16.6732 20.0148L15.0237 19.9697C15.0237 19.9697 15.8073 15.5444 18.344 14.8006C18.8195 14.6625 19.3401 14.5217 19.8634 14.4447C20.1741 14.3969 20.6656 14.3251 20.9073 14.2348C20.9631 13.8178 20.7877 13.2865 20.5912 12.6836C20.4371 12.2161 20.2777 11.7353 20.1927 11.2094C20.0281 10.184 20.3016 9.27827 20.9631 8.65405C21.7706 7.89702 23.0748 7.6553 24.5463 7.98999C25.3857 8.18124 26.0046 8.59296 26.5491 8.95421C27.3274 9.47217 27.7816 9.73514 28.7326 9.09499C29.8827 8.31936 28.3793 5.28327 27.5798 3.53014L30.5627 2.28702C30.9638 3.16358 32.9002 7.67389 31.6226 10.2478C31.1923 11.1137 30.4512 11.6875 29.479 11.9026C27.3646 12.3755 26.1268 11.552 25.2237 10.9517C24.796 10.6675 24.4215 10.4444 24.0151 10.3275C21.1915 9.52264 25.1334 13.677 23.2873 15.5444C22.1796 16.6626 19.4729 16.9575 19.2976 17C17.5551 17.4197 16.6732 20.0148 16.6732 20.0148Z" fill="#F48FB1"/>
								<path d="M11.6848 10.3036C11.6343 10.888 11.6104 11.2359 11.7618 11.9956C12.4923 12.5322 14.0834 12.5322 14.0834 12.5322C14.0143 12.317 13.9532 12.1311 13.9267 11.9983C13.9213 11.9797 13.9187 11.9584 13.9134 11.9398C12.2957 11.1323 11.6848 10.3036 11.6848 10.3036Z" fill="#C92B27"/>
								<path d="M8.37522 12.92L5.62866 11.5733L6.99663 9.59705L9.15085 11.0235L8.37522 12.92Z" fill="#FFC107"/>
								<path d="M4.32715 9.19062C2.92465 9.00202 1.49559 7.81202 1.33887 7.67656L2.71746 6.0589C3.13449 6.41218 4.01902 7.00452 4.61137 7.08421L4.32715 9.19062Z" fill="#FB8C00"/>
								<path d="M6.80269 5.64985L4.78394 4.98844C5.01503 4.28188 5.07612 3.51954 4.95659 2.78375L7.05503 2.44641C7.22769 3.51422 7.14003 4.62188 6.80269 5.64985Z" fill="#03A9F4"/>
								<path d="M19.4099 4.0707L21.4858 3.61649L22.0853 6.35667L20.0094 6.81089L19.4099 4.0707Z" fill="#FB8C00"/>
								<path d="M24.5596 4.72014L23.0986 3.17686C23.8636 2.4517 24.0389 1.50342 24.0389 1.4928L26.1374 1.83545C26.1108 2.0028 25.8425 3.50624 24.5596 4.72014Z" fill="#FFC107"/>
								<path d="M25.3708 12.9041L27.2268 12.3239L27.8606 14.3523L26.0046 14.9324L25.3708 12.9041Z" fill="#FB8C00"/>
								<path d="M25.9117 30.0236L23.8 29.7739C23.8903 29.0222 23.3298 28.1005 23.1758 27.8959L24.8758 26.6209C25.0033 26.7883 26.1109 28.3209 25.9117 30.0236Z" fill="#F44336"/>
								<path d="M31.9734 27.3301C31.1792 27.2106 30.3663 27.1628 29.5642 27.192L29.4924 25.067C30.4248 25.0351 31.3677 25.0883 32.2895 25.2291L31.9734 27.3301Z" fill="#FB8C00"/>
								<path d="M29.1162 30.2552L30.609 28.743L32.6636 30.7719L31.1708 32.2838L29.1162 30.2552Z" fill="#F48FB1"/>
								<path d="M24.7304 16.8231L26.2657 18.5786L24.5102 20.1139L22.9749 18.3584L24.7304 16.8231Z" fill="#F44336"/>
								<defs>
								<linearGradient id="paint0_linear_81_1846" x1="19.7583" y1="16.426" x2="11.8514" y2="21.17" gradientUnits="userSpaceOnUse">
								<stop offset="0.024" stop-color="#8F4700"/>
								<stop offset="1" stop-color="#703E2D"/>
								</linearGradient>
								</defs>
							</svg>
						</div>
						<div class="ready-sites-import-progress-info">
							<div class="ready-sites-import-progress-info-text"><?php esc_html_e( 'Your Website is ready and it took just ', 'responsive-add-ons' ); ?><span class="responsive-ready-sites-import-time-taken">60</span><?php esc_html_e( ' seconds to build.', 'responsive-add-ons' ); ?></div>
						</div>
						<div class="ready-sites-import-progress-bar-wrap">
							<div class="ready-sites-import-progress-bar-bg">
								<div class="ready-sites-import-progress-bar import-done"></div>
							</div>
							<div class="ready-sites-import-progress-gap"><span></span><span></span><span></span></div>
						</div>
						<div class="responsive-sites-import-done-success-section">
							<div class="responsive-sites-import-done-success">
								<p class="responsive-sites-after-import-rate-text">
									<?php esc_html_e( 'Every feedback inspires us to keep adding features and improving. If you\'re enjoying the plugin, we\'d really appreciate a 5-star rating.', 'responsive-add-ons' ); ?>
								</p>
							</div>
							<div class="responsive-sites-after-import-rate-plugin-btn-wrap" >
								<a href="https://wordpress.org/support/plugin/responsive-add-ons/reviews/#new-post" target="_blank" id="responsive-sites-after-import-rate-plugin-link">
									<p class="rate-btn"><?php esc_html_e( 'Rate the plugin', 'responsive-add-ons' ); ?></p>
									<?php for( $i=0; $i < 5; $i++ ) : ?>
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
										<g clip-path="url(#clip0_150_107)">
										<path d="M9.45387 3.39679C9.62577 2.86774 10.3742 2.86774 10.5461 3.39679L11.8049 7.271C11.8818 7.5076 12.1023 7.66779 12.3511 7.66779H16.4247C16.9809 7.66779 17.2122 8.37962 16.7622 8.70659L13.4666 11.101C13.2653 11.2472 13.1811 11.5064 13.258 11.743L14.5168 15.6172C14.6887 16.1463 14.0832 16.5862 13.6331 16.2592L10.3375 13.8648C10.1363 13.7186 9.86373 13.7186 9.66247 13.8648L6.36687 16.2592C5.91683 16.5862 5.31131 16.1463 5.48321 15.6172L6.74202 11.743C6.81889 11.5064 6.73468 11.2472 6.53341 11.101L3.23781 8.7066C2.78777 8.37962 3.01906 7.66779 3.57534 7.66779H7.64893C7.8977 7.66779 8.11818 7.5076 8.19506 7.271L9.45387 3.39679Z" fill="#FBBF24"/>
										</g>
										<defs>
										<clipPath id="clip0_150_107">
										<rect width="20" height="20" fill="white"/>
										</clipPath>
										</defs>
									</svg>
									<?php endfor; ?>
								</a>
							</div>
						</div>
						<div class="responsive-sites-after-import-site-btn-wrap">
							<a href="<?php echo esc_url( admin_url() ); ?>" class="btn responsive-sites-imported-site-dashboard-link" id="responsive-sites-imported-dashboard-site-link" target="_self"><?php esc_html_e( 'Exit to Dashboard', 'responsive-add-ons' ); ?></a>
							<a href="#" class="btn responsive-sites-imported-site-link" id="responsive-sites-imported-site-link" target="_blank"><?php esc_html_e( 'View Website', 'responsive-add-ons' ); ?></a>
						</div>
					</div>
					</div>
				</div>
				<div class="result_preview" style="display: none">
				</div>
		</div>
	</div>
</script>

<script type="text/template" id="tmpl-responsive-ready-sites-import-error-page">
	<div class="responsive-ready-sites-advanced-options-wrap responsive-ready-sites-import-error-page wp-full-overlay collapsed">
		<div class="wp-full-overlay-header">
			<div class="responsive-advanced-options-wrap">
					<div class="responsive-sites-demo-details">
							<div class="responsive-sites-demo-preview-logo-wrap">
								<img src="<?php echo esc_url( RESPONSIVE_ADDONS_URI . 'admin/images/svgs/responsive-starter-templates-thumbnail.svg' ); ?>">
							</div>
					</div>
					<div class="responsive-addons-import-btns">
						<a href="<?php echo esc_url( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) ) ); ?>" class="rst-exit-to-dashboard">
							<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M28.5 2.5L2.5 28.5M28.5 28.5L2.5 2.50002" stroke="black" stroke-width="4" stroke-linecap="round"/>
							</svg>
						</a>
					</div>
				</div>
		</div>
		<div class="wp-full-overlay-main">
			<div class="responsive-ready-sites-import-progress-container">
				<div class="site-import-options">
					<div class="responsive-ready-sites-advanced-options">
						<h2 class="ready-sites-import-progress-title"><?php esc_html_e( 'We are Building your Website', 'responsive-add-ons' ); ?></h2>
						<div class="sites-import-process-errors-container">
							<div class="import-process-error">
								<h4 class="current-importing-status-error-title">
									<?php esc_html_e( 'Sorry, something went wrong.', 'responsive-add-ons' ); ?>
								</h4>
								<div class="current-importing-status-error-wrap">
									<h5 class="current-importing-status-error-sub-title">
										<?php esc_html_e( 'What went wrong?', 'responsive-add-ons' ); ?>
									</h5>
									<# if( data.error_code ) { #>
									<p class="current-importing-status-error-code error-status"><span><?php esc_html_e( 'Code:', 'responsive-add-ons' ); ?></span><span>{{{data.error_code}}}</span></p>
									<# } #>
									<# if( data.message ) { #>
									<p class="current-importing-status-error-text error-status"><span><?php esc_html_e( 'Error:', 'responsive-add-ons' ); ?></span><span>"{{{data.message}}}"</span></p>
									<# } #>
									<# if( ! data.message && ! data.error_code ) { #>
									<p class="current-importing-status-error-text error-status"><span><?php esc_html_e( 'Error:', 'responsive-add-ons' ); ?></span><span>"{{{data}}}"</span></p>
									<# } #>
								</div>
								<div class="current-importing-status-error-btns">
									<button class="ready-sites-import-process-error-retry" onclick="location.reload()"><?php esc_html_e( "Click here to try again", 'responsive-add-ons' ); ?></button>
									<a class="ready-sites-import-process-error-support" href="https://cyberchimps.com/open-a-ticket/" target="_blank"><?php esc_html_e( 'or, get in touch', 'responsive-add-ons' ); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="result_preview" style="display: none"></div>
			</div>
		</div>
	</div>
</script>
<?php
wp_print_admin_notice_templates();