<?php
/**
 * Includes functions for all admin page templates and
 * functions that add menu pages in the dashboard. Also
 * has code for saving settings with defaults.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function sb_instagram_menu() {
	$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';

	$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

	global $sb_instagram_posts_manager;
	$notice = '';
	if ( $sb_instagram_posts_manager->are_critical_errors() ) {
		$notice = ' <span class="update-plugins sbi-error-alert"><span>!</span></span>';
	}

	add_menu_page(
		__( 'Instagram Feed', 'instagram-feed' ),
		__( 'Instagram Feed', 'instagram-feed' ) . $notice,
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);
	add_submenu_page(
		'sb-instagram-feed',
		__( 'Settings', 'instagram-feed' ),
		__( 'Settings', 'instagram-feed' ) . $notice,
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);
	add_submenu_page(
		'sb-instagram-feed',
		__( 'Customize', 'instagram-feed' ),
		__( 'Customize', 'instagram-feed' ),
		$cap,
		'sb-instagram-feed&tab=customize',
		'sb_instagram_settings_page'
	);
	$multisite_super_admin_only = get_site_option( 'sbi_super_admin_only', false );

	if ( !is_multisite() || $multisite_super_admin_only !== 'on' || current_user_can( 'manage_network' ) ) {
		/*add_submenu_page(
			'sb-instagram-feed',
			__( 'License', 'instagram-feed' ),
			__( 'License', 'instagram-feed' ),
			$cap,
			'sb-instagram-license',
			'sbi_license_page'
		);  */
	}
    /*add_submenu_page(
        'sb-instagram-feed',
        __( 'About Us', 'instagram-feed' ),
        __( 'About Us', 'instagram-feed' ),
        $cap,
        'sb-instagram-feed-about',
        'sb_instagram_about_page'
    ); */
}
add_action('admin_menu', 'sb_instagram_menu');

function sb_instagram_about_page() {
	do_action('sbi_admin_page' );
}

//Add Welcome page
add_action('admin_menu', 'sbi_welcome_menu');
function sbi_welcome_menu() {
	$capability = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
/*
	add_submenu_page(
		'sb-instagram-feed',
		__( "What's New?", 'instagram-feed' ),
		__( "What's New?", 'instagram-feed' ),
		$capability,
		'sbi-welcome-new',
		'sbi_welcome_screen_new_content'
	);
	add_submenu_page(
		'sb-instagram-feed',
		__( 'Getting Started', 'instagram-feed' ),
		__( 'Getting Started', 'instagram-feed' ),
		$capability,
		'sbi-welcome-started',
		'sbi_welcome_screen_started_content'
	); */
}
function sbi_welcome_screen_new_content() {

	?>
    <div class="wrap about-wrap sbi-welcome">
		<?php sbi_welcome_header(); ?>

        
    </div>
<?php }
function sbi_welcome_screen_started_content() { ?>
    <div class="wrap about-wrap sbi-welcome">
		<?php sbi_welcome_header(); ?>

    </div>
<?php }
function sbi_welcome_header(){ ?>
	<?php
	//Set an option that shows that the welcome page has been seen
	update_option( 'sbi_welcome_seen', true );
	// user has seen notice
	add_user_meta(get_current_user_id(), 'sbi_seen_welcome_'.SBI_WELCOME_VER, 'true', true);

	?>
    <div id="sbi-header">
      
    </div>
<?php }

add_action('admin_notices', 'sbi_welcome_page_notice');
function sbi_welcome_page_notice() {

	global $current_user;
	$user_id = $current_user->ID;

	// delete_transient( 'sbi_show_welcome_notice_transient' );
	// delete_option('sbi_welcome_'.SBI_WELCOME_VER.'_transient_set');

	if( current_user_can( 'manage_options' ) ){

		if( get_transient('sbi_show_welcome_notice_transient') || !get_option('sbi_welcome_'.SBI_WELCOME_VER.'_transient_set') ){

			// Use these to show notice again for testing
			// delete_user_meta($user_id, 'sbi_ignore_'.SBI_WELCOME_VER.'_welcome_notice');
			// delete_user_meta($user_id, 'sbi_seen_welcome_'.SBI_WELCOME_VER);

		} else {

			//If the transient hasn't been set before then set it for 7 days
			if( !get_option('sbi_welcome_'.SBI_WELCOME_VER.'_transient_set') ){
				set_transient( 'sbi_show_welcome_notice_transient', 'true', WEEK_IN_SECONDS );
				update_option('sbi_welcome_'.SBI_WELCOME_VER.'_transient_set', true);
			}

		}

	}

}

add_action('admin_init', 'sbi_welcome_page_banner_ignore');
function sbi_welcome_page_banner_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	if ( isset($_GET['sbi_ignore_'.SBI_WELCOME_VER.'_welcome_notice']) && '0' == $_GET['sbi_ignore_'.SBI_WELCOME_VER.'_welcome_notice']) {
		add_user_meta($user_id, 'sbi_ignore_'.SBI_WELCOME_VER.'_welcome_notice', 'true', true);
	}
}

add_action( 'admin_init', 'sbi_welcome_screen_do_activation_redirect' );
function sbi_welcome_screen_do_activation_redirect() {
	//sbi_clear_it();
	// Delete settings for testing
	// delete_user_meta(get_current_user_id(), 'sbi_seen_welcome_'.SBI_WELCOME_VER);
	// delete_option( 'sbi_ver' );
	// Check whether a 30-second transient has been set by the activation function. If it has then potentially redirect to the Getting Started page.
	$capability = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';

	if ( get_transient( '_sbi_activation_redirect' ) ){

		// Delete the redirect transient
		delete_transient( '_sbi_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$sbi_ver = get_option( 'sbi_ver' );
		if ( ! $sbi_ver ) {
			update_option( 'sbi_ver', SBIVER );
			sb_instagram_clear_page_caches();
			$disable_welcome = get_user_meta(get_current_user_id(), 'sbi_disable_welcome', true);
			if ( empty( $disable_welcome ) && current_user_can( $capability ) ) {
				update_option( 'sbi_welcome_seen', true );
				// user has seen notice
				add_user_meta(get_current_user_id(), 'sbi_seen_welcome_'.SBI_WELCOME_VER, 'true', true);
				wp_safe_redirect( admin_url( 'admin.php?page=sb-instagram-feed' ) );
				exit;
			}
		}
	} else {

		if ( isset($_GET['page']) && 'sb-instagram-feed' == $_GET['page'] && !get_user_meta(get_current_user_id(), 'sbi_seen_welcome_'.SBI_WELCOME_VER) )  {
			$disable_welcome = get_user_meta(get_current_user_id(), 'sbi_disable_welcome', true);

			if ( empty( $disable_welcome ) && current_user_can( $capability ) ) {
				update_option( 'sbi_welcome_seen', true );
				// user has seen notice
				add_user_meta(get_current_user_id(), 'sbi_seen_welcome_'.SBI_WELCOME_VER, 'true', true);
				wp_safe_redirect( admin_url( 'admin.php?page=sb-instagram-feed' ) );
				exit;
			}

		}

	}

}


function sbi_register_option() {
	// creates our settings in the options table
	register_setting('sbi_license', 'sbi_license_key', 'sbi_sanitize_license' );
}
add_action('admin_init', 'sbi_register_option');

function sbi_sanitize_license( $new ) {
	$old = get_option( 'sbi_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'sbi_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

function sbi_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['sbi_license_activate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'sbi_nonce', 'sbi_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$sbi_license = trim( get_option( 'sbi_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license'   => $sbi_license,
			'item_name' => urlencode( SBI_PLUGIN_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, SBI_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$sbi_license_data = json_decode( wp_remote_retrieve_body( $response ) );

		//store the license data in an option
		update_option( 'sbi_license_data', $sbi_license_data );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'sbi_license_status', $sbi_license_data->license );

	}
}
add_action('admin_init', 'sbi_activate_license');

function sbi_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['sbi_license_deactivate'] ) ) {

		// run a quick security check
		if( ! check_admin_referer( 'sbi_nonce', 'sbi_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$sbi_license= trim( get_option( 'sbi_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license'   => $sbi_license,
			'item_name' => urlencode( SBI_PLUGIN_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, SBI_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$sbi_license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $sbi_license_data->license == 'deactivated' )
			delete_option( 'sbi_license_status' );

	}
}
add_action('admin_init', 'sbi_deactivate_license');


//License page
function sbi_license_page() {
	$sbi_license    = trim( get_option( 'sbi_license_key' ) );
	$sbi_status     = get_option( 'sbi_license_status' );
	?>

    <div id="sbi_admin" class="wrap">

        <div id="header">
            <h1><?php _e('Instagram Feed', 'instagram-feed' ); ?></h1>
        </div>

		<?php sbi_expiration_notice(); ?>

        <form name="form1" method="post" action="options.php">

            <h2 class="nav-tab-wrapper">
                <a href="?page=sb-instagram-feed&amp;tab=configure" class="nav-tab"><?php _e('1. Configure', 'instagram-feed' ); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=customize" class="nav-tab"><?php _e('2. Customize', 'instagram-feed' ); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=display" class="nav-tab"><?php _e('3. Shortcodes', 'instagram-feed' ); ?></a>
				
            </h2>

			<?php settings_fields('sbi_license'); ?>

			<?php
			// data to send in our API request
			$sbi_api_params = array(
				'edd_action'=> 'check_license',
				'license'   => $sbi_license,
				'item_name' => urlencode( SBI_PLUGIN_NAME ) // the name of our product in EDD
			);

			// Call the custom API.
			$sbi_response = wp_remote_get( add_query_arg( $sbi_api_params, SBI_STORE_URL ), array( 'timeout' => 60, 'sslverify' => false ) );

			// decode the license data
			$sbi_license_data = (array) json_decode( wp_remote_retrieve_body( $sbi_response ) );

			//Store license data in db unless the data comes back empty as wasn't able to connect to our website to get it
			if( !empty($sbi_license_data) ) update_option( 'sbi_license_data', $sbi_license_data );

			?>

            <table class="form-table">
                <tbody>
                <h3><?php _e('License', 'instagram-feed' ); ?></h3>

                <tr valign="top">
                    <th scope="row" valign="top">
						<?php _e('Enter your license key', 'instagram-feed' ); ?>
                    </th>
                    <td>
                        <?php $license_output = apply_filters( 'sbi_license_page_output', '', get_current_user_id(), $sbi_license_data['license'] );
                        if ( empty( $license_output ) ) :

                        ?>
                        <input id="sbi_license_key" name="sbi_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $sbi_license ); ?>" />

						<?php if( false !== $sbi_license ) { ?>

							<?php if( $sbi_status !== false && $sbi_status == 'valid' ) { ?>
								<?php wp_nonce_field( 'sbi_nonce', 'sbi_nonce' ); ?>
                                <input type="submit" class="button-secondary" name="sbi_license_deactivate" value="<?php _e('Deactivate License', 'instagram-feed' ); ?>"/>

								<?php if($sbi_license_data['license'] == 'expired'){ ?>
                                    <span class="sbi_license_status" style="color:red;"><?php _e('Expired', 'instagram-feed' ); ?></span>
								<?php } else { ?>
                                    <span class="sbi_license_status" style="color:green;"><?php _e('Active', 'instagram-feed' ); ?></span>
								<?php } ?>

							<?php } else {
								wp_nonce_field( 'sbi_nonce', 'sbi_nonce' ); ?>
                                <input type="submit" class="button-secondary" name="sbi_license_activate" value="<?php _e('Activate License', 'instagram-feed' ); ?>"/>

								<?php if($sbi_license_data['license'] == 'expired'){ ?>
                                    <span class="sbi_license_status" style="color:red;"><?php _e('Expired', 'instagram-feed' ); ?></span>
								<?php } else { ?>
                                    <span class="sbi_license_status" style="color:red;"><?php _e('Inactive', 'instagram-feed' ); ?></span>
								<?php } ?>

							<?php } ?>
						<?php } ?>

                        <br />
                        
	                    <?php else:
                            echo $license_output;
                            endif; ?>

                    </td>
                </tr>

                </tbody>
            </table>

            <?php if ( empty( $license_output ) ) : ?>
            <p style="margin: 20px 0 0 0; height: 35px;">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>">
                <button name="sbi-test-license" id="sbi-test-license-btn" class="button button-secondary"><?php _e( 'Test Connection', 'instagram-feed' ); ?></button>
            </p>

            <div id="sbi-test-license-connection" style="display: none;">
				<?php
				if( isset( $sbi_license_data['item_name']) ){
					echo '<p class="sbi-success" style="display: inline-block; padding: 10px 15px; border-radius: 5px; margin: 0; background: #dceada; border: 1px solid #6ca365; color: #3e5f1c;"><i class="fa fa-check"></i> &nbsp;Connection Successful</p>';
				} else {
					echo '<div class="sbi-test-license-error">';
					highlight_string( var_export($sbi_response, true) );
					echo '<br />';
					highlight_string( var_export($sbi_license_data, true) );
					echo '</div>';
				}
				?>
            </div>
            <script type="text/javascript">
                jQuery('#sbi-test-license-btn').on('click', function(e){
                    e.preventDefault();
                    jQuery('#sbi-test-license-connection').toggle();
                });
            </script>
            <?php endif; ?>
        </form>

    </div>

	<?php
} //End License page

function sb_instagram_settings_page() {

	$sbi_welcome_seen = get_option( 'sbi_welcome_seen' );
	if( $sbi_welcome_seen == false ){ ?>
        <p class="sbi-page-loading"><?php _e("Loading...", 'instagram-feed'); ?></p>
        <script>window.location = "<?php echo admin_url( 'admin.php?page=sbi-welcome-new' ); ?>";</script>
	<?php }

	//Hidden fields
	$sb_instagram_settings_hidden_field = 'sb_instagram_settings_hidden_field';
	$sb_instagram_configure_hidden_field = 'sb_instagram_configure_hidden_field';
	$sb_instagram_customize_hidden_field = 'sb_instagram_customize_hidden_field';
	$sb_instagram_customize_posts_hidden_field = 'sb_instagram_customize_posts_hidden_field';
	$sb_instagram_customize_moderation_hidden_field = 'sb_instagram_customize_moderation_hidden_field';
	$sb_instagram_customize_advanced_hidden_field = 'sb_instagram_customize_advanced_hidden_field';
	$sb_instagram_customize_integrations_hidden_field = 'sb_instagram_customize_integration_hidden_field';


	//Declare defaults
	$sb_instagram_settings_defaults = array(
		'sb_instagram_at'                   => '',
		'sb_instagram_type'                 => 'user',
		'sb_instagram_order'                => 'top',
		'sb_instagram_user_id'              => '',
		'sb_instagram_tagged_ids' => '',
		'sb_instagram_hashtag'              => '',
		'sb_instagram_type_self_likes'      => '',
		'sb_instagram_location'             => '',
		'sb_instagram_coordinates'          => '',
		'sb_instagram_preserve_settings'    => '',
		'sb_instagram_ajax_theme'           => false,
		'enqueue_js_in_head'                => false,
		'disable_js_image_loading'          => false,
		'sb_instagram_disable_resize'       => false,
		'sb_instagram_favor_local'          => false,
		'sb_instagram_cache_time'           => '1',
		'sb_instagram_cache_time_unit'      => 'hours',
		'sbi_caching_type'                  => 'page',
		'sbi_cache_cron_interval'           => '12hours',
		'sbi_cache_cron_time'               => '1',
		'sbi_cache_cron_am_pm'              => 'am',

		'sb_instagram_width'                => '100',
		'sb_instagram_width_unit'           => '%',
		'sb_instagram_feed_width_resp'      => false,
		'sb_instagram_height'               => '',
		'sb_instagram_num'                  => '20',
		'sb_instagram_nummobile'            => '',
		'sb_instagram_height_unit'          => '',
		'sb_instagram_cols'                 => '4',
		'sb_instagram_colsmobile'           => 'auto',
		'sb_instagram_image_padding'        => '5',
		'sb_instagram_image_padding_unit'   => 'px',

		//Layout Type
		'sb_instagram_layout_type'          => 'grid',
		'sb_instagram_highlight_type'       => 'pattern',
		'sb_instagram_highlight_offset'     => 0,
		'sb_instagram_highlight_factor'     => 6,
		'sb_instagram_highlight_ids'        => '',
		'sb_instagram_highlight_hashtag'    => '',

		//Hover style
		'sb_hover_background'               => '',
		'sb_hover_text'                     => '',
		'sbi_hover_inc_username'            => true,
		'sbi_hover_inc_icon'                => true,
		'sbi_hover_inc_date'                => true,
		'sbi_hover_inc_instagram'           => true,
		'sbi_hover_inc_location'            => false,
		'sbi_hover_inc_caption'             => false,
		'sbi_hover_inc_likes'               => false,
		// 'sb_instagram_hover_text_size'      => '',

		'sb_instagram_sort'                 => 'none',
		'sb_instagram_disable_lightbox'     => false,
		'sb_instagram_captionlinks'         => false,
		'sb_instagram_offset'               => 0,
		'sb_instagram_background'           => '',
		'sb_instagram_show_btn'             => true,
		'sb_instagram_btn_background'       => '',
		'sb_instagram_btn_text_color'       => '',
		'sb_instagram_btn_text'             => __( 'Load More', 'instagram-feed' ),
		'sb_instagram_image_res'            => 'auto',
		'sb_instagram_media_type'           => 'all',
		'sb_instagram_moderation_mode'      => 'manual',
		'sb_instagram_hide_photos'          => '',
		'sb_instagram_block_users'          => '',
		'sb_instagram_ex_apply_to'          => 'all',
		'sb_instagram_inc_apply_to'         => 'all',
		'sb_instagram_show_users'           => '',
		'sb_instagram_exclude_words'        => '',
		'sb_instagram_include_words'        => '',

		//Text
		'sb_instagram_show_caption'         => true,
		'sb_instagram_caption_length'       => '50',
		'sb_instagram_caption_color'        => '',
		'sb_instagram_caption_size'         => '13',

		//lightbox comments
		'sb_instagram_lightbox_comments'    => true,
		'sb_instagram_num_comments'         => '20',

		//Meta
		'sb_instagram_show_meta'            => true,
		'sb_instagram_meta_color'           => '',
		'sb_instagram_meta_size'            => '13',
		//Header
		'sb_instagram_show_header'          => true,
		'sb_instagram_header_color'         => '',
		'sb_instagram_header_style'         => 'standard',
		'sb_instagram_show_followers'       => true,
		'sb_instagram_show_bio'             => true,
		'sb_instagram_custom_bio' => '',
		'sb_instagram_custom_avatar' => '',
		'sb_instagram_header_primary_color'  => '517fa4',
		'sb_instagram_header_secondary_color'  => 'eeeeee',
		'sb_instagram_header_size'  => 'small',
		'sb_instagram_outside_scrollable' => false,
		'sb_instagram_stories' => true,
		'sb_instagram_stories_time' => 5000,

		//Follow button
		'sb_instagram_show_follow_btn'      => true,
		'sb_instagram_folow_btn_background' => '',
		'sb_instagram_follow_btn_text_color' => '',
		'sb_instagram_follow_btn_text'      => __( 'Follow on Instagram', 'instagram-feed' ),

		//Autoscroll
		'sb_instagram_autoscroll' => false,
		'sb_instagram_autoscrolldistance' => 200,

		//Misc
		'sb_instagram_custom_css'           => '',
		'sb_instagram_custom_js'            => '',
		'sb_instagram_requests_max'         => '5',
		'sb_instagram_minnum' => '0',
		'sb_instagram_cron'                 => 'unset',
		'sb_instagram_disable_font'         => false,
		'sb_instagram_backup' => true,
		'sb_ajax_initial' => false,
		'enqueue_css_in_shortcode' => false,
		'sb_instagram_disable_mob_swipe' => false,
		'sbi_font_method' => 'svg',
		'sbi_br_adjust' => true,
		'sb_instagram_media_vine' => false,
		'custom_template' => false,
		'disable_admin_notice' => false,
		'enable_email_report' => 'on',
		'email_notification' => 'monday',
		'email_notification_addresses' => get_option( 'admin_email' ),

		//Carousel
		'sb_instagram_carousel'             => false,
		'sb_instagram_carousel_rows'        => 1,
		'sb_instagram_carousel_loop'        => 'rewind',
		'sb_instagram_carousel_arrows'      => false,
		'sb_instagram_carousel_pag'         => true,
		'sb_instagram_carousel_autoplay'    => false,
		'sb_instagram_carousel_interval'    => '5000'

	);

	if ( is_multisite() ) {
		$sb_instagram_settings_defaults['sbi_super_admin_only'] = false;
	}
	//Save defaults in an array
	$options = wp_parse_args(get_option('sb_instagram_settings'), $sb_instagram_settings_defaults);
	update_option( 'sb_instagram_settings', $options );
	if ( isset( $_POST['sbi_just_saved'] )) {
		echo '<input id="sbi_just_saved" type="hidden" name="sbi_just_saved" value="1">';
	}
	//Set the page variables
	$sb_instagram_at = $options[ 'sb_instagram_at' ];
	$sb_instagram_type = $options[ 'sb_instagram_type' ];
	$sb_instagram_order = $options[ 'sb_instagram_order' ];
	$sb_instagram_user_id = $options[ 'sb_instagram_user_id' ];
	$sb_instagram_hashtag = $options[ 'sb_instagram_hashtag' ];
	$sb_instagram_tagged_ids = $options[ 'sb_instagram_tagged_ids' ];

	$sb_instagram_type_self_likes = $options[ 'sb_instagram_type_self_likes' ];
	$sb_instagram_location = $options[ 'sb_instagram_location' ];
	$sb_instagram_coordinates = $options[ 'sb_instagram_coordinates' ];
	$sb_instagram_preserve_settings = $options[ 'sb_instagram_preserve_settings' ];
	$sb_instagram_ajax_theme = $options[ 'sb_instagram_ajax_theme' ];
	$enqueue_js_in_head = $options[ 'enqueue_js_in_head' ];
	$disable_js_image_loading = $options[ 'disable_js_image_loading' ];
	$sb_instagram_disable_resize = $options[ 'sb_instagram_disable_resize' ];
	$sb_instagram_favor_local = $options[ 'sb_instagram_favor_local' ];

	$sb_instagram_cache_time = $options[ 'sb_instagram_cache_time' ];
	$sb_instagram_cache_time_unit = $options[ 'sb_instagram_cache_time_unit' ];

	$sbi_caching_type = $options[ 'sbi_caching_type' ];
	$sbi_cache_cron_interval = $options[ 'sbi_cache_cron_interval' ];
	$sbi_cache_cron_time = $options[ 'sbi_cache_cron_time' ];
	$sbi_cache_cron_am_pm = $options[ 'sbi_cache_cron_am_pm' ];

	$sb_instagram_width = $options[ 'sb_instagram_width' ];
	$sb_instagram_width_unit = $options[ 'sb_instagram_width_unit' ];
	$sb_instagram_feed_width_resp = $options[ 'sb_instagram_feed_width_resp' ];
	$sb_instagram_height = $options[ 'sb_instagram_height' ];
	$sb_instagram_height_unit = $options[ 'sb_instagram_height_unit' ];
	$sb_instagram_num = $options[ 'sb_instagram_num' ];
	$sb_instagram_nummobile = $options[ 'sb_instagram_nummobile' ];
	$sb_instagram_cols = $options[ 'sb_instagram_cols' ];
	$sb_instagram_colsmobile = $options[ 'sb_instagram_colsmobile' ];

	$sb_instagram_disable_mobile = isset( $options[ 'sb_instagram_disable_mobile' ] ) && ( $options[ 'sb_instagram_disable_mobile' ] == 'on' || $options[ 'sb_instagram_disable_mobile' ] == true ) ? true : false;
	$sb_instagram_image_padding = $options[ 'sb_instagram_image_padding' ];
	$sb_instagram_image_padding_unit = $options[ 'sb_instagram_image_padding_unit' ];

	//Layout Type
	$sb_instagram_layout_type = $options[ 'sb_instagram_layout_type' ];
	$sb_instagram_highlight_type = $options[ 'sb_instagram_highlight_type' ];
	$sb_instagram_highlight_offset = $options[ 'sb_instagram_highlight_offset' ];
	$sb_instagram_highlight_factor = $options[ 'sb_instagram_highlight_factor' ];
	$sb_instagram_highlight_ids = $options[ 'sb_instagram_highlight_ids' ];
	$sb_instagram_highlight_hashtag = $options[ 'sb_instagram_highlight_hashtag' ];

	//Lightbox Comments
	$sb_instagram_lightbox_comments = $options[ 'sb_instagram_lightbox_comments' ];
	$sb_instagram_num_comments = $options[ 'sb_instagram_num_comments' ];

	//Photo hover style
	$sb_hover_background = $options[ 'sb_hover_background' ];
	$sb_hover_text = $options[ 'sb_hover_text' ];
	$sbi_hover_inc_username = $options[ 'sbi_hover_inc_username' ];
	$sbi_hover_inc_icon = $options[ 'sbi_hover_inc_icon' ];
	$sbi_hover_inc_date = $options[ 'sbi_hover_inc_date' ];
	$sbi_hover_inc_instagram = $options[ 'sbi_hover_inc_instagram' ];
	$sbi_hover_inc_location = $options[ 'sbi_hover_inc_location' ];
	$sbi_hover_inc_caption = $options[ 'sbi_hover_inc_caption' ];
	$sbi_hover_inc_likes = $options[ 'sbi_hover_inc_likes' ];

	$sb_instagram_sort = $options[ 'sb_instagram_sort' ];
	$sb_instagram_disable_lightbox = $options[ 'sb_instagram_disable_lightbox' ];
	$sb_instagram_captionlinks = $options[ 'sb_instagram_captionlinks' ];
	$sb_instagram_offset = $options[ 'sb_instagram_offset' ];

	$sb_instagram_background = $options[ 'sb_instagram_background' ];
	$sb_instagram_show_btn = $options[ 'sb_instagram_show_btn' ];
	$sb_instagram_btn_background = $options[ 'sb_instagram_btn_background' ];
	$sb_instagram_btn_text_color = $options[ 'sb_instagram_btn_text_color' ];
	$sb_instagram_btn_text = $options[ 'sb_instagram_btn_text' ];
	$sb_instagram_image_res = $options[ 'sb_instagram_image_res' ];
	$sb_instagram_media_type = $options[ 'sb_instagram_media_type' ];
	$sb_instagram_moderation_mode = $options[ 'sb_instagram_moderation_mode' ];
	$sb_instagram_hide_photos = $options[ 'sb_instagram_hide_photos' ];
	$sb_instagram_block_users = $options[ 'sb_instagram_block_users' ];
	$sb_instagram_ex_apply_to = $options[ 'sb_instagram_ex_apply_to' ];
	$sb_instagram_inc_apply_to = $options[ 'sb_instagram_inc_apply_to' ];
	$sb_instagram_show_users = $options[ 'sb_instagram_show_users' ];
	$sb_instagram_exclude_words = $options[ 'sb_instagram_exclude_words' ];
	$sb_instagram_include_words = $options[ 'sb_instagram_include_words' ];

	//Text
	$sb_instagram_show_caption = $options[ 'sb_instagram_show_caption' ];
	$sb_instagram_caption_length = $options[ 'sb_instagram_caption_length' ];
	$sb_instagram_caption_color = $options[ 'sb_instagram_caption_color' ];
	$sb_instagram_caption_size = $options[ 'sb_instagram_caption_size' ];
	//Meta
	$sb_instagram_show_meta = $options[ 'sb_instagram_show_meta' ];
	$sb_instagram_meta_color = $options[ 'sb_instagram_meta_color' ];
	$sb_instagram_meta_size = $options[ 'sb_instagram_meta_size' ];
	//Header
	$sb_instagram_show_header = $options[ 'sb_instagram_show_header' ];
	$sb_instagram_header_color = $options[ 'sb_instagram_header_color' ];
	$sb_instagram_header_style = $options[ 'sb_instagram_header_style' ];
	$sb_instagram_show_followers = $options[ 'sb_instagram_show_followers' ];
	$sb_instagram_show_bio = $options[ 'sb_instagram_show_bio' ];
	$sb_instagram_custom_bio = $options[ 'sb_instagram_custom_bio' ];
	$sb_instagram_custom_avatar = $options[ 'sb_instagram_custom_avatar' ];
	$sb_instagram_header_primary_color = $options[ 'sb_instagram_header_primary_color' ];
	$sb_instagram_header_secondary_color = $options[ 'sb_instagram_header_secondary_color' ];
	$sb_instagram_header_size = $options[ 'sb_instagram_header_size' ];
	$sb_instagram_outside_scrollable = $options[ 'sb_instagram_outside_scrollable' ];
	$sb_instagram_stories = $options[ 'sb_instagram_stories' ];
	$sb_instagram_stories_time = $options[ 'sb_instagram_stories_time' ];

	//Follow button
	$sb_instagram_show_follow_btn = $options[ 'sb_instagram_show_follow_btn' ];
	$sb_instagram_folow_btn_background = $options[ 'sb_instagram_folow_btn_background' ];
	$sb_instagram_follow_btn_text_color = $options[ 'sb_instagram_follow_btn_text_color' ];
	$sb_instagram_follow_btn_text = $options[ 'sb_instagram_follow_btn_text' ];

	//Autoscroll
	$sb_instagram_autoscroll = $options[ 'sb_instagram_autoscroll' ];
	$sb_instagram_autoscrolldistance = $options[ 'sb_instagram_autoscrolldistance' ];

	//Misc
	$sb_instagram_custom_css = $options[ 'sb_instagram_custom_css' ];
	$sb_instagram_custom_js = $options[ 'sb_instagram_custom_js' ];
	$sb_instagram_requests_max = $options[ 'sb_instagram_requests_max' ];
	$sb_instagram_minnum = $options[ 'sb_instagram_minnum' ];
	$sb_instagram_cron = $options[ 'sb_instagram_cron' ];
	$sb_instagram_disable_font = $options[ 'sb_instagram_disable_font' ];
	$sb_instagram_backup = $options[ 'sb_instagram_backup' ];
	$sb_ajax_initial = $options[ 'sb_ajax_initial' ];

	$enqueue_css_in_shortcode = $options[ 'enqueue_css_in_shortcode' ];
	$sb_instagram_disable_mob_swipe = $options[ 'sb_instagram_disable_mob_swipe' ];
	$sbi_font_method = $options[ 'sbi_font_method' ];
	$sbi_br_adjust = $options[ 'sbi_br_adjust' ];
	$sb_instagram_media_vine = $options[ 'sb_instagram_media_vine' ];
	$sb_instagram_custom_template = $options[ 'custom_template' ];
	$sb_instagram_disable_admin_notice = $options[ 'disable_admin_notice' ];
    $sb_instagram_enable_email_report = $options[ 'enable_email_report' ];
	$sb_instagram_email_notification = $options[ 'email_notification' ];
	$sb_instagram_email_notification_addresses = $options[ 'email_notification_addresses' ];

	if ( is_multisite() ) {
		$sbi_super_admin_only = $options[ 'sbi_super_admin_only' ];
	}
	//Carousel
	$sb_instagram_carousel = $options[ 'sb_instagram_carousel' ];
	$sb_instagram_carousel_rows = $options[ 'sb_instagram_carousel_rows' ];
	$sb_instagram_carousel_loop = $options[ 'sb_instagram_carousel_loop' ];
	$sb_instagram_carousel_arrows = $options[ 'sb_instagram_carousel_arrows' ];
	$sb_instagram_carousel_pag = $options[ 'sb_instagram_carousel_pag' ];
	$sb_instagram_carousel_autoplay = $options[ 'sb_instagram_carousel_autoplay' ];
	$sb_instagram_carousel_interval = $options[ 'sb_instagram_carousel_interval' ];


	//Check nonce before saving data
	if ( ! isset( $_POST['sb_instagram_pro_settings_nonce'] ) || ! wp_verify_nonce( $_POST['sb_instagram_pro_settings_nonce'], 'sb_instagram_pro_saving_settings' ) ) {
		//Nonce did not verify
	} else {

		// See if the user has posted us some information. If they did, this hidden field will be set to 'Y'.
		if( isset($_POST[ $sb_instagram_settings_hidden_field ]) && $_POST[ $sb_instagram_settings_hidden_field ] == 'Y' ) {

			if( isset($_POST[ $sb_instagram_configure_hidden_field ]) && $_POST[ $sb_instagram_configure_hidden_field ] == 'Y' ) {
				if (isset($_POST[ 'sb_instagram_at' ]) ) $sb_instagram_at = sanitize_text_field( $_POST[ 'sb_instagram_at' ] );
				if (isset($_POST[ 'sb_instagram_type' ]) ) $sb_instagram_type = $_POST[ 'sb_instagram_type' ];

				$sb_instagram_user_id = array();
				if ( isset( $_POST[ 'sb_instagram_user_id' ] )) {
					if ( is_array( $_POST[ 'sb_instagram_user_id' ] ) ) {
						foreach( $_POST[ 'sb_instagram_user_id' ] as $user_id ) {
							$sb_instagram_user_id[] = sanitize_text_field( $user_id );
						}
					} else {
						$sb_instagram_user_id[] = sanitize_text_field( $_POST[ 'sb_instagram_user_id' ] );
					}
				}

				$sb_instagram_tagged_ids = array();
				if ( isset( $_POST[ 'sb_instagram_tagged_id' ] )) {
					if ( is_array( $_POST[ 'sb_instagram_tagged_id' ] ) ) {
						foreach( $_POST[ 'sb_instagram_tagged_id' ] as $user_id ) {
							$sb_instagram_tagged_ids[] = sanitize_text_field( $user_id );
						}
					} else {
						$sb_instagram_tagged_ids[] = sanitize_text_field( $_POST[ 'sb_instagram_tagged_id' ] );
					}
				}

				if (isset($_POST[ 'sb_instagram_order' ]) ) $sb_instagram_order = sanitize_text_field( $_POST[ 'sb_instagram_order' ] );

				if (isset($_POST[ 'sb_instagram_hashtag' ]) ) $sb_instagram_hashtag = sanitize_text_field( $_POST[ 'sb_instagram_hashtag' ] );
				if (isset($_POST[ 'sb_instagram_type_self_likes' ]) ) $sb_instagram_type_self_likes = $_POST[ 'sb_instagram_type_self_likes' ];
				if (isset($_POST[ 'sb_instagram_location' ]) ) $sb_instagram_location = sanitize_text_field( $_POST[ 'sb_instagram_location' ] );
				if (isset($_POST[ 'sb_instagram_coordinates' ]) ) $sb_instagram_coordinates = sanitize_text_field( $_POST[ 'sb_instagram_coordinates' ] );

				isset($_POST[ 'sb_instagram_preserve_settings' ]) ? $sb_instagram_preserve_settings = $_POST[ 'sb_instagram_preserve_settings' ] : $sb_instagram_preserve_settings = '';
				if (isset($_POST[ 'sb_instagram_cache_time' ]) ) $sb_instagram_cache_time = sanitize_text_field( $_POST[ 'sb_instagram_cache_time' ] );
				isset($_POST[ 'sb_instagram_cache_time_unit' ]) ? $sb_instagram_cache_time_unit = $_POST[ 'sb_instagram_cache_time_unit' ] : $sb_instagram_cache_time_unit = '';

				isset($_POST[ 'sbi_caching_type' ]) ? $sbi_caching_type = sanitize_text_field( $_POST[ 'sbi_caching_type' ] ) : $sbi_caching_type = '';
				isset($_POST[ 'sbi_cache_cron_interval' ]) ? $sbi_cache_cron_interval = sanitize_text_field( $_POST[ 'sbi_cache_cron_interval' ] ) : $sbi_cache_cron_interval = '';
				isset($_POST[ 'sbi_cache_cron_time' ]) ? $sbi_cache_cron_time = sanitize_text_field( $_POST[ 'sbi_cache_cron_time' ] ) : $sbi_cache_cron_time = '';
				isset($_POST[ 'sbi_cache_cron_am_pm' ]) ? $sbi_cache_cron_am_pm = sanitize_text_field( $_POST[ 'sbi_cache_cron_am_pm' ] ) : $sbi_cache_cron_am_pm = '';

				$options[ 'sb_instagram_at' ] = $sb_instagram_at;
				$options[ 'sb_instagram_type' ] = $sb_instagram_type;
				$options[ 'sb_instagram_order' ] = $sb_instagram_order;
				$options[ 'sb_instagram_user_id' ] = $sb_instagram_user_id;
				$options[ 'sb_instagram_hashtag' ] = $sb_instagram_hashtag;
				$options[ 'sb_instagram_tagged_ids' ] = $sb_instagram_tagged_ids;
				$options[ 'sb_instagram_type_self_likes' ] = $sb_instagram_type_self_likes;
				$options[ 'sb_instagram_location' ] = $sb_instagram_location;
				$options[ 'sb_instagram_coordinates' ] = $sb_instagram_coordinates;

				$options[ 'sb_instagram_preserve_settings' ] = $sb_instagram_preserve_settings;
				$options[ 'sb_instagram_cache_time' ] = $sb_instagram_cache_time;
				$options[ 'sb_instagram_cache_time_unit' ] = $sb_instagram_cache_time_unit;

				$options[ 'sbi_caching_type' ] = $sbi_caching_type;
				$options[ 'sbi_cache_cron_interval' ] = $sbi_cache_cron_interval;
				$options[ 'sbi_cache_cron_time' ] = $sbi_cache_cron_time;
				$options[ 'sbi_cache_cron_am_pm' ] = $sbi_cache_cron_am_pm;

				//Delete all SBI transients
				global $wpdb;
				$table_name = $wpdb->prefix . "options";
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_sbi\_%')
                    " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
			        " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
			        " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_\$sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_\$sbi\_%')
                    " );

				if ( $sbi_caching_type === 'background' ) {
					delete_option( 'sbi_cron_report' );
					SB_Instagram_Cron_Updater::start_cron_job( $sbi_cache_cron_interval, $sbi_cache_cron_time, $sbi_cache_cron_am_pm );
				}

				global $sb_instagram_posts_manager;
				$sb_instagram_posts_manager->clear_hashtag_errors();

			} //End config tab post

			if( isset($_POST[ $sb_instagram_customize_hidden_field ]) && $_POST[ $sb_instagram_customize_hidden_field ] == 'Y' ) {
				//CUSTOMIZE - GENERAL
				//General
				if (isset($_POST[ 'sb_instagram_width' ]) ) $sb_instagram_width = sanitize_text_field( $_POST[ 'sb_instagram_width' ] );
				if (isset($_POST[ 'sb_instagram_width_unit' ]) ) $sb_instagram_width_unit = $_POST[ 'sb_instagram_width_unit' ];
				(isset($_POST[ 'sb_instagram_feed_width_resp' ]) ) ? $sb_instagram_feed_width_resp = $_POST[ 'sb_instagram_feed_width_resp' ] : $sb_instagram_feed_width_resp = '';
				if (isset($_POST[ 'sb_instagram_height' ]) ) $sb_instagram_height = sanitize_text_field( $_POST[ 'sb_instagram_height' ] );
				if (isset($_POST[ 'sb_instagram_height_unit' ]) ) $sb_instagram_height_unit = $_POST[ 'sb_instagram_height_unit' ];
				if (isset($_POST[ 'sb_instagram_background' ]) ) $sb_instagram_background = $_POST[ 'sb_instagram_background' ];

				//Layout Type
				if (isset($_POST[ 'sb_instagram_layout_type' ]) ) $sb_instagram_layout_type = $_POST[ 'sb_instagram_layout_type' ];
				if (isset($_POST[ 'sb_instagram_highlight_type' ]) ) $sb_instagram_highlight_type = $_POST[ 'sb_instagram_highlight_type' ];
				if (isset($_POST[ 'sb_instagram_highlight_offset' ]) ) $sb_instagram_highlight_offset = $_POST[ 'sb_instagram_highlight_offset' ];
				if (isset($_POST[ 'sb_instagram_highlight_factor' ]) ) $sb_instagram_highlight_factor = $_POST[ 'sb_instagram_highlight_factor' ];
				if (isset($_POST[ 'sb_instagram_highlight_ids' ]) ) $sb_instagram_highlight_ids = $_POST[ 'sb_instagram_highlight_ids' ];
				if (isset($_POST[ 'sb_instagram_highlight_hashtag' ]) ) $sb_instagram_highlight_hashtag = $_POST[ 'sb_instagram_highlight_hashtag' ];

				//Carousel
				isset($_POST[ 'sb_instagram_carousel' ]) ? $sb_instagram_carousel = $_POST[ 'sb_instagram_carousel' ] : $sb_instagram_carousel = '';
				isset($_POST[ 'sb_instagram_carousel_rows' ]) ? $sb_instagram_carousel_rows = $_POST[ 'sb_instagram_carousel_rows' ] : $sb_instagram_carousel_rows = 1;
				isset($_POST[ 'sb_instagram_carousel_loop' ]) ? $sb_instagram_carousel_loop = $_POST[ 'sb_instagram_carousel_loop' ] : $sb_instagram_carousel_loop = 'rewind';
				isset($_POST[ 'sb_instagram_carousel_arrows' ]) ? $sb_instagram_carousel_arrows = $_POST[ 'sb_instagram_carousel_arrows' ] : $sb_instagram_carousel_arrows = '';
				isset($_POST[ 'sb_instagram_carousel_pag' ]) ? $sb_instagram_carousel_pag = $_POST[ 'sb_instagram_carousel_pag' ] : $sb_instagram_carousel_pag = '';
				isset($_POST[ 'sb_instagram_carousel_autoplay' ]) ? $sb_instagram_carousel_autoplay = $_POST[ 'sb_instagram_carousel_autoplay' ] : $sb_instagram_carousel_autoplay = '';
				if (isset($_POST[ 'sb_instagram_carousel_interval' ]) ) $sb_instagram_carousel_interval = sanitize_text_field( $_POST[ 'sb_instagram_carousel_interval' ] );

				//Num/cols
				if (isset($_POST[ 'sb_instagram_num' ]) ) $sb_instagram_num = sanitize_text_field( $_POST[ 'sb_instagram_num' ] );
				if (isset($_POST[ 'sb_instagram_nummobile' ]) ) $sb_instagram_nummobile = sanitize_text_field( $_POST[ 'sb_instagram_nummobile' ] );
				if (isset($_POST[ 'sb_instagram_cols' ]) ) $sb_instagram_cols = sanitize_text_field( $_POST[ 'sb_instagram_cols' ] );
				if (isset($_POST[ 'sb_instagram_colsmobile' ]) ) $sb_instagram_colsmobile = sanitize_text_field( $_POST[ 'sb_instagram_colsmobile' ] );
				if (isset($_POST[ 'sb_instagram_colsmobile' ]) ) $options[ 'sb_instagram_disable_mobile' ] = false;
				if (isset($_POST[ 'sb_instagram_image_padding' ]) ) $sb_instagram_image_padding = sanitize_text_field( $_POST[ 'sb_instagram_image_padding' ] );
				if (isset($_POST[ 'sb_instagram_image_padding_unit' ]) ) $sb_instagram_image_padding_unit = $_POST[ 'sb_instagram_image_padding_unit' ];

				//Header
				isset($_POST[ 'sb_instagram_show_header' ]) ? $sb_instagram_show_header = $_POST[ 'sb_instagram_show_header' ] : $sb_instagram_show_header = '';
				if (isset($_POST[ 'sb_instagram_header_color' ]) ) $sb_instagram_header_color = $_POST[ 'sb_instagram_header_color' ];
				if (isset($_POST[ 'sb_instagram_header_style' ]) ) $sb_instagram_header_style = $_POST[ 'sb_instagram_header_style' ];
				isset($_POST[ 'sb_instagram_show_followers' ]) ? $sb_instagram_show_followers = $_POST[ 'sb_instagram_show_followers' ] : $sb_instagram_show_followers = '';
				isset($_POST[ 'sb_instagram_show_bio' ]) ? $sb_instagram_show_bio = $_POST[ 'sb_instagram_show_bio' ] : $sb_instagram_show_bio = '';
				if ( function_exists( 'sanitize_textarea_field' ) ) {
					isset($_POST[ 'sb_instagram_custom_bio' ]) ? $sb_instagram_custom_bio = sanitize_textarea_field( $_POST[ 'sb_instagram_custom_bio' ] ) : $sb_instagram_custom_bio = '';
				} else {
					isset($_POST[ 'sb_instagram_custom_bio' ]) ? $sb_instagram_custom_bio = sanitize_text_field( $_POST[ 'sb_instagram_custom_bio' ] ) : $sb_instagram_custom_bio = '';
				}
				isset($_POST[ 'sb_instagram_custom_avatar' ]) ? $sb_instagram_custom_avatar = sanitize_text_field( $_POST[ 'sb_instagram_custom_avatar' ] ) : $sb_instagram_custom_avatar = '';
				if (isset($_POST[ 'sb_instagram_header_primary_color' ]) ) $sb_instagram_header_primary_color = $_POST[ 'sb_instagram_header_primary_color' ];
				if (isset($_POST[ 'sb_instagram_header_secondary_color' ]) ) $sb_instagram_header_secondary_color = $_POST[ 'sb_instagram_header_secondary_color' ];
				if (isset($_POST[ 'sb_instagram_header_size' ]) ) $sb_instagram_header_size = $_POST[ 'sb_instagram_header_size' ];

				isset($_POST[ 'sb_instagram_outside_scrollable' ]) ? $sb_instagram_outside_scrollable = $_POST[ 'sb_instagram_outside_scrollable' ] : $sb_instagram_outside_scrollable = '';

				isset($_POST[ 'sb_instagram_stories' ]) ? $sb_instagram_stories = $_POST[ 'sb_instagram_stories' ] : $sb_instagram_stories = '';
				isset($_POST[ 'sb_instagram_stories_time' ]) ? $sb_instagram_stories_time = $_POST[ 'sb_instagram_stories_time' ] : $sb_instagram_stories = 5000;

				//Load More button
				isset($_POST[ 'sb_instagram_show_btn' ]) ? $sb_instagram_show_btn = $_POST[ 'sb_instagram_show_btn' ] : $sb_instagram_show_btn = '';
				if (isset($_POST[ 'sb_instagram_btn_background' ]) ) $sb_instagram_btn_background = $_POST[ 'sb_instagram_btn_background' ];
				if (isset($_POST[ 'sb_instagram_btn_text_color' ]) ) $sb_instagram_btn_text_color = $_POST[ 'sb_instagram_btn_text_color' ];
				if (isset($_POST[ 'sb_instagram_btn_text' ]) ) $sb_instagram_btn_text = sanitize_text_field( $_POST[ 'sb_instagram_btn_text' ] );
				//AutoScroll
				isset($_POST[ 'sb_instagram_autoscroll' ]) ? $sb_instagram_autoscroll = $_POST[ 'sb_instagram_autoscroll' ] : $sb_instagram_autoscroll = '';
				if (isset($_POST[ 'sb_instagram_autoscrolldistance' ]) ) $sb_instagram_autoscrolldistance = sanitize_text_field( $_POST[ 'sb_instagram_autoscrolldistance' ] );

				//Follow button
				isset($_POST[ 'sb_instagram_show_follow_btn' ]) ? $sb_instagram_show_follow_btn = $_POST[ 'sb_instagram_show_follow_btn' ] : $sb_instagram_show_follow_btn = '';
				if (isset($_POST[ 'sb_instagram_folow_btn_background' ]) ) $sb_instagram_folow_btn_background = $_POST[ 'sb_instagram_folow_btn_background' ];
				if (isset($_POST[ 'sb_instagram_follow_btn_text_color' ]) ) $sb_instagram_follow_btn_text_color = $_POST[ 'sb_instagram_follow_btn_text_color' ];
				if (isset($_POST[ 'sb_instagram_follow_btn_text' ]) ) $sb_instagram_follow_btn_text = sanitize_text_field( $_POST[ 'sb_instagram_follow_btn_text' ] );

				//General
				$options[ 'sb_instagram_width' ] = $sb_instagram_width;
				$options[ 'sb_instagram_width_unit' ] = $sb_instagram_width_unit;
				$options[ 'sb_instagram_feed_width_resp' ] = $sb_instagram_feed_width_resp;
				$options[ 'sb_instagram_height' ] = $sb_instagram_height;
				$options[ 'sb_instagram_height_unit' ] = $sb_instagram_height_unit;
				$options[ 'sb_instagram_background' ] = $sb_instagram_background;
				//Layout
				$options[ 'sb_instagram_layout_type' ] = $sb_instagram_layout_type;
				$options[ 'sb_instagram_highlight_type' ] = $sb_instagram_highlight_type;
				$options[ 'sb_instagram_highlight_offset' ] = $sb_instagram_highlight_offset;
				$options[ 'sb_instagram_highlight_factor' ] = $sb_instagram_highlight_factor;
				$options[ 'sb_instagram_highlight_ids' ] = $sb_instagram_highlight_ids;
				$options[ 'sb_instagram_highlight_hashtag' ] = $sb_instagram_highlight_hashtag;
				//Carousel
				$options[ 'sb_instagram_carousel' ] = $sb_instagram_carousel;
				$options[ 'sb_instagram_carousel_arrows' ] = $sb_instagram_carousel_arrows;
				$options[ 'sb_instagram_carousel_pag' ] = $sb_instagram_carousel_pag;
				$options[ 'sb_instagram_carousel_autoplay' ] = $sb_instagram_carousel_autoplay;
				$options[ 'sb_instagram_carousel_interval' ] = $sb_instagram_carousel_interval;
				$options[ 'sb_instagram_carousel_rows' ] = $sb_instagram_carousel_rows;
				$options[ 'sb_instagram_carousel_loop' ] = $sb_instagram_carousel_loop;
				//Num/cols
				$options[ 'sb_instagram_num' ] = $sb_instagram_num;
				$options[ 'sb_instagram_nummobile' ] = $sb_instagram_nummobile;
				$options[ 'sb_instagram_cols' ] = $sb_instagram_cols;
				$options[ 'sb_instagram_colsmobile' ] = $sb_instagram_colsmobile;
				$options[ 'sb_instagram_image_padding' ] = $sb_instagram_image_padding;
				$options[ 'sb_instagram_image_padding_unit' ] = $sb_instagram_image_padding_unit;
				//Header
				$options[ 'sb_instagram_show_header' ] = $sb_instagram_show_header;
				$options[ 'sb_instagram_header_color' ] = $sb_instagram_header_color;
				$options[ 'sb_instagram_header_style' ] = $sb_instagram_header_style;
				$options[ 'sb_instagram_show_followers' ] = $sb_instagram_show_followers;
				$options[ 'sb_instagram_show_bio' ] = $sb_instagram_show_bio;
				$options[ 'sb_instagram_custom_bio' ] = $sb_instagram_custom_bio;
				$options[ 'sb_instagram_custom_avatar' ] = $sb_instagram_custom_avatar;
				$options[ 'sb_instagram_header_primary_color' ] = $sb_instagram_header_primary_color;
				$options[ 'sb_instagram_header_secondary_color' ] = $sb_instagram_header_secondary_color;
				$options[ 'sb_instagram_header_size' ] = $sb_instagram_header_size;
				$options[ 'sb_instagram_outside_scrollable' ] = $sb_instagram_outside_scrollable;
				$options[ 'sb_instagram_stories' ] = $sb_instagram_stories;
				$options[ 'sb_instagram_stories_time' ] = $sb_instagram_stories_time;

				//Load More button
				$options[ 'sb_instagram_show_btn' ] = $sb_instagram_show_btn;
				$options[ 'sb_instagram_btn_background' ] = $sb_instagram_btn_background;
				$options[ 'sb_instagram_btn_text_color' ] = $sb_instagram_btn_text_color;
				$options[ 'sb_instagram_btn_text' ] = $sb_instagram_btn_text;
				//AutoScroll
				$options[ 'sb_instagram_autoscroll' ] = $sb_instagram_autoscroll;
				$options[ 'sb_instagram_autoscrolldistance' ] = $sb_instagram_autoscrolldistance;
				//Follow button
				$options[ 'sb_instagram_show_follow_btn' ] = $sb_instagram_show_follow_btn;
				$options[ 'sb_instagram_moderation_mode' ] = $sb_instagram_moderation_mode;
				$options[ 'sb_instagram_folow_btn_background' ] = $sb_instagram_folow_btn_background;
				$options[ 'sb_instagram_follow_btn_text_color' ] = $sb_instagram_follow_btn_text_color;
				$options[ 'sb_instagram_follow_btn_text' ] = $sb_instagram_follow_btn_text;

			}

			if( isset($_POST[ $sb_instagram_customize_posts_hidden_field ]) && $_POST[ $sb_instagram_customize_posts_hidden_field ] == 'Y' ) {


				//CUSTOMIZE - POSTS
				//Photos
				if (isset($_POST[ 'sb_instagram_sort' ]) ) $sb_instagram_sort = $_POST[ 'sb_instagram_sort' ];
				if (isset($_POST[ 'sb_instagram_image_res' ]) ) $sb_instagram_image_res = $_POST[ 'sb_instagram_image_res' ];
				if (isset($_POST[ 'sb_instagram_media_type' ]) ) $sb_instagram_media_type = $_POST[ 'sb_instagram_media_type' ];
				(isset($_POST[ 'sb_instagram_disable_lightbox' ]) ) ? $sb_instagram_disable_lightbox = $_POST[ 'sb_instagram_disable_lightbox' ] : $sb_instagram_disable_lightbox = '';
				(isset($_POST[ 'sb_instagram_captionlinks' ]) ) ? $sb_instagram_captionlinks = $_POST[ 'sb_instagram_captionlinks' ] : $sb_instagram_captionlinks = '';
				(isset($_POST[ 'sb_instagram_offset' ]) ) ? $sb_instagram_offset = $_POST[ 'sb_instagram_offset' ] : $sb_instagram_offset = 0;

				//Photo hover style
				if (isset($_POST[ 'sb_hover_background' ]) ) $sb_hover_background = $_POST[ 'sb_hover_background' ];
				(isset($_POST[ 'sb_hover_text' ]) && !empty($_POST[ 'sb_hover_text' ]) ) ? $sb_hover_text = $_POST[ 'sb_hover_text' ] : $sb_hover_text = '#fff';
				(isset($_POST[ 'sbi_hover_inc_username' ]) ) ? $sbi_hover_inc_username = $_POST[ 'sbi_hover_inc_username' ] : $sbi_hover_inc_username = '';
				(isset($_POST[ 'sbi_hover_inc_icon' ]) ) ? $sbi_hover_inc_icon = $_POST[ 'sbi_hover_inc_icon' ] : $sbi_hover_inc_icon = '';
				(isset($_POST[ 'sbi_hover_inc_date' ]) ) ? $sbi_hover_inc_date = $_POST[ 'sbi_hover_inc_date' ] : $sbi_hover_inc_date = '';
				(isset($_POST[ 'sbi_hover_inc_instagram' ]) ) ? $sbi_hover_inc_instagram = $_POST[ 'sbi_hover_inc_instagram' ] : $sbi_hover_inc_instagram = '';
				(isset($_POST[ 'sbi_hover_inc_location' ]) ) ? $sbi_hover_inc_location = $_POST[ 'sbi_hover_inc_location' ] : $sbi_hover_inc_location = '';
				(isset($_POST[ 'sbi_hover_inc_caption' ]) ) ? $sbi_hover_inc_caption = $_POST[ 'sbi_hover_inc_caption' ] : $sbi_hover_inc_caption = '';
				(isset($_POST[ 'sbi_hover_inc_likes' ]) ) ? $sbi_hover_inc_likes = $_POST[ 'sbi_hover_inc_likes' ] : $sbi_hover_inc_likes = '';

				//Text
				isset($_POST[ 'sb_instagram_show_caption' ]) ? $sb_instagram_show_caption = $_POST[ 'sb_instagram_show_caption' ] : $sb_instagram_show_caption = '';
				if (isset($_POST[ 'sb_instagram_caption_length' ]) ) $sb_instagram_caption_length = sanitize_text_field( $_POST[ 'sb_instagram_caption_length' ] );
				if (isset($_POST[ 'sb_instagram_caption_color' ]) ) $sb_instagram_caption_color = $_POST[ 'sb_instagram_caption_color' ];
				if (isset($_POST[ 'sb_instagram_caption_size' ]) ) $sb_instagram_caption_size = $_POST[ 'sb_instagram_caption_size' ];

				//Likes & Comments Icons (meta)
				isset($_POST[ 'sb_instagram_show_meta' ]) ? $sb_instagram_show_meta = $_POST[ 'sb_instagram_show_meta' ] : $sb_instagram_show_meta = '';
				if (isset($_POST[ 'sb_instagram_meta_color' ]) ) $sb_instagram_meta_color = $_POST[ 'sb_instagram_meta_color' ];
				if (isset($_POST[ 'sb_instagram_meta_size' ]) ) $sb_instagram_meta_size = $_POST[ 'sb_instagram_meta_size' ];

				//Lightbox comments
				(isset($_POST[ 'sb_instagram_lightbox_comments' ]) ) ? $sb_instagram_lightbox_comments = $_POST[ 'sb_instagram_lightbox_comments' ] : $sb_instagram_lightbox_comments = '';
				if(isset($_POST[ 'sb_instagram_num_comments' ]) ) $sb_instagram_num_comments = sanitize_text_field( $_POST[ 'sb_instagram_num_comments' ] );

				//Photos
				$options[ 'sb_instagram_sort' ] = $sb_instagram_sort;
				$options[ 'sb_instagram_image_res' ] = $sb_instagram_image_res;
				$options[ 'sb_instagram_media_type' ] = $sb_instagram_media_type;
				$options[ 'sb_instagram_disable_lightbox' ] = $sb_instagram_disable_lightbox;
				$options[ 'sb_instagram_captionlinks' ] = $sb_instagram_captionlinks;
				$options[ 'sb_instagram_offset' ] = $sb_instagram_offset;
				//Photo hover style
				$options[ 'sb_hover_background' ] = $sb_hover_background;
				$options[ 'sb_hover_text' ] = $sb_hover_text;
				$options[ 'sbi_hover_inc_username' ] = $sbi_hover_inc_username;
				$options[ 'sbi_hover_inc_icon' ] = $sbi_hover_inc_icon;
				$options[ 'sbi_hover_inc_date' ] = $sbi_hover_inc_date;
				$options[ 'sbi_hover_inc_instagram' ] = $sbi_hover_inc_instagram;
				$options[ 'sbi_hover_inc_location' ] = $sbi_hover_inc_location;
				$options[ 'sbi_hover_inc_caption' ] = $sbi_hover_inc_caption;
				$options[ 'sbi_hover_inc_likes' ] = $sbi_hover_inc_likes;
				//Text
				$options[ 'sb_instagram_show_caption' ] = $sb_instagram_show_caption;
				$options[ 'sb_instagram_caption_length' ] = $sb_instagram_caption_length;
				$options[ 'sb_instagram_caption_color' ] = $sb_instagram_caption_color;
				$options[ 'sb_instagram_caption_size' ] = $sb_instagram_caption_size;
				//Meta
				$options[ 'sb_instagram_show_meta' ] = $sb_instagram_show_meta;
				$options[ 'sb_instagram_meta_color' ] = $sb_instagram_meta_color;
				$options[ 'sb_instagram_meta_size' ] = $sb_instagram_meta_size;
				//Lightbox Comments
				$options[ 'sb_instagram_lightbox_comments' ] = $sb_instagram_lightbox_comments;
				$options[ 'sb_instagram_num_comments' ] = $sb_instagram_num_comments;

			}
			if ($sb_instagram_ex_apply_to === 'all') {
				if (isset($_POST[ 'sb_instagram_exclude_words' ]) ) $sb_instagram_exclude_words = sanitize_text_field( $_POST[ 'sb_instagram_exclude_words' ] );
			} else {
				$sb_instagram_exclude_words = '';
			}
			if ($sb_instagram_inc_apply_to === 'all') {
				if (isset($_POST[ 'sb_instagram_include_words' ]) ) $sb_instagram_include_words = sanitize_text_field( $_POST[ 'sb_instagram_include_words' ] );
			} else {
				$sb_instagram_include_words = '';
			}
			if (isset($_POST[ 'sb_instagram_ex_apply_to' ]) ) $sb_instagram_ex_apply_to = $_POST[ 'sb_instagram_ex_apply_to' ];
			if (isset($_POST[ 'sb_instagram_inc_apply_to' ]) ) $sb_instagram_inc_apply_to = $_POST[ 'sb_instagram_inc_apply_to' ];

			//Moderation
			isset($_POST[ 'sb_instagram_moderation_mode' ]) ? $sb_instagram_moderation_mode = $_POST[ 'sb_instagram_moderation_mode' ] : $sb_instagram_moderation_mode = 'visual';
			if (isset($_POST[ 'sb_instagram_hide_photos' ]) ) $sb_instagram_hide_photos = $_POST[ 'sb_instagram_hide_photos' ];
			if (isset($_POST[ 'sb_instagram_block_users' ]) ) $sb_instagram_block_users = $_POST[ 'sb_instagram_block_users' ];
			if (isset($_POST[ 'sb_instagram_show_users' ]) ) $sb_instagram_show_users = sanitize_text_field( $_POST[ 'sb_instagram_show_users' ] );

			if( isset($_POST[ $sb_instagram_customize_moderation_hidden_field ]) && $_POST[ $sb_instagram_customize_moderation_hidden_field ] == 'Y' ) {

				$options[ 'sb_instagram_exclude_words' ] = $sb_instagram_exclude_words;
				$options[ 'sb_instagram_include_words' ] = $sb_instagram_include_words;
				$options[ 'sb_instagram_ex_apply_to' ] = $sb_instagram_ex_apply_to;
				$options[ 'sb_instagram_inc_apply_to' ] = $sb_instagram_inc_apply_to;
				//Moderation
				$options[ 'sb_instagram_moderation_mode' ] = $sb_instagram_moderation_mode;
				$options[ 'sb_instagram_hide_photos' ] = $sb_instagram_hide_photos;
				$options[ 'sb_instagram_block_users' ] = $sb_instagram_block_users;
				$options[ 'sb_instagram_show_users' ] = $sb_instagram_show_users;

			}

			if( isset($_POST[ $sb_instagram_customize_advanced_hidden_field ]) && $_POST[ $sb_instagram_customize_advanced_hidden_field ] == 'Y' ) {

				//CUSTOMIZE - ADVANCED
				if (isset($_POST[ 'sb_instagram_custom_css' ]) ) $sb_instagram_custom_css = $_POST[ 'sb_instagram_custom_css' ];
				if (isset($_POST[ 'sb_instagram_custom_js' ]) ) $sb_instagram_custom_js = $_POST[ 'sb_instagram_custom_js' ];
				//Misc
				isset($_POST[ 'sb_instagram_ajax_theme' ]) ? $sb_instagram_ajax_theme = $_POST[ 'sb_instagram_ajax_theme' ] : $sb_instagram_ajax_theme = '';
				//enqueue_js_in_head
				isset($_POST[ 'enqueue_js_in_head' ]) ? $enqueue_js_in_head = $_POST[ 'enqueue_js_in_head' ] : $enqueue_js_in_head = '';
				isset($_POST[ 'disable_js_image_loading' ]) ? $disable_js_image_loading = $_POST[ 'disable_js_image_loading' ] : $disable_js_image_loading = '';
				isset($_POST[ 'sb_instagram_disable_resize' ]) ? $sb_instagram_disable_resize = $_POST[ 'sb_instagram_disable_resize' ] : $sb_instagram_disable_resize = '';
				isset($_POST[ 'sb_instagram_favor_local' ]) ? $sb_instagram_favor_local = sanitize_text_field( $_POST[ 'sb_instagram_favor_local' ] ) : $sb_instagram_favor_local = '';

				if (isset($_POST[ 'sb_instagram_requests_max' ]) ) $sb_instagram_requests_max = $_POST[ 'sb_instagram_requests_max' ];
				if (isset($_POST[ 'sb_instagram_minnum' ]) ) $sb_instagram_minnum = $_POST[ 'sb_instagram_minnum' ];

				if (isset($_POST[ 'sb_instagram_cron' ]) ) $sb_instagram_cron = $_POST[ 'sb_instagram_cron' ];
				isset($_POST[ 'sb_instagram_disable_font' ]) ? $sb_instagram_disable_font = $_POST[ 'sb_instagram_disable_font' ] : $sb_instagram_disable_font = '';
				isset($_POST[ 'sb_instagram_backup' ]) ? $sb_instagram_backup = $_POST[ 'sb_instagram_backup' ] : $sb_instagram_backup = '';
				isset($_POST[ 'sb_ajax_initial' ]) ? $sb_ajax_initial = $_POST[ 'sb_ajax_initial' ] : $sb_ajax_initial = '';

				//
				isset($_POST[ 'enqueue_css_in_shortcode' ]) ? $enqueue_css_in_shortcode = $_POST[ 'enqueue_css_in_shortcode' ] : $enqueue_css_in_shortcode = '';
				isset($_POST[ 'sb_instagram_disable_mob_swipe' ]) ? $sb_instagram_disable_mob_swipe = $_POST[ 'sb_instagram_disable_mob_swipe' ] : $sb_instagram_disable_mob_swipe = '';
				isset($_POST[ 'sbi_font_method' ]) ? $sbi_font_method = $_POST[ 'sbi_font_method' ] : $sbi_font_method = '';
				isset($_POST[ 'sbi_br_adjust' ]) ? $sbi_br_adjust = $_POST[ 'sbi_br_adjust' ] : $sbi_br_adjust = '';
				if ( is_multisite() ) {
					isset($_POST[ 'sbi_super_admin_only' ]) ? $sbi_super_admin_only = $_POST[ 'sbi_super_admin_only' ] : $sbi_super_admin_only = '';
				}
				//Advanced
				$options[ 'sb_instagram_custom_css' ] = $sb_instagram_custom_css;
				$options[ 'sb_instagram_custom_js' ] = $sb_instagram_custom_js;
				//Misc
				$options[ 'sb_instagram_ajax_theme' ] = $sb_instagram_ajax_theme;
				$options[ 'enqueue_js_in_head' ] = $enqueue_js_in_head;
				$options[ 'disable_js_image_loading' ] = $disable_js_image_loading;
				$options[ 'sb_instagram_disable_resize' ] = $sb_instagram_disable_resize;
				$options[ 'sb_instagram_favor_local' ] = $sb_instagram_favor_local;
				$options[ 'sb_instagram_requests_max' ] = $sb_instagram_requests_max;
				$options[ 'sb_instagram_minnum' ] = $sb_instagram_minnum;

				$options[ 'sb_instagram_cron' ] = $sb_instagram_cron;
				$options[ 'sb_instagram_disable_font' ] = $sb_instagram_disable_font;
				$options[ 'sb_ajax_initial' ] = $sb_ajax_initial;

				$options['sb_instagram_backup'] = $sb_instagram_backup;
				$options['enqueue_css_in_shortcode'] = $enqueue_css_in_shortcode;
				$options['sb_instagram_disable_mob_swipe'] = $sb_instagram_disable_mob_swipe;
				$options['sbi_font_method'] = $sbi_font_method;
				$options['sbi_br_adjust'] = $sbi_br_adjust;

				if ( is_multisite() ) {
					$options['sbi_super_admin_only'] = $sbi_super_admin_only;
					update_site_option( 'sbi_super_admin_only', $sbi_super_admin_only );
				}

				isset($_POST[ 'sb_instagram_media_vine' ]) ? $sb_instagram_media_vine = $_POST[ 'sb_instagram_media_vine' ] : $sb_instagram_media_vine = '';
				$options['sb_instagram_media_vine'] = $sb_instagram_media_vine;
				isset($_POST[ 'sb_instagram_custom_template' ]) ? $sb_instagram_custom_template = $_POST[ 'sb_instagram_custom_template' ] : $sb_instagram_custom_template = '';
				$options['custom_template'] = $sb_instagram_custom_template;
				isset($_POST[ 'sb_instagram_disable_admin_notice' ]) ? $sb_instagram_disable_admin_notice = $_POST[ 'sb_instagram_disable_admin_notice' ] : $sb_instagram_disable_admin_notice = '';
				$options['disable_admin_notice'] = $sb_instagram_disable_admin_notice;
				//
                isset($_POST[ 'sb_instagram_enable_email_report' ]) ? $sb_instagram_enable_email_report = $_POST[ 'sb_instagram_enable_email_report' ] : $sb_instagram_enable_email_report = '';
				$options['enable_email_report'] = $sb_instagram_enable_email_report;
				isset($_POST[ 'sb_instagram_email_notification' ]) ? $sb_instagram_email_notification = $_POST[ 'sb_instagram_email_notification' ] : $sb_instagram_email_notification = '';
				$original = $options['email_notification'];
				$options['email_notification'] = $sb_instagram_email_notification;
				isset($_POST[ 'sb_instagram_email_notification_addresses' ]) ? $sb_instagram_email_notification_addresses = $_POST[ 'sb_instagram_email_notification_addresses' ] : $sb_instagram_email_notification_addresses = get_option( 'admin_email' );
				$options['email_notification_addresses'] = $sb_instagram_email_notification_addresses;

				if ( $original !== $sb_instagram_email_notification && $sb_instagram_enable_email_report === 'on' ){
					//Clear the existing cron event
					wp_clear_scheduled_hook('sb_instagram_feed_issue_email');

					$input = sanitize_text_field($_POST[ 'sb_instagram_email_notification' ] );
					$timestamp = strtotime( 'next ' . $input );

					if ( $timestamp - (3600 * 1) < time() ) {
						$timestamp = $timestamp + (3600 * 24 * 7);
					}
					$six_am_local = $timestamp + sbi_get_utc_offset() + (6*60*60);

					wp_schedule_event( $six_am_local, 'sbiweekly', 'sb_instagram_feed_issue_email' );
				}

				//Delete all SBI transients
				global $wpdb;
				$table_name = $wpdb->prefix . "options";
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_sbi\_%')
                    " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
			        " );
				$wpdb->query( "
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
			        " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_\$sbi\_%')
                    " );
				$wpdb->query( "
                    DELETE
                    FROM $table_name
                    WHERE `option_name` LIKE ('%\_transient\_timeout\_\$sbi\_%')
                    " );
				if( $sb_instagram_cron == 'no' ) wp_clear_scheduled_hook('sb_instagram_cron_job');

				//Run cron when Misc settings are saved
				if( $sb_instagram_cron == 'yes' ){
					//Clear the existing cron event
					wp_clear_scheduled_hook('sb_instagram_cron_job');

					$sb_instagram_cache_time = $options[ 'sb_instagram_cache_time' ];
					$sb_instagram_cache_time_unit = $options[ 'sb_instagram_cache_time_unit' ];

					//Set the event schedule based on what the caching time is set to
					$sb_instagram_cron_schedule = 'hourly';
					if( $sb_instagram_cache_time_unit == 'hours' && $sb_instagram_cache_time > 5 ) $sb_instagram_cron_schedule = 'twicedaily';
					if( $sb_instagram_cache_time_unit == 'days' ) $sb_instagram_cron_schedule = 'daily';

					wp_schedule_event(time(), $sb_instagram_cron_schedule, 'sb_instagram_cron_job');

					sb_instagram_clear_page_caches();
				}

			}

			//End customize tab post requests

			//Save the settings to the settings array
			update_option( 'sb_instagram_settings', $options );
			$sb_instagram_using_custom_sizes = get_option( 'sb_instagram_using_custom_sizes');
			if ( isset( $_POST['sb_instagram_using_custom_sizes'] ) ) {
				$sb_instagram_using_custom_sizes = (int)$_POST['sb_instagram_using_custom_sizes'];
			} elseif( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] === 'customize' ) {
				$sb_instagram_using_custom_sizes = false;
			}
			update_option( 'sb_instagram_using_custom_sizes', $sb_instagram_using_custom_sizes );

			?>
            <div class="updated"><p><strong><?php _e('Settings saved.', 'instagram-feed' ); ?></strong></p></div>
		<?php } ?>

	<?php } //End nonce check ?>


    <div id="sbi_admin" class="wrap">

        <div id="header">
            <h1><?php _e('Instagram Feed', 'instagram-feed' ); ?></h1>
        </div>

		<?php sbi_expiration_notice(); ?>

	    <?php

	    $returned_data = sbi_get_connected_accounts_data( $sb_instagram_at );
	    $sb_instagram_at = $returned_data['access_token'];
	    $connected_accounts = $returned_data['connected_accounts'];
	    $user_feeds_returned = isset(  $returned_data['user_ids'] ) ? $returned_data['user_ids'] : false;
	    if ( $user_feeds_returned ) {
		    $user_feed_ids = $user_feeds_returned;
	    } else {
		    $user_feed_ids = ! is_array( $sb_instagram_user_id ) ? explode( ',', $sb_instagram_user_id ) : $sb_instagram_user_id;
	    }

	    $tagged_feed_ids = ! is_array( $sb_instagram_tagged_ids ) ? explode( ',', $sb_instagram_tagged_ids ) : $sb_instagram_tagged_ids;
	    $new_user_name = false;




	    if( isset($_GET['access_token']) && isset($_GET['graph_api']) && empty($_POST) ) { ?>
		    <?php
		    $access_token = sbi_maybe_clean(urldecode($_GET['access_token']));
		    //
		    $url = 'https://graph.facebook.com/me/accounts?fields=instagram_business_account,access_token&limit=500&access_token='.$access_token;
		    $args = array(
			    'timeout' => 60,
			    'sslverify' => false
		    );
		    $result = wp_remote_get( $url, $args );
		    $pages_data = '{}';
		    if ( ! is_wp_error( $result ) ) {
			    $pages_data = $result['body'];
		    } else {
			    $page_error = $result;
		    }

		    $pages_data_arr = json_decode($pages_data);
		    $num_accounts = 0;
		    if(isset($pages_data_arr)){
			    $num_accounts = is_array( $pages_data_arr->data ) ? count( $pages_data_arr->data ) : 0;
		    }
		    ?>
            <div id="sbi_config_info" class="sb_list_businesses sbi_num_businesses_<?php echo $num_accounts; ?>">
                <div class="sbi_config_modal">
                    <div class="sbi-managed-pages">
					    <?php if ( isset( $page_error ) && isset( $page_error->errors ) ) {
						    foreach ($page_error->errors as $key => $item) {
							    echo '<div class="sbi_user_id_error" style="display:block;"><strong>Connection Error: </strong>' . $key . ': ' . $item[0] . '</div>';
						    }
					    }
					    ?>
					    <?php if( empty($pages_data_arr->data) ) : ?>
                            <span id="sbi-bus-account-error">
                            <p style="margin-top: 5px;"><b style="font-size: 16px">Couldn't find Business Profile</b><br />
                            Uh oh. It looks like this Facebook account is not currently connected to an Instagram Business profile. Please check that you are logged into the <a href="https://www.facebook.com/" target="_blank">Facebook account</a> in this browser which is associated with your Instagram Business Profile.</p>
                            <p><b style="font-size: 16px">Why do I need a Business Profile?</b><br />
                            A Business Profile is only required if you are displaying a Hashtag feed. If you want to display a regular User feed then you can do this by selecting to connect a Personal account instead. For directions on how to convert your Personal profile into a Business profile please <a href="#" target="_blank">see here</a>.</p>
                            </span>

					    <?php elseif ( $num_accounts === 0 ): ?>
                            <span id="sbi-bus-account-error">
                            <p style="margin-top: 5px;"><b style="font-size: 16px">Couldn't find Business Profile</b><br />
                            Uh oh. It looks like this Facebook account is not currently connected to an Instagram Business profile. Please check that you are logged into the <a href="https://www.facebook.com/" target="_blank">Facebook account</a> in this browser which is associated with your Instagram Business Profile.</p>
                            <p>If you are, in fact, logged-in to the correct account please make sure you have Instagram accounts connected with your Facebook account by following <a href="#" target="_blank">this FAQ</a></p>
                            </span>
					    <?php else: ?>
                            <p class="sbi-managed-page-intro"><b style="font-size: 16px;">Instagram Business profiles for this account</b><br /><i style="color: #666;">Note: In order to display a Hashtag feed you first need to select a Business profile below.</i></p>
						    <?php if ( $num_accounts > 1 ) : ?>
                                <div class="sbi-managed-page-select-all"><input type="checkbox" id="sbi-select-all" class="sbi-select-all"><label for="sbi-select-all">Select All</label></div>
						    <?php endif; ?>
                            <div class="sbi-scrollable-accounts">

							    <?php foreach ( $pages_data_arr->data as $page => $page_data ) : ?>

								    <?php if( isset( $page_data->instagram_business_account ) ) :

									    $instagram_business_id = $page_data->instagram_business_account->id;

									    $page_access_token = isset( $page_data->access_token ) ? $page_data->access_token : '';

									    //Make another request to get page info
									    $instagram_account_url = 'https://graph.facebook.com/'.$instagram_business_id.'?fields=name,username,profile_picture_url&access_token='.$access_token;

									    $args = array(
										    'timeout' => 60,
										    'sslverify' => false
									    );
									    $result = wp_remote_get( $instagram_account_url, $args );
									    $instagram_account_info = '{}';
									    if ( ! is_wp_error( $result ) ) {
										    $instagram_account_info = $result['body'];
									    } else {
										    $page_error = $result;
									    }

									    $instagram_account_data = json_decode($instagram_account_info);

									    $instagram_biz_img = isset( $instagram_account_data->profile_picture_url ) ? $instagram_account_data->profile_picture_url : false;
									    $selected_class = $instagram_business_id == $sb_instagram_user_id ? ' sbi-page-selected' : '';

									    ?>
									    <?php if ( isset( $page_error ) && isset( $page_error->errors ) ) :
									    foreach ($page_error->errors as $key => $item) {
										    echo '<div class="sbi_user_id_error" style="display:block;"><strong>Connection Error: </strong>' . $key . ': ' . $item[0] . '</div>';
									    }
								    else : ?>
                                        <div class="sbi-managed-page<?php echo $selected_class; ?>" data-page-token="<?php echo esc_attr( $page_access_token ); ?>" data-token="<?php echo esc_attr( $access_token ); ?>" data-page-id="<?php echo esc_attr( $instagram_business_id ); ?>">
                                            <div class="sbi-add-checkbox">
                                                <input id="sbi-<?php echo esc_attr( $instagram_business_id ); ?>" type="checkbox" name="sbi_managed_pages[]" value="<?php echo esc_attr( $instagram_account_info ); ?>">
                                            </div>
                                            <div class="sbi-managed-page-details">
                                                <label for="sbi-<?php echo esc_attr( $instagram_business_id ); ?>"><img class="sbi-page-avatar" border="0" height="50" width="50" src="<?php echo esc_url( $instagram_biz_img ); ?>"><b style="font-size: 16px;"><?php echo esc_html( $instagram_account_data->name ); ?></b>
                                                    <br />@<?php echo esc_html( $instagram_account_data->username); ?><span style="font-size: 11px; margin-left: 5px;">(<?php echo esc_html( $instagram_business_id ); ?>)</span></label>
                                            </div>
                                        </div>
								    <?php endif; ?>

								    <?php endif; ?>

							    <?php endforeach; ?>

                            </div> <!-- end scrollable -->
                            <p style="font-size: 11px; line-height: 1.5; margin-bottom: 0;"><i style="color: #666;">*<?php echo sprintf( __( 'Changing the password, updating privacy settings, or removing page admins for the related Facebook page may require %smanually reauthorizing our app%s to reconnect an account.', 'instagram-feed' ), '<a href="#" target="_blank" rel="noopener noreferrer">', '</a>' ); ?></i></p>

                            <a href="JavaScript:void(0);" id="sbi-connect-business-accounts" class="button button-primary" disabled="disabled" style="margin-top: 20px;">Connect Accounts</a>

					    <?php endif; ?>

                        <a href="JavaScript:void(0);" class="sbi_modal_close"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            </div>
	    <?php } elseif ( isset( $_GET['access_token'] ) && isset( $_GET['account_type'] ) && empty( $_POST ) ) {
		    $access_token = sanitize_text_field( $_GET['access_token'] );
		    $account_type = sanitize_text_field( $_GET['account_type'] );
		    $user_id = sanitize_text_field( $_GET['id'] );
		    $user_name = sanitize_text_field( $_GET['username'] );
		    $expires_in = (int)$_GET['expires_in'];
		    $expires_timestamp = time() + $expires_in;

		    $new_account_details = array(
			    'access_token' => $access_token,
			    'account_type' => $account_type,
			    'user_id' => $user_id,
			    'username' => $user_name,
			    'expires_timestamp' => $expires_timestamp,
			    'type' => 'basic'
		    );


		    $matches_existing_personal = sbi_matches_existing_personal( $new_account_details );
		    $button_text = $matches_existing_personal ? __( 'Update This Account', 'instagram-feed' ) : __( 'Connect This Account', 'instagram-feed' );

		    $account_json = wp_json_encode( $new_account_details );

		    $already_connected_as_business_account = (isset( $connected_accounts[ $user_id ] ) && $connected_accounts[ $user_id ]['type'] === 'business');

		    ?>

            <div id="sbi_config_info" class="sb_get_token">
                <div class="sbi_config_modal">
                    <div class="sbi_ca_username"><strong><?php echo esc_html( $user_name ); ?></strong></div>
                    <form action="<?php echo admin_url( 'admin.php?page=sb-instagram-feed' ); ?>" method="post">
                        <p class="sbi_submit">
						    <?php if ( $already_connected_as_business_account ) :
							    _e( 'The Instagram account you are logged into is already connected as a "business" account. Remove the business account if you\'d like to connect as a basic account instead (not recommended).', 'instagram-feed' );
							    ?>
						    <?php else : ?>
                                <input type="submit" name="sbi_submit" id="sbi_connect_account" class="button button-primary" value="<?php echo esc_html( $button_text ); ?>">
						    <?php  endif; ?>
                            <input type="hidden" name="sbi_account_json" value="<?php echo esc_attr( $account_json ) ; ?>">
                            <input type="hidden" name="sbi_connect_username" value="<?php echo esc_attr( $user_name ); ?>">
                            <a href="JavaScript:void(0);" class="button button-secondary" id="sbi_switch_accounts"><?php esc_html_e( 'Switch Accounts', 'instagram-feed' ); ?></a>
                        </p>
                    </form>
                    <a href="JavaScript:void(0);"><i class="sbi_modal_close fa fa-times"></i></a>
                </div>
            </div>
		    <?php
	    } elseif ( isset( $_POST['sbi_connect_username'] ) ) {

		    $new_user_name = sanitize_text_field( $_POST['sbi_connect_username'] );
		    $new_account_details = json_decode( stripslashes( $_POST['sbi_account_json'] ), true );
		    array_map( 'sanitize_text_field', $new_account_details );

		    $updated_options = sbi_connect_basic_account( $new_account_details );
		    $connected_accounts = $updated_options['connected_accounts'];
		    $user_feed_ids = $updated_options['sb_instagram_user_id'];
	    }?>

	    <?php //Display connected page
	    if (isset( $sbi_connected_page ) && strpos($sbi_connected_page, ':') !== false) {

		    $sbi_connected_page_pieces = explode(":", $sbi_connected_page);
		    $sbi_connected_page_id = $sbi_connected_page_pieces[0];
		    $sbi_connected_page_name = $sbi_connected_page_pieces[1];
		    $sbi_connected_page_image = $sbi_connected_page_pieces[2];

		    echo '&nbsp;';
		    echo '<p style="font-weight: bold; margin-bottom: 5px;">Connected Business Profile:</p>';
		    echo '<div class="sbi-managed-page sbi-no-select">';
		    echo '<p><img class="sbi-page-avatar" border="0" height="50" width="50" src="'.$sbi_connected_page_image.'"><b>'.$sbi_connected_page_name.'</b> &nbsp; ('.$sbi_connected_page_id.')</p>';
		    echo '</div>';
	    }

	    ?>

        <form name="form1" method="post" action="">
            <input type="hidden" name="<?php echo $sb_instagram_settings_hidden_field; ?>" value="Y">
			<?php wp_nonce_field( 'sb_instagram_pro_saving_settings', 'sb_instagram_pro_settings_nonce' ); ?>

			<?php $sbi_active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'configure'; ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=sb-instagram-feed&amp;tab=configure" class="nav-tab <?php echo $sbi_active_tab == 'configure' ? 'nav-tab-active' : ''; ?>"><?php _e('1. Configure', 'instagram-feed' ); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=customize" class="nav-tab <?php echo strpos($sbi_active_tab, 'customize') !== false ? 'nav-tab-active' : ''; ?>"><?php _e('2. Customize', 'instagram-feed' ); ?></a>

                <a href="?page=sb-instagram-feed&amp;tab=display" class="nav-tab <?php echo $sbi_active_tab == 'display' ? 'nav-tab-active' : ''; ?>"><?php _e('3. Shortcodes', 'instagram-feed' ); ?></a>
               
			
            </h2>
			<?php if( $sbi_active_tab == 'configure' ) { //Start Configure tab ?>
			<input type="hidden" name="<?php echo $sb_instagram_configure_hidden_field; ?>" value="Y">

			<table class="form-table">
				<tbody>
				<h3><?php _e( 'Configure', 'instagram-feed' ); ?></h3>
                <noscript>
                    <div>Herererererererere</div>
                </noscript>

                <div id="sbi_config">
                    <a data-personal-basic-api="https://api.instagram.com/oauth/authorize?app_id=423965861585747&redirect_uri=https://api.smashballoon.com/instagram-basic-display-redirect.php&response_type=code&scope=user_profile,user_media&state=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>"
                       data-new-api="https://www.facebook.com/dialog/oauth?client_id=254638078422287&redirect_uri=https://api.smashballoon.com/instagram-graph-api-redirect.php&scope=manage_pages,instagram_basic,instagram_manage_insights,instagram_manage_comments&state=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>"
                       href="https://api.instagram.com/oauth/authorize?app_id=423965861585747&redirect_uri=https://api.smashballoon.com/instagram-basic-display-redirect.php&response_type=code&scope=user_profile,user_media&state=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>" class="sbi_admin_btn"><i class="fa fa-user-plus" aria-hidden="true" style="font-size: 20px;"></i>&nbsp; <?php _e('Connect an Instagram Account', 'instagram-feed' ); ?></a>

                    <!--<a href="https://instagram.com/oauth/authorize/?client_id=3a81a9fa2a064751b8c31385b91cc25c&scope=basic&redirect_uri=https://smashballoon.com/instagram-feed/instagram-token-plugin/?return_uri=<?php echo admin_url('admin.php?page=sb-instagram-feed'); ?>&response_type=token&state=<?php echo admin_url('admin.php?page-sb-instagram-feed'); ?>" class="sbi_admin_btn"><i class="fa fa-user-plus" aria-hidden="true" style="font-size: 20px;"></i>&nbsp; <?php _e('Connect an Instagram Account', 'instagram-feed' ); ?></a>
                    -->
                   
                </div>

				<!-- Old Access Token -->
				<input name="sb_instagram_at" id="sb_instagram_at" type="hidden" value="<?php echo esc_attr( $sb_instagram_at ); ?>" size="80" maxlength="100" placeholder="Click button above to get your Access Token" />

                <tr valign="top">
                    <th scope="row"><label><?php _e( 'Instagram Accounts', 'instagram-feed' ); ?></label><span style="font-weight:normal; font-style:italic; font-size: 12px; display: block;"><?php _e('Use the button above to connect an Instagram account', 'instagram-feed'); ?></span></th>
                    <td class="sbi_connected_accounts_wrap">
						<?php if ( empty( $connected_accounts ) ) : ?>
                            <p class="sbi_no_accounts"><?php _e( 'No Instagram accounts connected. Click the button above to connect an account.', 'instagram-feed' ); ?></p><br />
						<?php else:
                            if ( sbi_is_after_deprecation_deadline() ) {
	                            $deprecated_connected_account_message = __( '<b>Action Needed:</b> Reconnect this account to allow feed to update.', 'instagram-feed' );
                            } else {
	                            $deprecated_connected_account_message = __( '<b>Action Needed:</b> Reconnect this account before June 1, 2020, to avoid disruption with this feed.', 'instagram-feed' );
                            }

                            $accounts_that_need_updating = sbi_get_user_names_of_personal_accounts_not_also_already_updated();
                            ?>
							<?php foreach ( $connected_accounts as $account ) :
								$username = $account['username'] ? $account['username'] : $account['user_id'];
								if ( isset( $account['local_avatar'] ) && $account['local_avatar'] && isset( $options['sb_instagram_favor_local'] ) && $options['sb_instagram_favor_local' ] === 'on' ) {
									$upload = wp_upload_dir();
									$resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( SBI_UPLOADS_NAME );
									$profile_picture = '<img class="sbi_ca_avatar" src="'.$resized_url . $account['username'].'.jpg" />'; //Could add placeholder avatar image
								} else {
									$profile_picture = $account['profile_picture'] ? '<img class="sbi_ca_avatar" src="'.$account['profile_picture'].'" />' : ''; //Could add placeholder avatar image
								}

								$is_invalid_class = ! $account['is_valid'] ? ' sbi_account_invalid' : '';
								$in_user_feed = in_array( $account['user_id'], $user_feed_ids, true );
								$account_type = isset( $account['type'] ) ? $account['type'] : 'personal';
								$use_tagged = isset( $account['use_tagged'] ) && $account['use_tagged'] == '1';

								if ( empty( $profile_picture ) && $account_type === 'personal' ) {
									$account_update = sbi_account_data_for_token( $account['access_token'] );
									if ( isset( $account['is_valid'] ) ) {
										$split = explode( '.', $account['access_token'] );
										$connected_accounts[ $split[0] ] = array(
											'access_token' => $account['access_token'],
											'user_id' => $split[0],
											'username' => $account_update['username'],
											'is_valid' => true,
											'last_checked' => time(),
											'profile_picture' => $account_update['profile_picture']
										);

										$sbi_options = get_option( 'sb_instagram_settings', array() );
										$sbi_options['connected_accounts'] = $connected_accounts;
										update_option( 'sb_instagram_settings', $sbi_options );
									}

								}
								$updated_or_new_account_class = $new_user_name === $username && $account_type !== 'business' ? ' sbi_ca_new_or_updated' : '';

								?>
                                <div class="sbi_connected_account<?php echo $is_invalid_class . $updated_or_new_account_class; ?><?php if ( $in_user_feed ) echo ' sbi_account_active' ?> sbi_account_type_<?php echo $account_type; ?>" id="sbi_connected_account_<?php esc_attr_e( $account['user_id'] ); ?>" data-accesstoken="<?php esc_attr_e( $account['access_token'] ); ?>" data-userid="<?php esc_attr_e( $account['user_id'] ); ?>" data-username="<?php esc_attr_e( $account['username'] ); ?>" data-type="<?php esc_attr_e( $account_type ); ?>" data-permissions="<?php if ( $use_tagged ) echo 'tagged'; ?>">
	                                <?php if ( $account_type === 'personal' && in_array( $username, $accounts_that_need_updating, true ) ) : ?>
                                    <div class="sbi_deprecated">
                                        <span><i class="fa fa-exclamation-circle" aria-hidden="true"></i><?php echo $deprecated_connected_account_message; ?> <button class="sbi_reconnect button-primary">Reconnect</button></span>
                                    </div>
	                                <?php endif; ?>
                                    <div class="sbi_ca_alert">
                                        <span><?php _e( 'The Access Token for this account is expired or invalid. Click the button above to attempt to renew it.', 'instagram-feed' ) ?></span>
                                    </div>
                                    <div class="sbi_ca_info">

                                        <div class="sbi_ca_delete">
                                            <a href="<?php echo add_query_arg( 'disconnect', $account['user_id'], get_admin_url( null, 'admin.php?page=sb-instagram-feed' ) ); ?>" class="sbi_delete_account"><i class="fa fa-times"></i><span class="sbi_remove_text"><?php _e( 'Remove', 'instagram-feed' ); ?></span></a>
                                        </div>

                                        <div class="sbi_ca_username">
											<?php echo $profile_picture; ?>
                                            <strong><?php echo $username; ?><span><?php echo sbi_account_type_display( $account_type ); ?></span></strong>
                                        </div>

                                        <div class="sbi_ca_actions">
											<?php if ( ! $in_user_feed ) : ?>
                                                <a href="JavaScript:void(0);" class="sbi_use_in_user_feed button-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i><?php _e( 'Add to Primary Feed', 'instagram-feed' ); ?></a>
											<?php else : ?>
                                                <a href="JavaScript:void(0);" class="sbi_remove_from_user_feed button-primary"><i class="fa fa-minus-circle" aria-hidden="true"></i><?php _e( 'Remove from Primary Feed', 'instagram-feed' ); ?></a>
											<?php endif; ?>
                                            <a class="sbi_ca_token_shortcode button-secondary" href="JavaScript:void(0);"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i><?php _e( 'Add to another Feed', 'instagram-feed' ); ?></a>
                                            <a class="sbi_ca_show_token button-secondary" href="JavaScript:void(0);" title="<?php _e('Show access token and account info', 'instagram-feed'); ?>"><i class="fa fa-cog"></i></a>

                                        </div>

                                        <div class="sbi_ca_shortcode">

                                            <p><?php _e('Copy and paste this shortcode into your page or widget area', 'instagram-feed'); ?>:<br>
												<?php if ( !empty( $account['username'] ) ) : ?>
                                                    <code>[instagram-feed user="<?php echo $account['username']; ?>"]</code>
												<?php else : ?>
                                                    <code>[instagram-feed accesstoken="<?php echo $account['access_token']; ?>"]</code>
												<?php endif; ?>
                                            </p>

                                            <p><?php _e('To add multiple users in the same feed, simply separate them using commas', 'instagram-feed'); ?>:<br>
												<?php if ( !empty( $account['username'] ) ) : ?>
                                                    <code>[instagram-feed user="<?php echo $account['username']; ?>, a_second_user, a_third_user"]</code>
												<?php else : ?>
                                                    <code>[instagram-feed accesstoken="<?php echo $account['access_token']; ?>, another_access_token"]</code>
												<?php endif; ?>

                                            <p><?php echo sprintf( __('Click on the %s tab to learn more about shortcodes', 'instagram-feed'), '<a href="?page=sb-instagram-feed&tab=display" target="_blank">'. __( 'Display Your Feed', 'instagram-feed' ) . '</a>' ); ?></p>
                                        </div>

                                        <div class="sbi_ca_accesstoken">
                                            <span class="sbi_ca_token_label"><?php _e('Access Token', 'instagram-feed');?>:</span><input type="text" class="sbi_ca_token" value="<?php echo $account['access_token']; ?>" readonly="readonly" onclick="this.focus();this.select()" title="<?php _e('To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', 'instagram-feed');?>"><br>
                                            <span class="sbi_ca_token_label"><?php _e('User ID', 'instagram-feed');?>:</span><input type="text" class="sbi_ca_user_id" value="<?php echo $account['user_id']; ?>" readonly="readonly" onclick="this.focus();this.select()" title="<?php _e('To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', 'instagram-feed');?>"><br>
                                            <?php
                                            if ( $use_tagged ) {
	                                            $message = __( 'All', 'instagram-feed' );
                                            } else {
	                                            $message = __( '"Tagged" feed type unavailable - ', 'instagram-feed' );
                                                $is_basic_but_can_connect_as_business = ($account_type === 'basic' && ($account['account_type'] === 'business' || $account['account_type'] === 'creator'));
                                                if ( $is_basic_but_can_connect_as_business ) {
	                                                $message .= __( 'reconnect as a business account to enable this feed type', 'instagram-feed' );
                                                } elseif ( $account_type === 'personal' ) {
                                                    $message .= __( 'connect a business account to enable this feed type', 'instagram-feed' );
                                                } else {
	                                                $message .= __( 'reconnect account to enable this feed type', 'instagram-feed' );
                                                }
                                            }
                                            ?>
                                            <span class="sbi_ca_token_label"><?php _e('Permissions', 'instagram-feed');?>:</span><span class="sbi_permissions_desc"><?php echo esc_html( $message ); ?></span>
                                        </div>

                                    </div>

                                </div>

							<?php endforeach;  ?>
						<?php endif; ?>
                        <a href="JavaScript:void(0);" class="sbi_manually_connect button-secondary"><?php _e( 'Manually Connect an Account', 'instagram-feed' ); ?></a>
                        <div class="sbi_manually_connect_wrap">
                            <input name="sb_manual_at" id="sb_manual_at" type="text" value="" style="margin-top: 4px; padding: 5px 9px; margin-left: 0px;" size="64" minlength="15" maxlength="200" placeholder="<?php esc_attr_e( 'Enter a valid Instagram Access Token', 'instagram-feed' ); ?>" /><span class='sbi_business_profile_tag'><?php _e('Business or Basic Display', 'instagram-feed');?></span>
                            <div class="sbi_manual_account_id_toggle">
                                <label><?php _e('Please enter the User ID for this Profile:', 'instagram-feed');?></label>
                                <input name="sb_manual_account_id" id="sb_manual_account_id" type="text" value="" style="margin-top: 4px; padding: 5px 9px; margin-left: 0px;" size="40" minlength="5" maxlength="100" placeholder="Eg: 15641403491391489" />
                            </div>
                            <p id="sbi_no_js_warning" class="sbi_notice"><?php echo sprintf( __('It looks like JavaScript is not working on this page. Some features may not work fully.', 'instagram-feed'), '<a href="#">', '</a>' ); ?></p>
                            <p class="sbi_submit" style="display: inline-block;"><input type="submit" name="sbi_submit" id="sbi_manual_submit" class="button button-primary" value="<?php _e('Connect This Account', 'instagram-feed' );?>"></p>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Feed Type', 'instagram-feed'); ?>:</label><code class="sbi_shortcode"> type
                            Eg: type=user user=smashballoon
                            Eg: type=hashtag hashtag="dogs"
                            Eg: type=tagged tagged=smashballoon
                            Eg: type=mixed user=x hashtag=x</code></th>
                    <td>
                        <div class="sbi_row" style="min-height: 29px;">
                            <div class="sbi_col sbi_one">
                                <input type="radio" name="sb_instagram_type" id="sb_instagram_type_user" value="user" <?php if($sb_instagram_type == "user") echo "checked"; ?> />
                                <label class="sbi_radio_label" for="sb_instagram_type_user"><?php _e( 'User Account', 'instagram-feed' ); ?>: <a class="sbi_type_tooltip_link" href="JavaScript:void(0);"><i class="fa fa-question-circle" aria-hidden="true" style="margin-left: 2px;"></i></a></label>
                            </div>
                            <div class="sbi_col sbi_two">
                                <div class="sbi_user_feed_ids_wrap">
									<?php foreach ( $user_feed_ids as $feed_id ) : if ( $feed_id !== '' ) :?>
                                        <div id="sbi_user_feed_id_<?php echo $feed_id; ?>" class="sbi_user_feed_account_wrap">

											<?php if ( isset( $connected_accounts[ $feed_id ] ) && ! empty( $connected_accounts[ $feed_id ]['username'] ) ) : ?>
                                                <strong><?php echo $connected_accounts[ $feed_id ]['username']; ?></strong> <span>(<?php echo $feed_id; ?>)</span>
                                                <input name="sb_instagram_user_id[]" id="sb_instagram_user_id" type="hidden" value="<?php esc_attr_e( $feed_id ); ?>" />
											<?php elseif ( isset( $connected_accounts[ $feed_id ] ) && ! empty( $connected_accounts[ $feed_id ]['access_token'] ) ) : ?>
                                                <strong><?php echo $feed_id; ?></strong>
                                                <input name="sb_instagram_user_id[]" id="sb_instagram_user_id" type="hidden" value="<?php esc_attr_e( $feed_id ); ?>" />
											<?php endif; ?>

                                        </div>
									<?php endif; endforeach; ?>
                                </div>

								<?php if ( empty( $user_feed_ids ) ) : ?>
                                    <p class="sbi_no_accounts" style="margin-top: -3px; margin-right: 10px;"><?php _e('Connect a user account above', 'instagram-feed' );?></p>
								<?php endif; ?>
                            </div>

                            <div class="sbi_tooltip sbi_type_tooltip">
                                <p><?php _e("In order to display posts from a User account, first connect an account using the button above.", 'instagram-feed' ); ?></p>
                                <p style="padding-top:8px;"><b><?php _e("Multiple Acounts", 'instagram-feed' ); ?></b><br />
									<?php _e("It is only possible to display feeds from Instagram accounts which you own. In order to display feeds from multiple accounts, first connect them above and then use the buttons to add the account either to your primary feed or to another feed on your site.", 'instagram-feed'); ?>
                                </p>
                                <p style="padding:10px 0 6px 0;">
                                    <b><?php _e("Displaying Posts from Other Instagram Accounts", 'instagram-feed' ); ?></b><br />
									<?php _e("Due to Instagram restrictions it is not possible to display photos from other Instagram accounts which you do not have access to. You can only display the user feed of an account which you connect above. You can connect as many account as you like by logging in using the button above, or manually copy/pasting an Access Token by selecting the 'Manually Connect an Account' option.", 'instagram-feed' ); ?>
                                </p>
                            </div>
                        </div>

						<?php
						//Check whether a business account is connected and hashtag feed is selected so we can display an error
						$sbi_business_account_connected = false;
						$sbi_hashtag_feed_issue = false;
						foreach ( $connected_accounts as $connected_account ) {
							if( isset($connected_account[ 'type' ]) && $connected_account[ 'type' ] == 'business' ){
								$sbi_business_account_connected = true;
							}
						}
						if( $sb_instagram_type == "hashtag" && $sbi_business_account_connected == false && count($connected_accounts) > 0 ){
							$sbi_hashtag_feed_issue = true;
						}
						?>

                        <div class="sbi_row <?php if($sbi_hashtag_feed_issue) echo 'sbi_hashtag_feed_issue'; ?>">
                            <div class="sbi_col sbi_one">
                                <input type="radio" name="sb_instagram_type" id="sb_instagram_type_hashtag" value="hashtag" <?php if($sb_instagram_type == "hashtag") echo "checked"; ?> />
                                <label class="sbi_radio_label" for="sb_instagram_type_hashtag">Public Hashtag: <a class="sbi_type_tooltip_link" href="JavaScript:void(0);"><i class="fa fa-question-circle" aria-hidden="true" style="margin-left: 2px;"></i></a></label>
                            </div>
                            <div class="sbi_col sbi_two">
                                <input name="sb_instagram_hashtag" id="sb_instagram_hashtag" type="text" value="<?php esc_attr_e( $sb_instagram_hashtag ); ?>" size="45" placeholder="Eg: #one, #two, #three" />
								<?php $order_val = isset( $sb_instagram_order ) ? $sb_instagram_order : 'top'; ?>
                                <div class="sbi_radio_reveal">
                                    <label class="sbi_shortcode_label"><?php _e( 'Order of Posts','instagram-feed' ); ?>:<code class="sbi_shortcode">order=recent | order=top</code></label>
                                    <div class="sbi_row">
                                        <input name="sb_instagram_order" id="sb_instagram_order_top" type="radio" value="top" <?php if ( $order_val == 'top' ) { echo 'checked'; } ?>/><label for="sb_instagram_order_top"><?php _e( 'Top','instagram-feed' ); ?><span>(<?php _e( 'Most popular first','instagram-feed'); ?>)</span></label>
                                    </div>
                                    <div class="sbi_row">
                                        <input name="sb_instagram_order" id="sb_instagram_order_recent" type="radio" value="recent" <?php if ( $order_val == 'recent' ) { echo 'checked'; }?>/><label for="sb_instagram_order_recent"><?php _e( 'Recent','instagram-feed'); ?><span>(<?php _e( 'Within 24 hours or "Top posts" initially','instagram-feed'); ?> <a class="sbi_type_tooltip_link" href="JavaScript:void(0);" style="margin-left: 0;"><i class="fa fa-question-circle" aria-hidden="true" style="padding: 2px;"></i></a>)</span></label>

                                        <p class="sbi_tooltip"><?php _e("Instagram only returns the most recent hashtag posts from the past 24 hour period. The first time you display a hashtag feed, posts from the \"Top posts\" section will be retrieved, sorted by date, and saved. The plugin then stores these posts so that they can continue to be displayed indefinitely, creating a permanent feed of your posts.", 'instagram-feed'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <p class="sbi_tooltip sbi_type_tooltip"><?php _e("Display all public posts from a hashtag instead of from a user. Separate multiple hashtags using commas. To show posts from a user feed that also have a certain hashtag, use the \"includewords\" shortcode setting.", 'instagram-feed'); ?><br /><b>Note:</b> <?php _e("To display a hashtag feed, it is required that you first connect an Instagram Business Profile using the", 'instagram-feed'); ?> <b>"Connect an Instagram Account"</b> <?php _e("button above", 'instagram-feed'); ?>. &nbsp;<a href="#"><?php _e("Why is this required?", 'instagram-feed'); ?></a></p>

							<?php if($sbi_hashtag_feed_issue) { ?>
                                <p class="sbi_hashtag_feed_issue_note"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?php _e("Hashtag Feeds now require a Business Profile to be connected.", 'instagram-feed'); ?> <a href="<?php echo admin_url( 'admin.php?page=sbi-welcome-new' ); ?>" target="_blank">See here</a> for more info.</p>
							<?php } ?>
                        </div>

                        <div class="sbi_row" style="min-height: 29px;">
                            <div class="sbi_col sbi_one">
                                <input type="radio" name="sb_instagram_type" id="sb_instagram_type_tagged" value="tagged" <?php if($sb_instagram_type == "tagged") echo "checked"; ?> />
                                <label class="sbi_radio_label" for="sb_instagram_type_tagged">Tagged: <a class="sbi_type_tooltip_link" href="JavaScript:void(0);"><i class="fa fa-question-circle" aria-hidden="true" style="margin-left: 2px;"></i></a></label>
                            </div>
                            <div class="sbi_col sbi_two">
                                <div class="sbi_tagged_feed_ids_wrap">
			                        <?php foreach ( $tagged_feed_ids as $feed_id ) : if ( $feed_id !== '' ) :?>
                                        <div id="sbi_tagged_feed_id_<?php echo $feed_id; ?>" class="sbi_tagged_feed_account_wrap">

					                        <?php if ( isset( $connected_accounts[ $feed_id ] ) && ! empty( $connected_accounts[ $feed_id ]['username'] ) ) : ?>
                                                <strong><?php echo $connected_accounts[ $feed_id ]['username']; ?></strong> <span>(<?php echo $feed_id; ?>)</span>
                                                <input name="sb_instagram_tagged_id[]" id="sb_instagram_tagged_id" type="hidden" value="<?php esc_attr_e( $feed_id ); ?>" />
					                        <?php elseif ( isset( $connected_accounts[ $feed_id ] ) && ! empty( $connected_accounts[ $feed_id ]['access_token'] ) ) : ?>
                                                <strong><?php echo $feed_id; ?></strong>
                                                <input name="sb_instagram_tagged_id[]" id="sb_instagram_tagged_id" type="hidden" value="<?php esc_attr_e( $feed_id ); ?>" />
					                        <?php endif; ?>

                                        </div>
			                        <?php endif; endforeach; ?>
                                </div>

		                        <?php if ( empty( $tagged_feed_ids ) ) : ?>
                                    <p class="sbi_no_accounts" style="margin-right: 10px;"><?php _e('Connect a user account above', 'instagram-feed' );?></p>
		                        <?php endif; ?>
                            </div>
                            <p class="sbi_tooltip sbi_type_tooltip"><?php _e("Display photos that an account has been tagged in. Separate multiple user names using commas.", 'instagram-feed'); ?><br /><b>Note:</b> <?php _e("To display a tagged feed, it is required that you first connect an Instagram Business Profile using the", 'instagram-feed'); ?> <b>"Connect an Instagram Account"</b> <?php _e("button above. This account must have been connected or reconnected after version 5.2 of the Pro version or 2.1 of the Free version", 'instagram-feed'); ?>. &nbsp;<a href="#"><?php _e("Why is this required?", 'instagram-feed'); ?></a></p>
                        </div>

                        <div class="sbi_row sbi_mixed_directions">
                            <div class="sbi_col sbi_one">
                                <input type="radio" name="sb_instagram_type" disabled />
                                <label class="sbi_radio_label" for="sb_instagram_type_mixed">Mixed: <a href="JavaScript:void(0);"><i class="fa fa-question-circle" aria-hidden="true" style="margin-left: 2px;"></i></a></label>
                            </div>
                            <div class="sbi_col sbi_two" style="max-width: 350px;">
                                <input name="sb_instagram_hashtag" id="sb_instagram_mixed" type="text" size="45" disabled />
                            </div>
                            <div class="sbi_tooltip sbi_type_tooltip">
                                <p>
									<?php echo sprintf( __('To display multiple feed types in a single feed, use %s in your shortcode and then add the user name or hashtag for each feed into the shortcode, like so: %s. This will combine a user feed and a hashtag feed into the same feed.', 'instagram-feed'), 'type="mixed"', '<code>[instagram-feed type="mixed" user="smashballoon" hashtag="#awesomeplugins"]</code>' ); ?>
                                </p>
                                <p style="padding-top: 8px;"><b>Note:</b> To display a hashtag feed, it is required that you first connect an Instagram Business Profile using the <b>"Connect an Instagram Account"</b> button above. &nbsp;<a href="#">Why is this required?</a>
                                </p>
                            </div>
                        </div>

                    </td>
                </tr>

                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Preserve settings when plugin is removed", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_preserve_settings" type="checkbox" id="sb_instagram_preserve_settings" <?php if($sb_instagram_preserve_settings == true) echo "checked"; ?> />
                        <label for="sb_instagram_preserve_settings"><?php _e('Yes'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><i class="fa fa-question-circle" aria-hidden="true" style="margin-right: 6px;"></i></a>
                        <p class="sbi_tooltip"><?php _e('When removing the plugin your settings are automatically erased. Checking this box will prevent any settings from being deleted. This means that you can uninstall and reinstall the plugin without losing your settings.', 'instagram-feed'); ?></p>
                    </td>
                </tr>


                <tr valign="top" class="sbi_cron_cache_opts">
                    <th scope="row"><?php _e( 'Check for new posts', 'instagram-feed' ); ?></th>
                    <td>

                        <div class="sbi_row">
                            <input type="radio" name="sbi_caching_type" id="sbi_caching_type_page" value="page" <?php if ( $sbi_caching_type === 'page' ) echo 'checked'; ?>>
                            <label for="sbi_caching_type_page"><?php _e( 'When the page loads', 'instagram-feed' ); ?></label>
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);" style="position: relative; top: 2px;"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                            <p class="sbi_tooltip sbi-more-info"><?php _e( 'Your Instagram post data is temporarily cached by the plugin in your WordPress database. There are two ways that you can set the plugin to check for new data', 'instagram-feed' ); ?>:<br><br>
								<?php _e( '<b>1. When the page loads</b><br>Selecting this option means that when the cache expires then the plugin will check Instagram for new posts the next time that the feed is loaded. You can choose how long this data should be cached for. If you set the time to 60 minutes then the plugin will clear the cached data after that length of time, and the next time the page is viewed it will check for new data. <b>Tip:</b> If you\'re experiencing an issue with the plugin not updating automatically then try enabling the setting labeled <b>\'Force cache to clear on interval\'</b> which is located on the \'Customize\' tab.', 'instagram-feed' ); ?>
                                <br><br>
								<?php _e( '<b>2. In the background</b><br>Selecting this option means that the plugin will check for new data in the background so that the feed is updated behind the scenes. You can select at what time and how often the plugin should check for new data using the settings below. <b>Please note</b> that the plugin will initially check for data from Instagram when the page first loads, but then after that will check in the background on the schedule selected - unless the cache is cleared.</p>', 'instagram-feed' ); ?>
                        </div>
                        <div class="sbi_row sbi-caching-page-options" style="display: none;">
							<?php _e( 'Every', 'instagram-feed' ); ?>:
                            <input name="sb_instagram_cache_time" type="text" value="<?php esc_attr_e( $sb_instagram_cache_time ); ?>" size="4" />
                            <select name="sb_instagram_cache_time_unit">
                                <option value="minutes" <?php if($sb_instagram_cache_time_unit == "minutes") echo 'selected="selected"' ?> ><?php _e('Minutes', 'instagram-feed'); ?></option>
                                <option value="hours" <?php if($sb_instagram_cache_time_unit == "hours") echo 'selected="selected"' ?> ><?php _e('Hours', 'instagram-feed'); ?></option>
                                <option value="days" <?php if($sb_instagram_cache_time_unit == "days") echo 'selected="selected"' ?> ><?php _e('Days', 'instagram-feed'); ?></option>
                            </select>
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                            <p class="sbi_tooltip"><?php _e('Your Instagram posts are temporarily cached by the plugin in your WordPress database. You can choose how long the posts should be cached for. If you set the time to 1 hour then the plugin will clear the cache after that length of time and check Instagram for posts again.', 'instagram-feed'); ?></p>
                        </div>

                        <div class="sbi_row">
                            <input type="radio" name="sbi_caching_type" id="sbi_caching_type_cron" value="background" <?php if ( $sbi_caching_type === 'background' ) echo 'checked'; ?>>
                            <label for="sbi_caching_type_cron"><?php _e( 'In the background', 'instagram-feed' ); ?></label>
                        </div>
                        <div class="sbi_row sbi-caching-cron-options" style="display: block;">

                            <select name="sbi_cache_cron_interval" id="sbi_cache_cron_interval">
                                <option value="30mins" <?php if ( $sbi_cache_cron_interval === '30mins' ) echo 'selected'; ?>><?php _e( 'Every 30 minutes', 'instagram-feed' ); ?></option>
                                <option value="1hour" <?php if ( $sbi_cache_cron_interval === '1hour' ) echo 'selected'; ?>><?php _e( 'Every hour', 'instagram-feed' ); ?></option>
                                <option value="12hours" <?php if ( $sbi_cache_cron_interval === '12hours' ) echo 'selected'; ?>><?php _e( 'Every 12 hours', 'instagram-feed' ); ?></option>
                                <option value="24hours" <?php if ( $sbi_cache_cron_interval === '24hours' ) echo 'selected'; ?>><?php _e( 'Every 24 hours', 'instagram-feed' ); ?></option>
                            </select>

                            <div id="sbi-caching-time-settings" style="display: none;">
								<?php _e('at' ); ?>

                                <select name="sbi_cache_cron_time" style="width: 80px">
                                    <option value="1" <?php if ( $sbi_cache_cron_time === '1' ) echo 'selected'; ?>>1:00</option>
                                    <option value="2" <?php if ( $sbi_cache_cron_time === '2' ) echo 'selected'; ?>>2:00</option>
                                    <option value="3" <?php if ( $sbi_cache_cron_time === '3' ) echo 'selected'; ?>>3:00</option>
                                    <option value="4" <?php if ( $sbi_cache_cron_time === '4' ) echo 'selected'; ?>>4:00</option>
                                    <option value="5" <?php if ( $sbi_cache_cron_time === '5' ) echo 'selected'; ?>>5:00</option>
                                    <option value="6" <?php if ( $sbi_cache_cron_time === '6' ) echo 'selected'; ?>>6:00</option>
                                    <option value="7" <?php if ( $sbi_cache_cron_time === '7' ) echo 'selected'; ?>>7:00</option>
                                    <option value="8" <?php if ( $sbi_cache_cron_time === '8' ) echo 'selected'; ?>>8:00</option>
                                    <option value="9" <?php if ( $sbi_cache_cron_time === '9' ) echo 'selected'; ?>>9:00</option>
                                    <option value="10" <?php if ( $sbi_cache_cron_time === '10' ) echo 'selected'; ?>>10:00</option>
                                    <option value="11" <?php if ( $sbi_cache_cron_time === '11' ) echo 'selected'; ?>>11:00</option>
                                    <option value="0" <?php if ( $sbi_cache_cron_time === '0' ) echo 'selected'; ?>>12:00</option>
                                </select>

                                <select name="sbi_cache_cron_am_pm" style="width: 70px">
                                    <option value="am" <?php if ( $sbi_cache_cron_am_pm === 'am' ) echo 'selected'; ?>>AM</option>
                                    <option value="pm" <?php if ( $sbi_cache_cron_am_pm === 'pm' ) echo 'selected'; ?>>PM</option>
                                </select>
                            </div>

							<?php
							if ( wp_next_scheduled( 'sbi_feed_update' ) ) {
								$time_format = get_option( 'time_format' );
								if ( ! $time_format ) {
									$time_format = 'g:i a';
								}
								//
								$schedule = wp_get_schedule( 'sbi_feed_update' );
								if ( $schedule == '30mins' ) $schedule = __( 'every 30 minutes', 'instagram-feed' );
								if ( $schedule == 'twicedaily' ) $schedule = __( 'every 12 hours', 'instagram-feed' );
								$sbi_next_cron_event = wp_next_scheduled( 'sbi_feed_update' );
								echo '<p class="sbi-caching-sched-notice"><span><b>' . __( 'Next check', 'instagram-feed' ) . ': ' . date( $time_format, $sbi_next_cron_event + sbi_get_utc_offset() ) . ' (' . $schedule . ')</b> - ' . __( 'Note: Saving the settings on this page will clear the cache and reset this schedule', 'instagram-feed' ) . '</span></p>';
							} else {
								echo '<p style="font-size: 11px; color: #666;">' . __( 'Nothing currently scheduled', 'instagram-feed' ) . '</p>';
							}
							?>

                        </div>

                    </td>
                </tr>

                </tbody>
            </table>

	        <?php submit_button(); ?>
        </form>



    <?php } // End Configure tab ?>



	    <?php if( strpos($sbi_active_tab, 'customize') !== false ) { //Show Customize sub tabs ?>

            <h2 class="nav-tab-wrapper sbi-subtabs">
                <a href="?page=sb-instagram-feed&amp;tab=customize" class="nav-tab <?php echo $sbi_active_tab == 'customize' ? 'nav-tab-active' : ''; ?>"><?php _e('General'); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=customize-posts" class="nav-tab <?php echo $sbi_active_tab == 'customize-posts' ? 'nav-tab-active' : ''; ?>"><?php _e('Posts'); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=customize-moderation" class="nav-tab <?php echo $sbi_active_tab == 'customize-moderation' ? 'nav-tab-active' : ''; ?>"><?php _e('Moderation'); ?></a>
                <a href="?page=sb-instagram-feed&amp;tab=customize-advanced" class="nav-tab <?php echo $sbi_active_tab == 'customize-advanced' ? 'nav-tab-active' : ''; ?>"><?php _e('Advanced'); ?></a>
            </h2>

	    <?php } ?>

	    <?php if( $sbi_active_tab == 'customize' ) { //Start General tab ?>

            <p class="sb_instagram_contents_links" id="general">
                <span><?php _e('Jump to:', 'instagram-feed'); ?> </span>
                <a href="#general"><?php _e('General', 'instagram-feed'); ?></a>
                <a href="#layout"><?php _e('Layout', 'instagram-feed'); ?></a>
                <a href="#headeroptions"><?php _e('Header', 'instagram-feed'); ?></a>
                <a href="#loadmore"><?php _e("'Load More' Button", 'instagram-feed'); ?></a>
                <a href="#follow"><?php _e("'Follow' Button", 'instagram-feed'); ?></a>
            </p>

            <input type="hidden" name="<?php echo $sb_instagram_customize_hidden_field; ?>" value="Y">

            <h3><?php _e('General', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Width of Feed', 'instagram-feed'); ?></label><code class="sbi_shortcode"> width  widthunit
                            Eg: width=50 widthunit=%</code></th>
                    <td>
                        <input name="sb_instagram_width" type="text" value="<?php esc_attr_e( $sb_instagram_width ); ?>" id="sb_instagram_width" size="4" />
                        <select name="sb_instagram_width_unit" id="sb_instagram_width_unit">
                            <option value="px" <?php if($sb_instagram_width_unit == "px") echo 'selected="selected"' ?> ><?php _e('px'); ?></option>
                            <option value="%" <?php if($sb_instagram_width_unit == "%") echo 'selected="selected"' ?> ><?php _e('%'); ?></option>
                        </select>
                        <div id="sb_instagram_width_options">
                            <input name="sb_instagram_feed_width_resp" type="checkbox" id="sb_instagram_feed_width_resp" <?php if($sb_instagram_feed_width_resp == true) echo "checked"; ?> /><label for="sb_instagram_feed_width_resp"><?php _e('Set to be 100% width on mobile?'); ?></label>
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                            <p class="sbi_tooltip"><?php _e("If you set a width on the feed then this will be used on mobile as well as desktop. Check this setting to set the feed width to be 100% on mobile so that it is responsive.", 'instagram-feed'); ?></p>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Height of Feed', 'instagram-feed'); ?></label><code class="sbi_shortcode"> height  heightunit
                            Eg: height=500 heightunit=px</code></th>
                    <td>
                        <input name="sb_instagram_height" type="text" value="<?php esc_attr_e( $sb_instagram_height ); ?>" size="4" />
                        <select name="sb_instagram_height_unit">
                            <option value="px" <?php if($sb_instagram_height_unit == "px") echo 'selected="selected"' ?> ><?php _e('px'); ?></option>
                            <option value="%" <?php if($sb_instagram_height_unit == "%") echo 'selected="selected"' ?> ><?php _e('%'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Background Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> background
                            Eg: background=d89531</code></th>
                    <td>
                        <input name="sb_instagram_background" type="text" value="<?php esc_attr_e( $sb_instagram_background ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                </tbody>
            </table>

            <hr id="layout" />
            <h3><?php _e('Layout', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
			    <?php
			    $selected_type = isset( $sb_instagram_layout_type ) ? $sb_instagram_layout_type : 'grid';
			    $layout_types = array(
				    'grid' => __( 'Grid', 'instagram-feed' ),
				    'carousel' => __( 'Carousel', 'instagram-feed' ),
				    'masonry' => __( 'Masonry', 'instagram-feed' ),
				    'highlight' => __( 'Highlight', 'instagram-feed' )
			    );
			    $layout_images = array(
				    'grid' => SBI_PLUGIN_URL . 'img/grid.png',
				    'carousel' => SBI_PLUGIN_URL . 'img/carousel.png',
				    'masonry' => SBI_PLUGIN_URL . 'img/masonry.png',
				    'highlight' => SBI_PLUGIN_URL . 'img/highlight.png'
			    );
			    ?>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Layout Type', 'instagram-feed'); ?></label><code class="sbi_shortcode"> layout
                            Eg: layout=grid
                            Eg: layout=carousel
                            Eg: layout=masonry
                            Eg: layout=highlight</code></th>
                    <td>
					    <?php foreach( $layout_types as $layout_type => $label ) : ?>
                            <div class="sbi_layout_cell <?php if($selected_type === $layout_type) echo "sbi_layout_selected"; ?>">
                                <input class="sb_layout_type" id="sb_layout_type_<?php esc_attr_e( $layout_type ); ?>" name="sb_instagram_layout_type" type="radio" value="<?php esc_attr_e( $layout_type ); ?>" <?php if ( $selected_type === $layout_type ) echo 'checked'; ?>/><label for="sb_layout_type_<?php esc_attr_e( $layout_type ); ?>"><span class="sbi_label"><?php echo esc_html( $label ); ?></span><img src="<?php echo $layout_images[ $layout_type ]; ?>" /></label>
                            </div>
					    <?php endforeach; ?>
                        <div class="sb_layout_options_wrap">
                            <div class="sb_instagram_layout_settings sbi_layout_type_grid">
                                <i class="fa fa-info-circle" aria-hidden="true" style="margin-right: 8px;"></i><span class="sbi_note" style="margin-left: 0;"><?php _e('A uniform grid of square-cropped images.'); ?></span>
                            </div>
                            <div class="sb_instagram_layout_settings sbi_layout_type_masonry">
                                <i class="fa fa-info-circle" aria-hidden="true" style="margin-right: 8px;"></i><span class="sbi_note" style="margin-left: 0;"><?php _e('Images in their original aspect ratios with no vertical space between posts.'); ?></span>
                            </div>
                            <div class="sb_instagram_layout_settings sbi_layout_type_carousel">
                                <div class="sb_instagram_layout_setting">
                                    <i class="fa fa-info-circle" aria-hidden="true" style="margin-right: 8px;"></i><span class="sbi_note" style="margin-left: 0;"><?php _e('Posts are displayed in a slideshow carousel.', 'instagram-feed'); ?></span>
                                </div>
                                <div class="sb_instagram_layout_setting">

                                    <label><?php _e('Number of Rows', 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouselrows
                                        Eg: carouselrows=2</code>
                                    <br />
                                    <span class="sbi_note" style="margin: -5px 0 -10px 0; display: block;">Use the "Number of Columns" setting below this section to set how many posts are visible in the carousel at a given time.</span>
                                    <br />
                                    <select name="sb_instagram_carousel_rows" id="sb_instagram_carousel_rows">
                                        <option value="1" <?php if($sb_instagram_carousel_rows == "1") echo 'selected="selected"' ?> >1</option>
                                        <option value="2" <?php if($sb_instagram_carousel_rows == "2") echo 'selected="selected"' ?> >2</option>
                                    </select>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <label><?php _e('Loop Type', 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouselloop
                                        Eg: carouselloop=rewind
                                        carouselloop=infinity</code>
                                    <br />
                                    <select name="sb_instagram_carousel_loop" id="sb_instagram_carousel_loop">
                                        <option value="rewind" <?php if($sb_instagram_carousel_loop == "rewind") echo 'selected="selected"' ?> ><?php _e( 'Rewind', 'instagram-feed'); ?></option>
                                        <option value="infinity" <?php if($sb_instagram_carousel_loop == "infinity") echo 'selected="selected"' ?> ><?php _e( 'Infinity', 'instagram-feed'); ?></option>
                                    </select>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <input type="checkbox" name="sb_instagram_carousel_arrows" id="sb_instagram_carousel_arrows" <?php if($sb_instagram_carousel_arrows == true) echo 'checked="checked"' ?> />
                                    <label><?php _e("Show Navigation Arrows", 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouselarrows
                                        Eg: carouselarrows=true</code>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <input type="checkbox" name="sb_instagram_carousel_pag" id="sb_instagram_carousel_pag" <?php if($sb_instagram_carousel_pag == true) echo 'checked="checked"' ?> />
                                    <label><?php _e("Show Pagination", 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouselpag
                                        Eg: carouselpag=true</code>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <input type="checkbox" name="sb_instagram_carousel_autoplay" id="sb_instagram_carousel_autoplay" <?php if($sb_instagram_carousel_autoplay == true) echo 'checked="checked"' ?> />
                                    <label><?php _e("Enable Autoplay", 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouselautoplay
                                        Eg: carouselautoplay=true</code>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <label><?php _e("Interval Time", 'instagram-feed'); ?></label><code class="sbi_shortcode"> carouseltime
                                        Eg: carouseltime=8000</code>
                                    <br />
                                    <input name="sb_instagram_carousel_interval" type="text" value="<?php esc_attr_e( $sb_instagram_carousel_interval ); ?>" size="6" /><?php _e("miliseconds", 'instagram-feed'); ?>
                                </div>
                            </div>

                            <div class="sb_instagram_layout_settings sbi_layout_type_highlight">
                                <div class="sb_instagram_layout_setting">
                                    <i class="fa fa-info-circle" aria-hidden="true" style="margin-right: 8px;"></i><span class="sbi_note" style="margin-left: 0;"><?php _e('Masonry style, square-cropped, image only (no captions or likes/comments below image). "Highlighted" posts are twice as large.', 'instagram-feed'); ?></span>
                                </div>
                                <div class="sb_instagram_layout_setting">
                                    <label><?php _e('Highlighting Type', 'instagram-feed'); ?></label><code class="sbi_shortcode"> highlighttype
                                        Eg: highlighttype=pattern</code>
                                    <br />
                                    <select name="sb_instagram_highlight_type" id="sb_instagram_highlight_type">
                                        <option value="pattern" <?php if($sb_instagram_highlight_type == "pattern") echo 'selected="selected"' ?> ><?php _e( 'Pattern', 'instagram-feed'); ?></option>
                                        <option value="id" <?php if($sb_instagram_highlight_type == "id") echo 'selected="selected"' ?> ><?php _e( 'Post ID', 'instagram-feed'); ?></option>
                                        <option value="hashtag" <?php if($sb_instagram_highlight_type == "hashtag") echo 'selected="selected"' ?> ><?php _e( 'Hashtag', 'instagram-feed'); ?></option>
                                    </select>
                                </div>
                                <div class="sb_instagram_highlight_sub_options sb_instagram_highlight_pattern sb_instagram_layout_setting">
                                    <label><?php _e('Offset', 'instagram-feed'); ?></label><code class="sbi_shortcode"> highlightoffset
                                        Eg: highlightoffset=2</code>
                                    <br />
                                    <input name="sb_instagram_highlight_offset" type="number" min="0" value="<?php esc_attr_e( $sb_instagram_highlight_offset ); ?>" style="width: 50px;" />
                                </div>
                                <div class="sb_instagram_highlight_sub_options sb_instagram_highlight_pattern sb_instagram_layout_setting">
                                    <label><?php _e('Pattern', 'instagram-feed'); ?></label><code class="sbi_shortcode"> highlightpattern
                                        Eg: highlightpattern=3</code>
                                    <br />
                                    <span><?php _e( 'Highlight every', 'instagram-feed' ); ?></span><input name="sb_instagram_highlight_factor" type="number" min="2" value="<?php esc_attr_e( $sb_instagram_highlight_factor ); ?>" style="width: 50px;" /><span><?php _e( 'posts', 'instagram-feed' ); ?></span>
                                </div>
                                <div class="sb_instagram_highlight_sub_options sb_instagram_highlight_hashtag sb_instagram_layout_setting">
                                    <label><?php _e("Highlight Posts with these Hashtags", 'instagram-feed'); ?></label>
                                    <input name="sb_instagram_highlight_hashtag" id="sb_instagram_highlight_hashtag" type="text" size="40" value="<?php esc_attr_e( stripslashes( $sb_instagram_highlight_hashtag ) ); ?>" />&nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                                    <br />
                                    <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate multiple hashtags using commas', 'instagram-feed'); ?></span>


                                    <p class="sbi_tooltip"><?php _e("You can use this setting to highlight posts by a hashtag. Use a specified hashtag in your posts and they will be automatically highlighted in your feed.", 'instagram-feed'); ?></p>
                                </div>
                                <div class="sb_instagram_highlight_sub_options sb_instagram_highlight_ids sb_instagram_layout_setting">
                                    <label><?php _e("Highlight Posts by ID", 'instagram-feed'); ?></label>
                                    <textarea name="sb_instagram_highlight_ids" id="sb_instagram_highlight_ids" style="width: 100%;" rows="3"><?php esc_attr_e( stripslashes( $sb_instagram_highlight_ids ) ); ?></textarea>
                                    <br />
                                    <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate IDs using commas', 'instagram-feed'); ?></span>

                                    &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                                    <p class="sbi_tooltip"><?php _e("You can use this setting to highlight posts by their ID. Enable and use \"moderation mode\", check the box to show post IDs underneath posts, then copy and paste IDs into this text box.", 'instagram-feed'); ?></p>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>


                <tr valign="top">
                    <th scope="row"><label><?php _e('Number of Photos', 'instagram-feed'); ?></label><code class="sbi_shortcode"> num
                            Eg: num=6</code></th>
                    <td>
                        <input name="sb_instagram_num" type="text" value="<?php esc_attr_e( $sb_instagram_num ); ?>" size="4" />
                        <span class="sbi_note"><?php _e('Number of photos to show initially', 'instagram-feed'); ?></span>
                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This is the number of photos which will be displayed initially and also the number which will be loaded in when you click on the 'Load More' button in your feed. For optimal performance it is recommended not to set this higher than 50.", 'instagram-feed'); ?></p>
                        <br>
                        <a href="javascript:void(0);" class="sb_instagram_mobile_layout_reveal button-secondary"><?php _e( 'Show Mobile Options', 'instagram-feed' ); ?></a>
                        <br>
                        <div class="sb_instagram_mobile_layout_setting">
                            <p style="font-weight: bold; padding-bottom: 5px;"><?php _e('Number of Photos on Mobile', 'instagram-feed');?></p>
                            <input name="sb_instagram_nummobile" type="number" value="<?php esc_attr_e( $sb_instagram_nummobile ); ?>" min="0" max="100" style="width: 50px;" />
                            <span class="sbi_note"><?php _e('Leave blank to use the same as above', 'instagram-feed'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Number of Columns', 'instagram-feed'); ?></label><code class="sbi_shortcode"> cols
                            Eg: cols=3</code></th>
                    <td>
                        <select name="sb_instagram_cols">
                            <option value="1" <?php if($sb_instagram_cols == "1") echo 'selected="selected"' ?> ><?php _e('1'); ?></option>
                            <option value="2" <?php if($sb_instagram_cols == "2") echo 'selected="selected"' ?> ><?php _e('2'); ?></option>
                            <option value="3" <?php if($sb_instagram_cols == "3") echo 'selected="selected"' ?> ><?php _e('3'); ?></option>
                            <option value="4" <?php if($sb_instagram_cols == "4") echo 'selected="selected"' ?> ><?php _e('4'); ?></option>
                            <option value="5" <?php if($sb_instagram_cols == "5") echo 'selected="selected"' ?> ><?php _e('5'); ?></option>
                            <option value="6" <?php if($sb_instagram_cols == "6") echo 'selected="selected"' ?> ><?php _e('6'); ?></option>
                            <option value="7" <?php if($sb_instagram_cols == "7") echo 'selected="selected"' ?> ><?php _e('7'); ?></option>
                            <option value="8" <?php if($sb_instagram_cols == "8") echo 'selected="selected"' ?> ><?php _e('8'); ?></option>
                            <option value="9" <?php if($sb_instagram_cols == "9") echo 'selected="selected"' ?> ><?php _e('9'); ?></option>
                            <option value="10" <?php if($sb_instagram_cols == "10") echo 'selected="selected"' ?> ><?php _e('10'); ?></option>
                        </select>
                        <br>
                        <a href="javascript:void(0);" class="sb_instagram_mobile_layout_reveal button-secondary"><?php _e( 'Show Mobile Options', 'instagram-feed' ); ?></a>
                        <br>
                        <div class="sb_instagram_mobile_layout_setting">

                            <p style="font-weight: bold; padding-bottom: 5px;"><?php _e('Number of Columns on Mobile', 'instagram-feed' );?></p>
                            <select name="sb_instagram_colsmobile">
                                <option value="auto" <?php if($sb_instagram_colsmobile == "auto") echo 'selected="selected"' ?> ><?php _e('Auto', 'instagram-feed'); ?></option>
                                <option value="same" <?php if($sb_instagram_colsmobile == "same") echo 'selected="selected"' ?> ><?php _e('Same as desktop', 'instagram-feed'); ?></option>
                                <option value="1" <?php if($sb_instagram_colsmobile == "1") echo 'selected="selected"' ?> ><?php _e('1'); ?></option>
                                <option value="2" <?php if($sb_instagram_colsmobile == "2") echo 'selected="selected"' ?> ><?php _e('2'); ?></option>
                                <option value="3" <?php if($sb_instagram_colsmobile == "3") echo 'selected="selected"' ?> ><?php _e('3'); ?></option>
                                <option value="4" <?php if($sb_instagram_colsmobile == "4") echo 'selected="selected"' ?> ><?php _e('4'); ?></option>
                                <option value="5" <?php if($sb_instagram_colsmobile == "5") echo 'selected="selected"' ?> ><?php _e('5'); ?></option>
                                <option value="6" <?php if($sb_instagram_colsmobile == "6") echo 'selected="selected"' ?> ><?php _e('6'); ?></option>
                                <option value="7" <?php if($sb_instagram_colsmobile == "7") echo 'selected="selected"' ?> ><?php _e('7'); ?></option>
                            </select>
                            &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What does \"Auto\" mean?", 'instagram-feed'); ?></a>
                            <p class="sbi_tooltip" style="padding: 10px 0 0 0;"><?php _e("This means that the plugin will automatically calculate how many columns to use for mobile based on the screen size and number of columns selected above. For example, a feed which is set to use 4 columns will show 2 columns for screen sizes less than 640 pixels and 1 column for screen sizes less than 480 pixels.", 'instagram-feed'); ?></p>
                        </div>
					    <?php if($sb_instagram_disable_mobile == true) $sb_instagram_colsmobile = 'same'; ?>

                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Padding around Images', 'instagram-feed'); ?></label><code class="sbi_shortcode"> imagepadding  imagepaddingunit</code></th>
                    <td>
                        <input name="sb_instagram_image_padding" type="text" value="<?php esc_attr_e( $sb_instagram_image_padding ); ?>" size="4" />
                        <select name="sb_instagram_image_padding_unit">
                            <option value="px" <?php if($sb_instagram_image_padding_unit == "px") echo 'selected="selected"' ?> ><?php _e('px'); ?></option>
                            <option value="%" <?php if($sb_instagram_image_padding_unit == "%") echo 'selected="selected"' ?> ><?php _e('%'); ?></option>
                        </select>
                    </td>
                </tr>

                </tbody>
            </table>
		    <?php submit_button(); ?>

            <hr id="headeroptions" />
            <h3><?php _e("Header", 'instagram-feed'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show Feed Header", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showheader
                            Eg: showheader=false</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_header" id="sb_instagram_show_header" <?php if($sb_instagram_show_header == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit sbi-expand-button">
                <a href="javascript:void(0);" class="button">Show Customization Options</a>
            </p>

            <table class="form-table sbi-expandable-options">
                <tbody>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Header Style', 'instagram-feed'); ?></label><code class="sbi_shortcode"> headerstyle
                            Eg: headerstyle=boxed</code></th>
                    <td>
                        <select name="sb_instagram_header_style" id="sb_instagram_header_style" style="float: left;">
                            <option value="standard" <?php if($sb_instagram_header_style == "standard") echo 'selected="selected"' ?> ><?php _e('Standard', 'instagram-feed'); ?></option>
                            <option value="boxed" <?php if($sb_instagram_header_style == "boxed") echo 'selected="selected"' ?> ><?php _e('Boxed', 'instagram-feed'); ?></option>
                            <option value="centered" <?php if($sb_instagram_header_style == "centered") echo 'selected="selected"' ?> ><?php _e('Centered', 'instagram-feed'); ?></option>
                        </select>
                        <div id="sb_instagram_header_style_boxed_options">
                            <p><?php _e('Please select 2 background colors for your Boxed header:', 'instagram-feed'); ?></p>
                            <div class="sbi_row">
                                <div class="sbi_col sbi_one">
                                    <label><?php _e('Primary Color', 'instagram-feed'); ?></label>
                                </div>
                                <div class="sbi_col sbi_two">
                                    <input name="sb_instagram_header_primary_color" type="text" value="<?php esc_attr_e( $sb_instagram_header_primary_color ); ?>" class="sbi_colorpick" />
                                </div>
                            </div>

                            <div class="sbi_row">
                                <div class="sbi_col sbi_one">
                                    <label><?php _e('Secondary Color', 'instagram-feed'); ?></label>
                                </div>
                                <div class="sbi_col sbi_two">
                                    <input name="sb_instagram_header_secondary_color" type="text" value="<?php esc_attr_e( $sb_instagram_header_secondary_color ); ?>" class="sbi_colorpick" />
                                </div>
                            </div>
                            <p style="margin-top: 10px;"><?php _e("Don't forget to set your text color below.", 'instagram-feed'); ?></p>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Header Size', 'instagram-feed'); ?></label><code class="sbi_shortcode"> headersize
                            Eg: headersize=medium</code></th>
                    <td>
                        <select name="sb_instagram_header_size" id="sb_instagram_header_size" style="float: left;">
                            <option value="small" <?php if($sb_instagram_header_size == "small") echo 'selected="selected"' ?> ><?php _e('Small', 'instagram-feed'); ?></option>
                            <option value="medium" <?php if($sb_instagram_header_size == "medium") echo 'selected="selected"' ?> ><?php _e('Medium', 'instagram-feed'); ?></option>
                            <option value="large" <?php if($sb_instagram_header_size == "large") echo 'selected="selected"' ?> ><?php _e('Large', 'instagram-feed'); ?></option>
                        </select>
                    </td>
                </tr>

                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show Number of Followers", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showfollowers
                            Eg: showfollowers=false</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_followers" id="sb_instagram_show_followers" <?php if($sb_instagram_show_followers == true) echo 'checked="checked"' ?> />
                        <span class="sbi_note"><?php _e("User feeds from a Business account only.", 'instagram-feed'); ?></span><a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("Why?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts...", 'instagram-feed'), '<a href="#">these directions</a>' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show Bio Text", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showbio
                            Eg: showbio=false</code></th>
                    <td>
		                <?php $sb_instagram_show_bio = isset( $sb_instagram_show_bio ) ? $sb_instagram_show_bio  : true; ?>
                        <input type="checkbox" name="sb_instagram_show_bio" id="sb_instagram_show_bio" <?php if($sb_instagram_show_bio == true) echo 'checked="checked"' ?> />
                        <span class="sbi_note"><?php _e("Only applies for Instagram accounts with bios", 'instagram-feed'); ?></span><br />
                        <div class="sb_instagram_box" style="display: block;">
                            <div class="sb_instagram_box_setting" style="display: block;">
                                <label style="padding-bottom: 0;"><?php _e("Add Custom Bio Text", 'instagram-feed'); ?></label><code class="sbi_shortcode" style="margin-top: 5px;"> custombio
                                    Eg: custombio="My custom bio."</code>
                                <br>
                                <span class="sbi_aside" style="padding-bottom: 5px; display: block;"><?php _e("Use your own custom bio text in the feed header. Bio text is automatically retrieved from Instagram for Business accounts.", 'instagram-feed'); ?></span>

                                <textarea type="text" name="sb_instagram_custom_bio" id="sb_instagram_custom_bio" ><?php echo esc_textarea( stripslashes( $sb_instagram_custom_bio ) ); ?></textarea>
                                &nbsp;<a class="sbi_tooltip_link sbi_tooltip_under" href="JavaScript:void(0);"><?php _e("Why is my bio not displaying automatically?", 'instagram-feed'); ?></a>
                                <p class="sbi_tooltip" style="padding: 10px 0 0 0; width: 99%;"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts...", 'instagram-feed'), '<a href="#">these directions</a>' ); ?></p>
                            </div>
                        </div>

                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e("Use Custom Avatar", 'instagram-feed'); ?></label><code class="sbi_shortcode"> customavatar
                            Eg: customavatar="https://my-website.com/avatar.jpg"</code></th>
                    <td>
                        <input type="text" name="sb_instagram_custom_avatar" class="large-text" id="sb_instagram_custom_avatar" value="<?php echo esc_attr( stripslashes( $sb_instagram_custom_avatar ) ); ?>" placeholder="https://example.com/avatar.jpg" />
                        <span class="sbi_aside"><?php _e("Avatar is automatically retrieved from Instagram for Business accounts", 'instagram-feed'); ?></span>
                        <br>
                        <a class="sbi_tooltip_link sbi_tooltip_under" href="JavaScript:void(0);"><?php _e("Why is my avatar not displaying automatically?", 'instagram-feed'); ?></a>

                        <p class="sbi_tooltip sbi_tooltip_under_text" style="padding: 10px 0 0 0;"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts...", 'instagram-feed'), '<a href="#">the</a>' ); ?></p>

                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Header Text Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> headercolor
                            Eg: headercolor=fff</code></th>
                    <td>
                        <input name="sb_instagram_header_color" type="text" value="<?php esc_attr_e( $sb_instagram_header_color ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Display Outside the Scrollable Area', 'instagram-feed'); ?></label><code class="sbi_shortcode"> headeroutside
                            Eg: headeroutside=true</code></th>
                    <td>
                        <input name="sb_instagram_outside_scrollable" type="checkbox" <?php if($sb_instagram_outside_scrollable == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What is this?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e('This positions the Header outside of the feed container. It is useful if your feed has a vertical scrollbar as it places it outside of the scrollable area and fixes it at the top.', 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Include Stories', 'instagram-feed'); ?></label><code class="sbi_shortcode"> stories
                            Eg: stories=true</code></th>
                    <td>
                        <input name="sb_instagram_stories" type="checkbox" <?php if($sb_instagram_stories == true) echo 'checked="checked"' ?> />

                        <span class="sbi_note"><?php _e('Business accounts only', 'instagram-feed'); ?></span><br />

                        <div class="sb_instagram_box" style="display: block;">
                            <div class="sb_instagram_box_setting" style="display: block;">
                                <label><?php _e("Slide Change Interval", 'instagram-feed'); ?></label><code class="sbi_shortcode"> storiestime
                                    Eg: storiestime=5000</code>
                                <br>
                                <input name="sb_instagram_stories_time" type="number" min="1000" step="500" value="<?php echo esc_attr( $sb_instagram_stories_time ); ?>" style="width: 80px;">milliseconds
                                <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What is this?', 'instagram-feed'); ?></a>
                                <p class="sbi_tooltip"><?php _e('This is the number of milliseconds that an image story slide will display before displaying the next slide. Videos always change when the video is finished.', 'instagram-feed'); ?></p>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>


            <hr id="loadmore" />
            <h3><?php _e("'Load More' Button", 'instagram-feed'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show the 'Load More' button", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showbutton
                            Eg: showbutton=false</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_btn" id="sb_instagram_show_btn" <?php if($sb_instagram_show_btn == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit sbi-expand-button">
                <a href="javascript:void(0);" class="button">Show Customization Options</a>
            </p>

            <table class="form-table sbi-expandable-options">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Background Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> buttoncolor
                            Eg: buttoncolor=8224e3</code></th>
                    <td>
                        <input name="sb_instagram_btn_background" type="text" value="<?php esc_attr_e( $sb_instagram_btn_background ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Text Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> buttontextcolor
                            Eg: buttontextcolor=eeee22</code></th>
                    <td>
                        <input name="sb_instagram_btn_text_color" type="text" value="<?php esc_attr_e( $sb_instagram_btn_text_color ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Text', 'instagram-feed'); ?></label><code class="sbi_shortcode"> buttontext
                            Eg: buttontext="Show more.."</code></th>
                    <td>
                        <input name="sb_instagram_btn_text" type="text" value="<?php echo stripslashes( esc_attr( $sb_instagram_btn_text ) ); ?>" size="30" />
                    </td>
                </tr>
                <tr valign="top">
                    <th class="bump-left"><label class="bump-left"><?php _e("Autoload more posts on scroll", 'instagram-feed'); ?></label><code class="sbi_shortcode"> autoscroll
                            Eg: autoscroll=true</code></th>
                    <td>
                        <input name="sb_instagram_autoscroll" type="checkbox" id="sb_instagram_autoscroll" <?php if($sb_instagram_autoscroll == true) echo "checked"; ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What is this?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e('This will make every Instagram feed load more posts as the user gets to the bottom of the feed. To enable this on only a specific feed use the autoscroll=true shortcode option.', 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Scroll Trigger Distance', 'instagram-feed'); ?></label><code class="sbi_shortcode"> autoscrolldistance
                            Eg: autoscrolldistance=200</code></th>
                    <td>
                        <input name="sb_instagram_autoscrolldistance" type="text" value="<?php echo stripslashes( esc_attr( $sb_instagram_autoscrolldistance ) ); ?>" size="30" />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What is this?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e('This is the distance in pixels from the bottom of the page the user must scroll to to trigger the loading of more posts.', 'instagram-feed'); ?></p>
                    </td>
                </tr>


                </tbody>
            </table>

            <hr id="follow" />
            <h3><?php _e("'Follow' Button", 'instagram-feed'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show the Follow button", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showfollow
                            Eg: showfollow=true</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_follow_btn" id="sb_instagram_show_follow_btn" <?php if($sb_instagram_show_follow_btn == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>
                </tbody>
            </table>

            <p class="submit sbi-expand-button">
                <a href="javascript:void(0);" class="button">Show Customization Options</a>
            </p>

            <table class="form-table sbi-expandable-options">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Background Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> followcolor
                            Eg: followcolor=28a1bf</code></th>
                    <td>
                        <input name="sb_instagram_folow_btn_background" type="text" value="<?php esc_attr_e( $sb_instagram_folow_btn_background ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Text Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> followtextcolor
                            Eg: followtextcolor=000</code></th>
                    <td>
                        <input name="sb_instagram_follow_btn_text_color" type="text" value="<?php esc_attr_e( $sb_instagram_follow_btn_text_color ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Button Text', 'instagram-feed'); ?></label><code class="sbi_shortcode"> followtext
                            Eg: followtext="Follow me"</code></th>
                    <td>
                        <input name="sb_instagram_follow_btn_text" type="text" value="<?php echo stripslashes( esc_attr( $sb_instagram_follow_btn_text ) ); ?>" size="30" />
                    </td>
                </tr>
                </tbody>
            </table>

		    <?php submit_button(); ?>


	    <?php } //End Customize General tab ?>

	    <?php if( $sbi_active_tab == 'customize-posts' ) { //Start Customize Posts tab ?>

            <p class="sb_instagram_contents_links" id="general">
                <span><?php _e('Jump to:', 'instagram-feed'); ?> </span>
                <a href="#photos"><?php _e('Photos', 'instagram-feed'); ?></a>
                <a href="#hover"><?php _e('Photo Hover Style', 'instagram-feed'); ?></a>
                <a href="#caption"><?php _e('Caption', 'instagram-feed'); ?></a>
                <a href="#likes"><?php _e('Likes &amp; Comments Icons', 'instagram-feed'); ?></a>
                <a href="#comments"><?php _e('Lightbox Comments', 'instagram-feed'); ?></a>
            </p>

            <input type="hidden" name="<?php echo $sb_instagram_customize_posts_hidden_field; ?>" value="Y">

            <hr id="photos" />
            <h3><?php _e('Photos', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Sort Photos By', 'instagram-feed'); ?></label><code class="sbi_shortcode"> sortby
                            Eg: sortby=random</code></th>
                    <td>
                        <select name="sb_instagram_sort" class="sb_instagram_sort">
                            <option value="none" <?php if($sb_instagram_sort == "none") echo 'selected="selected"' ?> ><?php _e('Newest to oldest', 'instagram-feed'); ?></option>
                            <option value="random" <?php if($sb_instagram_sort == "random") echo 'selected="selected"' ?> ><?php _e('Random', 'instagram-feed'); ?></option>
                            <option value="likes" <?php if($sb_instagram_sort == "likes") echo 'selected="selected"' ?> ><?php _e('Likes*', 'instagram-feed'); ?></option>
                        </select>
                        <div class="sbi_likes_explain sb_instagram_box">
                            <p>
	                            *<?php _e('Sorting by likes is available for business accounts only. Feed will use up to the most recent 200 posts. It\'s recommended that you use background caching (setting found on the Configure tab) to prevent slow feed load times.', 'instagram-feed'); ?>
                            </p>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Image Resolution', 'instagram-feed'); ?></label><code class="sbi_shortcode"> imageres
                            Eg: imageres=thumb</code></th>
                    <td>
					    <?php
					    $sb_instagram_using_custom_sizes = get_option( 'sb_instagram_using_custom_sizes' );
					    $sb_standard_res_name = 'sb_instagram_image_res';
					    $sb_standard_res_class = '';
					    $sb_custom_res_name = '';
					    $sb_custom_res_class = ' style="display:none;"';
					    if ( $sb_instagram_using_custom_sizes == 1 ) {
						    $sb_custom_res_name = 'sb_instagram_image_res';
						    $sb_standard_res_name = '';
						    $sb_custom_res_class = '';
						    $sb_standard_res_class = ' style="opacity:.5"';
					    }

					    ?>
                        <select id="sb_standard_res_settings" name="<?php echo $sb_standard_res_name; ?>"<?php echo $sb_standard_res_class; ?>>
                            <option value="auto" <?php if($sb_instagram_image_res == "auto") echo 'selected="selected"' ?> ><?php _e('Auto-detect (recommended)', 'instagram-feed'); ?></option>
                            <option value="thumb" <?php if($sb_instagram_image_res == "thumb") echo 'selected="selected"' ?> ><?php _e('Thumbnail (150x150)', 'instagram-feed'); ?></option>
                            <option value="medium" <?php if($sb_instagram_image_res == "medium") echo 'selected="selected"' ?> ><?php _e('Medium (320x320)', 'instagram-feed'); ?></option>
                            <option value="full" <?php if($sb_instagram_image_res == "full") echo 'selected="selected"' ?> ><?php _e('Full size (640x640)', 'instagram-feed'); ?></option>
                        </select>

                        &nbsp<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What does Auto-detect mean?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Auto-detect means that the plugin automatically sets the image resolution based on the size of your feed.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Media Type to Display', 'instagram-feed'); ?></label><code class="sbi_shortcode"> media
                            Eg: media=photos
                            media=videos
                            media=all</code></th>
                    <td>
                        <select name="sb_instagram_media_type">
                            <option value="all" <?php if($sb_instagram_media_type == "all") echo 'selected="selected"' ?> ><?php _e('All', 'instagram-feed'); ?></option>
                            <option value="photos" <?php if($sb_instagram_media_type == "photos") echo 'selected="selected"' ?> ><?php _e('Photos only', 'instagram-feed'); ?></option>
                            <option value="videos" <?php if($sb_instagram_media_type == "videos") echo 'selected="selected"' ?> ><?php _e('Videos only', 'instagram-feed'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e("Disable Pop-up Lightbox", 'instagram-feed'); ?></label><code class="sbi_shortcode"> disablelightbox
                            Eg: disablelightbox=true</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_disable_lightbox" id="sb_instagram_disable_lightbox" <?php if($sb_instagram_disable_lightbox == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e("Link Posts to URL in Caption (Shoppable feed)", 'instagram-feed'); ?></label><code class="sbi_shortcode"> captionlinks
                            Eg: captionlinks=true</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_captionlinks" id="sb_instagram_captionlinks" <?php if($sb_instagram_captionlinks == true) echo 'checked="checked"' ?> />
                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What will this do?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php echo sprintf( __("Checking this box will change the link for each post to any url included in the caption for that Instagram post. The lightbox will be disabled.", 'instagram-feed'), '<a href="#">'. __( 'this link', 'instagram-feed' ) . '</a>' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Post Offset", 'instagram-feed'); ?></label><code class="sbi_shortcode"> offset
                            Eg: offset=1</code></th>
                    <td>
                        <input name="sb_instagram_offset" id="sb_instagram_offset" type="text" value="<?php echo esc_attr( $sb_instagram_offset ); ?>" size="4" />Posts
                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Feed will \"skip\" this many posts or offset the start of your posts by this number.", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr id="hover" />
            <h3><?php _e('Photo Hover Style', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Hover Background Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> hovercolor
                            Eg: hovercolor=1e73be</code></th>
                    <td>
                        <input name="sb_hover_background" type="text" value="<?php esc_attr_e( $sb_hover_background ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Hover Text Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> hovertextcolor
                            Eg: hovertextcolor=fff</code></th>
                    <td>
                        <input name="sb_hover_text" type="text" value="<?php esc_attr_e( $sb_hover_text ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Information to display', 'instagram-feed'); ?></label><code class="sbi_shortcode"> hoverdisplay
                            Eg: hoverdisplay='username,date'

                            Options: username, date, instagra, caption, likes</code></th>
                    <td>
                        <div>
                            <input name="sbi_hover_inc_username" type="checkbox" id="sbi_hover_inc_username" <?php if($sbi_hover_inc_username == true) echo "checked"; ?> />
                            <label for="sbi_hover_inc_username"><?php _e('Username', 'instagram-feed'); ?>&nbsp;<span style="font-size: 12px;">(<?php _e('User feeds only', 'instagram-feed'); ?>)</span></label>
                        </div>
                        <div>
                            <input name="sbi_hover_inc_date" type="checkbox" id="sbi_hover_inc_date" <?php if($sbi_hover_inc_date == true) echo "checked"; ?> />
                            <label for="sbi_hover_inc_date"><?php _e('Date', 'instagram-feed'); ?>&nbsp;<span style="font-size: 12px;">(<?php _e('User feeds only', 'instagram-feed'); ?>)</span></label>
                        </div>
                        <div>
                            <input name="sbi_hover_inc_instagram" type="checkbox" id="sbi_hover_inc_instagram" <?php if($sbi_hover_inc_instagram == true) echo "checked"; ?> />
                            <label for="sbi_hover_inc_instagram"><?php _e('Instagram Icon/Link', 'instagram-feed'); ?></label>
                        </div>
                        <div>
                            <input name="sbi_hover_inc_caption" type="checkbox" id="sbi_hover_inc_caption" <?php if($sbi_hover_inc_caption == true) echo "checked"; ?> />
                            <label for="sbi_hover_inc_caption"><?php _e('Caption', 'instagram-feed'); ?></label>
                        </div>
                        <div>
                            <input name="sbi_hover_inc_likes" type="checkbox" id="sbi_hover_inc_likes" <?php if($sbi_hover_inc_likes == true) echo "checked"; ?> />
                            <label for="sbi_hover_inc_likes"><?php _e('Like/Comment Icons', 'instagram-feed'); ?>&nbsp;<span style="font-size: 12px;">(<?php _e('Business accounts only.', 'instagram-feed'); ?> <a class="sbi_tooltip_link" href="JavaScript:void(0);" style="font-size: 12px; margin: 0;"><?php _e("Why?", 'instagram-feed'); ?></a>)<span class="sbi_tooltip"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts..", 'instagram-feed'), '<a href="#">these directions</a>' ); ?></span></span></label>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>

		    <?php submit_button(); ?>

            <hr id="caption" />
            <h3><?php _e("Caption", 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show Caption", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showcaption
                            Eg: showcaption=false</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_caption" id="sb_instagram_show_caption" <?php if($sb_instagram_show_caption == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Maximum Text Length", 'instagram-feed'); ?></label><code class="sbi_shortcode"> captionlength
                            Eg: captionlength=20</code></th>
                    <td>
                        <input name="sb_instagram_caption_length" id="sb_instagram_caption_length" type="text" value="<?php esc_attr_e( $sb_instagram_caption_length ); ?>" size="4" />Characters
                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("The number of characters of text to display in the caption. An elipsis link will be added to allow the user to reveal more text if desired.", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Text Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> captioncolor
                            Eg: captioncolor=dd3333</code></th>
                    <td>
                        <input name="sb_instagram_caption_color" type="text" value="<?php esc_attr_e( $sb_instagram_caption_color ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Text Size', 'instagram-feed'); ?></label><code class="sbi_shortcode"> captionsize
                            Eg: captionsize=12</code></th>
                    <td>
                        <select name="sb_instagram_caption_size" style="width: 180px;">
                            <option value="inherit" <?php if($sb_instagram_caption_size == "inherit") echo 'selected="selected"' ?> ><?php _e('Inherit from theme', 'instagram-feed'); ?></option>
                            <option value="10" <?php if($sb_instagram_caption_size == "10") echo 'selected="selected"' ?> ><?php _e('10px'); ?></option>
                            <option value="11" <?php if($sb_instagram_caption_size == "11") echo 'selected="selected"' ?> ><?php _e('11px'); ?></option>
                            <option value="12" <?php if($sb_instagram_caption_size == "12") echo 'selected="selected"' ?> ><?php _e('12px'); ?></option>
                            <option value="13" <?php if($sb_instagram_caption_size == "13") echo 'selected="selected"' ?> ><?php _e('13px'); ?></option>
                            <option value="14" <?php if($sb_instagram_caption_size == "14") echo 'selected="selected"' ?> ><?php _e('14px'); ?></option>
                            <option value="16" <?php if($sb_instagram_caption_size == "16") echo 'selected="selected"' ?> ><?php _e('16px'); ?></option>
                            <option value="18" <?php if($sb_instagram_caption_size == "18") echo 'selected="selected"' ?> ><?php _e('18px'); ?></option>
                            <option value="20" <?php if($sb_instagram_caption_size == "20") echo 'selected="selected"' ?> ><?php _e('20px'); ?></option>
                            <option value="24" <?php if($sb_instagram_caption_size == "24") echo 'selected="selected"' ?> ><?php _e('24px'); ?></option>
                            <option value="28" <?php if($sb_instagram_caption_size == "28") echo 'selected="selected"' ?> ><?php _e('28px'); ?></option>
                            <option value="32" <?php if($sb_instagram_caption_size == "32") echo 'selected="selected"' ?> ><?php _e('32px'); ?></option>
                            <option value="36" <?php if($sb_instagram_caption_size == "36") echo 'selected="selected"' ?> ><?php _e('36px'); ?></option>
                            <option value="40" <?php if($sb_instagram_caption_size == "40") echo 'selected="selected"' ?> ><?php _e('40px'); ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr id="likes" />
            <h3><?php _e("Likes &amp; Comments Icons", 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e("Show Icons", 'instagram-feed'); ?></label><code class="sbi_shortcode"> showlikes
                            Eg: showlikes=false</code></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_show_meta" id="sb_instagram_show_meta" <?php if($sb_instagram_show_meta == true) echo 'checked="checked"' ?> />
                        <span class="sbi_note"><?php _e("Only available for Business accounts", 'instagram-feed'); ?></span>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("Why?", 'instagram-feed'); ?></a>
                        
                        <p class="sbi_tooltip"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts..", 'instagram-feed'), '<a href="#">these directions</a>' ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Icon Color', 'instagram-feed'); ?></label><code class="sbi_shortcode"> likescolor
                            Eg: likescolor=fff</code></th>
                    <td>
                        <input name="sb_instagram_meta_color" type="text" value="<?php esc_attr_e( $sb_instagram_meta_color ); ?>" class="sbi_colorpick" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Icon Size', 'instagram-feed'); ?></label><code class="sbi_shortcode"> likessize
                            Eg: likessize=14</code></th>
                    <td>
                        <select name="sb_instagram_meta_size" style="width: 180px;">
                            <option value="inherit" <?php if($sb_instagram_meta_size == "inherit") echo 'selected="selected"' ?> ><?php _e('Inherit from theme', 'instagram-feed'); ?></option>
                            <option value="10" <?php if($sb_instagram_meta_size == "10") echo 'selected="selected"' ?> ><?php _e('10px'); ?></option>
                            <option value="11" <?php if($sb_instagram_meta_size == "11") echo 'selected="selected"' ?> ><?php _e('11px'); ?></option>
                            <option value="12" <?php if($sb_instagram_meta_size == "12") echo 'selected="selected"' ?> ><?php _e('12px'); ?></option>
                            <option value="13" <?php if($sb_instagram_meta_size == "13") echo 'selected="selected"' ?> ><?php _e('13px'); ?></option>
                            <option value="14" <?php if($sb_instagram_meta_size == "14") echo 'selected="selected"' ?> ><?php _e('14px'); ?></option>
                            <option value="16" <?php if($sb_instagram_meta_size == "16") echo 'selected="selected"' ?> ><?php _e('16px'); ?></option>
                            <option value="18" <?php if($sb_instagram_meta_size == "18") echo 'selected="selected"' ?> ><?php _e('18px'); ?></option>
                            <option value="20" <?php if($sb_instagram_meta_size == "20") echo 'selected="selected"' ?> ><?php _e('20px'); ?></option>
                            <option value="24" <?php if($sb_instagram_meta_size == "24") echo 'selected="selected"' ?> ><?php _e('24px'); ?></option>
                            <option value="28" <?php if($sb_instagram_meta_size == "28") echo 'selected="selected"' ?> ><?php _e('28px'); ?></option>
                            <option value="32" <?php if($sb_instagram_meta_size == "32") echo 'selected="selected"' ?> ><?php _e('32px'); ?></option>
                            <option value="36" <?php if($sb_instagram_meta_size == "36") echo 'selected="selected"' ?> ><?php _e('36px'); ?></option>
                            <option value="40" <?php if($sb_instagram_meta_size == "40") echo 'selected="selected"' ?> ><?php _e('40px'); ?></option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>

            <hr id="comments" />
            <h3><?php _e('Lightbox Comments', 'instagram-feed'); ?></h3>
            <div>
                <p><span style="margin: -10px 0 0 0; font-style: italic; font-size: 12px;"><?php _e('Comments available for User feeds from Business accounts only', 'instagram-feed'); ?></span>
                <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("Why?", 'instagram-feed'); ?></a>
                            
                <span class="sbi_tooltip"><?php echo sprintf( __("Instagram is deprecating their old API for Personal accounts..", 'instagram-feed'), '<a href="#">these directions</a>' ); ?></span>
                </p>
            </div>

            <table class="form-table">
                <tbody>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Show Comments in Lightbox', 'instagram-feed'); ?></label><code class="sbi_shortcode"> lightboxcomments
                            Eg: lightboxcomments="true"</code></th>
                    <td style="padding: 5px 10px 0 10px;">
                        <input type="checkbox" name="sb_instagram_lightbox_comments" id="sb_instagram_lightbox_comments" <?php if($sb_instagram_lightbox_comments == true) echo 'checked="checked"' ?> style="margin-right: 15px;" />
                        <input id="sbi_clear_comment_cache" class="button-secondary" style="margin-top: -5px;" type="submit" value="<?php esc_attr_e( 'Clear Comment Cache' ); ?>" />
                        &nbsp<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This will remove the cached comments saved in the database", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Number of Comments', 'instagram-feed'); ?></label><code class="sbi_shortcode"> numcomments
                            Eg: numcomments="10"</code></th>
                    <td>
                        <input name="sb_instagram_num_comments" type="text" value="<?php esc_attr_e( $sb_instagram_num_comments ); ?>" size="4" />
                        <span class="sbi_note"><?php _e('Max number of latest comments.', 'instagram-feed'); ?></span>
                        &nbsp<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This is the maximum number of comments that will be shown in the lightbox. If there are more comments available than the number set, only the latest comments will be shown", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                </tbody>
            </table>

		    <?php submit_button(); ?>

	    <?php } //End Customize Posts tab ?>

	    <?php if( $sbi_active_tab == 'customize-moderation' ) { //Start Customize Moderation tab ?>

            <p class="sb_instagram_contents_links" id="general">
                <span><?php _e('Jump to:', 'instagram-feed'); ?> </span>
                <a href="#filtering"><?php _e('Post Filtering', 'instagram-feed'); ?></a>
                <a href="#moderation"><?php _e('Moderation', 'instagram-feed'); ?></a>
            </p>

            <input type="hidden" name="<?php echo $sb_instagram_customize_moderation_hidden_field; ?>" value="Y">

            <hr id="filtering" />
            <h3><?php _e('Post Filtering', 'instagram-feed'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Remove photos containing these words or hashtags', 'instagram-feed'); ?></label><code class="sbi_shortcode"> excludewords
                            Eg: excludewords="naughty, words"</code></th>
                    <td>
                        <div class="sb_instagram_apply_labels">
                            <p>Apply to:</p>
                            <input name="sb_instagram_ex_apply_to" id="sb_instagram_ex_all" class="sb_instagram_incex_one_all" type="radio" value="all" <?php if ( $sb_instagram_ex_apply_to == 'all' ) echo 'checked'; ?>/><label for="sb_instagram_ex_all">All feeds</label>
                            <input name="sb_instagram_ex_apply_to" id="sb_instagram_ex_one" class="sb_instagram_incex_one_all" type="radio" value="one" <?php if ( $sb_instagram_ex_apply_to == 'one' ) echo 'checked'; ?>/><label for="sb_instagram_ex_one">One feed</label>
                        </div>

                        <input name="sb_instagram_exclude_words" id="sb_instagram_exclude_words" type="text" style="width: 70%;" value="<?php esc_attr_e( stripslashes($sb_instagram_exclude_words) ); ?>" />
                        <p class="sbi_extra_info sbi_incex_shortcode" <?php if ( $sb_instagram_ex_apply_to == 'one' ) echo 'style="display:block;"'; ?>><?php echo sprintf( __('Add this to the shortcode for your feed %s', 'instagram-feed'), '<code></code>' ); ?></p>

                        <br />
                        <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate words/hashtags using commas', 'instagram-feed'); ?></span>

                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("You can use this setting to remove photos which contain certain words or hashtags in the caption. Separate multiple words or hashtags using commas.", 'instagram-feed'); ?></p>

                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Show photos containing these words or hashtags', 'instagram-feed'); ?></label><code class="sbi_shortcode"> includewords
                            Eg: includewords="sunshine"</code></th>
                    <td>
                        <div class="sb_instagram_apply_labels">
                            <p>Apply to:</p>
                            <input name="sb_instagram_inc_apply_to" id="sb_instagram_inc_all" class="sb_instagram_incex_one_all" type="radio" value="all" <?php if ( $sb_instagram_inc_apply_to == 'all' ) echo 'checked'; ?>/><label for="sb_instagram_inc_all">All feeds</label>
                            <input name="sb_instagram_inc_apply_to" id="sb_instagram_inc_one" class="sb_instagram_incex_one_all" type="radio" value="one" <?php if ( $sb_instagram_inc_apply_to == 'one' ) echo 'checked'; ?>/><label for="sb_instagram_inc_one">One feed</label>
                        </div>

                        <input name="sb_instagram_include_words" id="sb_instagram_include_words" type="text" style="width: 70%;" value="<?php esc_attr_e( stripslashes($sb_instagram_include_words) ); ?>" />
                        <p class="sbi_extra_info sbi_incex_shortcode" <?php if ( $sb_instagram_ex_apply_to == 'one' ) echo 'style="display:block;"'; ?>><?php echo sprintf( __('Add this to the shortcode for your feed %s', 'instagram-feed'), '<code></code>' ); ?></p>

                        <br />
                        <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate words/hashtags using commas', 'instagram-feed'); ?></span>

                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php echo sprintf( __("You can use this setting to only show photos which contain certain words or hashtags in the caption. For example, adding %s will show any photos which contain either the word sheep, cow, or dog. Separate multiple words or hashtags using commas.", 'instagram-feed'), '<code>' . __( 'sheep, cow, dog', 'instagram-feed' ) . '</code>' ); ?></p>

                    </td>
                </tr>
                </tbody>
            </table>

            <p>
                <a class="sbi_tooltip_link" href="JavaScript:void(0);" style="margin-left: 0;"><?php _e("Can I filter words or hashtags from comments?", 'instagram-feed'); ?></a>             
                <span class="sbi_tooltip" style="width: 100%;"><?php _e("Instagram is deprecating their old API for Personal accounts...", 'instagram-feed'); ?></span>
            </p>

            <hr id="moderation" />
            <h3><?php _e('Moderation', 'instagram-feed'); ?></h3>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php _e('Moderation Type', 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_moderation_mode" id="sb_instagram_moderation_mode_visual" class="sb_instagram_moderation_mode" type="radio" value="visual" <?php if ( $sb_instagram_moderation_mode === 'visual' ) echo 'checked'; ?> style="margin-top: 0;" /><label for="sb_instagram_moderation_mode_visual">Visual</label>
                        <input name="sb_instagram_moderation_mode" id="sb_instagram_moderation_mode_manual" class="sb_instagram_moderation_mode" type="radio" value="manual" <?php if ( $sb_instagram_moderation_mode === 'manual' ) echo 'checked'; ?> style="margin-top: 0; margin-left: 10px;"/><label for="sb_instagram_moderation_mode_manual">Manual</label>

                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><b><?php _e('Visual Moderation Mode', 'instagram-feed' ); ?></b><br /><?php echo sprintf( __("This adds a button to each feed that will allow you to hide posts, and create white lists from the front end using a visual interface.", 'instagram-feed'), '<a href="#">' . __('this page', 'instagram-feed' ) . '</a>' ); ?></p>

                        <br />
                        <div class="sbi_mod_manual_settings">

                            <div class="sbi_row">
                                <label><?php _e('Hide specific photos', 'instagram-feed'); ?></label>
                                <textarea name="sb_instagram_hide_photos" id="sb_instagram_hide_photos" style="width: 100%;" rows="3"><?php esc_attr_e( stripslashes($sb_instagram_hide_photos) ); ?></textarea>
                                <br />
                                <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate IDs using commas', 'instagram-feed'); ?></span>

                                &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                                <p class="sbi_tooltip"><?php _e("You can use this setting to hide specific photos in your feed. Just click the 'Hide Photo' link in the photo pop-up in your feed to get the ID of the photo, then copy and paste it into this text box.", 'instagram-feed'); ?></p>
                            </div>
						    <?php if ( !empty( $sb_instagram_block_users ) ) : ?>
                                <div class="sbi_row">
                                    <label><?php _e('Block users', 'instagram-feed'); ?></label>
                                    <input name="sb_instagram_block_users" id="sb_instagram_block_users" type="text" style="width: 100%;" value="<?php esc_attr_e( stripslashes($sb_instagram_block_users) ); ?>" />

                                    <br />
                                    <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate usernames using commas', 'instagram-feed'); ?></span>

                                    &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                                    <p class="sbi_tooltip"><?php _e("You can use this setting to block photos from certain users in your feed. Just enter the usernames here which you want to block. Separate multiple usernames using commas.", 'instagram-feed'); ?></p>
                                </div>
						    <?php endif; ?>

                        </div>

                    </td>
                </tr>
			    <?php if ( !empty( $sb_instagram_show_users ) ) : ?>

                    <tr valign="top">
                        <th scope="row"><label><?php _e('Only show posts by these users', 'instagram-feed'); ?></label></th>
                        <td>

                            <input name="sb_instagram_show_users" id="sb_instagram_show_users" type="text" style="width: 70%;" value="<?php esc_attr_e( stripslashes($sb_instagram_show_users) ); ?>" />

                            <br />
                            <span class="sbi_note" style="margin-left: 0;"><?php _e('Separate usernames using commas', 'instagram-feed'); ?></span>

                            &nbsp<a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e("What is this?", 'instagram-feed'); ?></a>
                            <p class="sbi_tooltip"><?php _e("You can use this setting to show photos only from certain users in your feed. Just enter the usernames here which you want to show. Separate multiple usernames using commas.", 'instagram-feed'); ?></p>

                        </td>
                    </tr>
			    <?php endif; ?>

                <tr valign="top">
                    <th scope="row"><label><?php _e('White lists', 'instagram-feed'); ?></label></th>
                    <td>
                        <div class="sbi_white_list_names_wrapper">
						    <?php
						    $sbi_current_white_names = get_option( 'sb_instagram_white_list_names', array() );

						    if( empty($sbi_current_white_names) ){
							    _e("No white lists currently created", 'instagram-feed');
						    } else {
							    $sbi_white_size = count( $sbi_current_white_names );
							    $sbi_i = 1;
							    echo 'IDs: ';
							    foreach ( $sbi_current_white_names as $white ) {
								    if( $sbi_i !== $sbi_white_size ) {
									    echo '<span>'.$white.', </span>';
								    } else {
									    echo '<span>'.$white.'</span>';
								    }
								    $sbi_i++;
							    }
							    echo '<br />';
						    }
						    ?>
                        </div>

                        <input id="sbi_clear_white_lists" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Clear White Lists' ); ?>" />
                        &nbsp;<a class="sbi_tooltip_link" href="JavaScript:void(0);" style="display: inline-block; margin-top: 5px;"><?php _e("What is this?", 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This will remove all of the white lists from the database", 'instagram-feed'); ?></p>

					    <?php
					    $permanent_white_lists = get_option( 'sb_permanent_white_lists', array() );
					    if ( ! empty( $permanent_white_lists ) &&  ! empty( $sbi_current_white_names ) ) {
						    $sbi_white_size = count( $permanent_white_lists );
						    $sbi_i = 1;
						    echo '<div class="sbi_white_list_names_wrapper sbi_white_list_perm">';
						    echo 'Permanent: ';
						    foreach ( $permanent_white_lists as $white ) {
							    if( $sbi_i !== $sbi_white_size ) {
								    echo '<span>'.$white.', </span>';
							    } else {
								    echo '<span style="margin-right: 10px;">'.$white.'</span>';
							    }
							    $sbi_i++;
						    }
						    echo '<input id="sbi_clear_permanent_white_lists" class="button-secondary" type="submit" value="' . esc_attr__( 'Disable Permanent White Lists' ) . '" style="vertical-align: middle;"/>';
						    echo '</div>';
					    }
					    ?>
                    </td>
                </tr>

                </tbody>
            </table>

		    <?php submit_button(); ?>

	    <?php } //End Customize Moderation tab ?>

	    <?php if( $sbi_active_tab == 'customize-advanced' ) { //Start Customize Advanced tab ?>

            <p class="sb_instagram_contents_links" id="general">
                <span><?php _e('Jump to:', 'instagram-feed'); ?> </span>
                <a href="#snippets"><?php _e('Custom Code', 'instagram-feed'); ?></a>
                <a href="#misc"><?php _e('Misc', 'instagram-feed'); ?></a>
            </p>

            <input type="hidden" name="<?php echo $sb_instagram_customize_advanced_hidden_field; ?>" value="Y">

            <hr id="snippets" />
            <h3><?php _e('Custom Code Snippets', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <td style="padding-bottom: 0;">
                        <strong style="font-size: 15px;"><?php _e( 'Custom CSS', 'instagram-feed' ); ?></strong><br />
					    <?php _e( 'Enter your own custom CSS in the box below', 'instagram-feed' ); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <td>
                        <textarea name="sb_instagram_custom_css" id="sb_instagram_custom_css" style="width: 70%;" rows="7"><?php esc_attr_e( stripslashes($sb_instagram_custom_css) ); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <td style="padding-bottom: 0;">
                        <strong style="font-size: 15px;"><?php _e('Custom JavaScript', 'instagram-feed'); ?></strong><br />
					    <?php _e( 'Enter your own custom JavaScript/jQuery in the box below', 'instagram-feed' ); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <td>
                        <textarea name="sb_instagram_custom_js" id="sb_instagram_custom_js" style="width: 70%;" rows="7"><?php esc_attr_e( stripslashes($sb_instagram_custom_js) ); ?></textarea>
                        <br /><span class="sbi_note" style="margin: 5px 0 0 2px; display: block;"><b><?php _e('Note:', 'instagram-feed'); ?></b> <?php _e('Custom JavaScript reruns every time more posts are loaded into the feed', 'instagram-feed'); ?></span>
                    </td>
                </tr>
                </tbody>
            </table>

		    <?php submit_button(); ?>

            <hr id="misc" />
            <h3><?php _e('Misc', 'instagram-feed'); ?></h3>

            <table class="form-table">
                <tbody>
                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Are you using an Ajax powered theme?", 'instagram-feed'); ?></label><code class="sbi_shortcode"> ajaxtheme
                            Eg: ajaxtheme=true</code></th>
                    <td>
                        <input name="sb_instagram_ajax_theme" type="checkbox" id="sb_instagram_ajax_theme" <?php if($sb_instagram_ajax_theme == true) echo "checked"; ?> />
                        <label for="sb_instagram_ajax_theme"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("When navigating your site, if your theme uses Ajax to load content into your pages (meaning your page doesn't refresh) then check this setting. If you're not sure then it's best to leave this setting unchecked while checking with your theme author, otherwise checking it may cause a problem.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Image Resizing", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_disable_resize" type="checkbox" id="sb_instagram_disable_resize" <?php if($sb_instagram_disable_resize == true) echo "checked"; ?> />
                        <label for="sb_instagram_disable_resize"><?php _e('Disable Image Resizing', 'instagram-feed'); ?></label>                        <span>                      <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("The disables the local storing of image files to use for images in the plugin.", 'instagram-feed'); ?></p><br><br>
                        </span>
                        <input name="sb_instagram_favor_local" type="checkbox" id="sb_instagram_favor_local" <?php if($sb_instagram_favor_local == true) echo "checked"; ?> />
                        <span><label for="sb_instagram_favor_local"><?php _e('Favor Local Images', 'instagram-feed'); ?></label>                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("The plugin creates and stores resized versions of images in order to serve a more optimized resolution size in the feed. Enable this setting to always use these images if one is available.", 'instagram-feed'); ?></p><br><br>
                        </span>
                        <input id="sbi_reset_resized" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Reset Resized Images' ); ?>" style="vertical-align: middle;"/>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("The plugin creates and stores resized versions of images in order to serve a more optimized resolution size in the feed. The local images and records in custom database tables are also used to store posts from recent hashtag feeds that are no longer available from Instagram's API. Click this button to clear all data related to resized images. Enable the setting to favor local images to always use a local, resized image if one is available.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Max concurrent API requests', 'instagram-feed'); ?></label><code class="sbi_shortcode"> maxrequests
                            Eg: maxrequests=2</code></th>
                    <td>
                        <input name="sb_instagram_requests_max" type="number" min="1" max="10" value="<?php echo esc_attr( $sb_instagram_requests_max ); ?>" />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Change the number of maximum concurrent API requests. This is not recommended unless directed by a member of the support team.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('API request size', 'instagram-feed'); ?></label><code class="sbi_shortcode"> minnum
                            Eg: minnum=25</code></th>
                    <td>
                        <input name="sb_instagram_minnum" type="number" min="0" max="100" value="<?php echo esc_attr( $sb_instagram_minnum ); ?>" />
                        <span class="sbi_note"><?php _e('Leave at "0" for default', 'instagram-feed'); ?></span>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("If your feed contains a lot of IG TV posts or your feed is not displaying any posts despite there being posts available on Instagram.com, try increasing this number to 25 or more.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th class="bump-left">
                        <label class="bump-left"><?php _e("Load initial posts with AJAX", 'instagram-feed'); ?></label>
                    </th>
                    <td>
                        <input name="sb_ajax_initial" type="checkbox" id="sb_ajax_initial" <?php if($sb_ajax_initial == true) echo "checked"; ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Initial posts will be loaded using AJAX instead of added to the page directly. If you use page caching, this will allow the feed to update according to the \"Check for new posts every\" setting on the \"Configure\" tab.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th class="bump-left">
                        <label for="sb_instagram_cron" class="bump-left"><?php _e("Force cache to clear on interval", 'instagram-feed'); ?></label>
                    </th>
                    <td>
                        <select name="sb_instagram_cron">
                            <option value="unset" <?php if($sb_instagram_cron == "unset") echo 'selected="selected"' ?> ><?php _e(' - '); ?></option>
                            <option value="yes" <?php if($sb_instagram_cron == "yes") echo 'selected="selected"' ?> ><?php _e('Yes', 'instagram-feed'); ?></option>
                            <option value="no" <?php if($sb_instagram_cron == "no") echo 'selected="selected"' ?> ><?php _e('No', 'instagram-feed'); ?></option>
                        </select>

                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("If you're experiencing an issue with the plugin not auto-updating then you can set this to 'Yes' to run a scheduled event behind the scenes which forces the plugin cache to clear on a regular basis and retrieve new data from Instagram.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th><label><?php _e("Enable Backup/Permanent caching", 'instagram-feed'); ?></label></th>
                    <td class="sbi-customize-tab-opt">
                        <input name="sb_instagram_backup" type="checkbox" id="sb_instagram_backup" <?php if($sb_instagram_backup == true) echo "checked"; ?> />
                        <input id="sbi_clear_backups" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Clear Backup/Permanent Caches' ); ?>" />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e('Every feed will save a duplicate version of itself in the database to be used if the normal cache is not available.', 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Enqueue JS file in head', 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enqueue_js_in_head" id="sb_instagram_enqueue_js_in_head" <?php if($enqueue_js_in_head == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Check this box if you'd like to enqueue the JavaScript file for the plugin in the head instead of the footer.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Enqueue CSS file with shortcode', 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="enqueue_css_in_shortcode" id="sb_instagram_enqueue_css_in_shortcode" <?php if($enqueue_css_in_shortcode == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Check this box if you'd like to only include the CSS file for the plugin when the feed is on the page.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e('Disable JS Image Loading', 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="disable_js_image_loading" id="sb_instagram_disable_js_image_loading" <?php if($disable_js_image_loading == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Check this box to have images loaded server side instead of with JS.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="sb_instagram_disable_mob_swipe"><?php _e('Disable Mobile Swipe Code', 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_disable_mob_swipe" id="sb_instagram_disable_mob_swipe" <?php if($sb_instagram_disable_mob_swipe == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Check this box if you'd like to disable jQuery mobile in the JavaScript file. This will fix issues with jQuery versions 2.x and later.", 'instagram-feed'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label><?php _e("Disable icon font", 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="sb_instagram_disable_font" id="sb_instagram_disable_font" <?php if($sb_instagram_disable_font == true) echo 'checked="checked"' ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sbi_font_method"><?php _e("Icon Method", 'instagram-feed'); ?></label></th>
                    <td>
                        <select name="sbi_font_method" id="sbi_font_method" class="default-text">
                            <option value="svg" id="sbi-font_method" class="default-text" <?php if($sbi_font_method == 'svg') echo 'selected="selected"' ?>>SVG</option>
                            <option value="fontfile" id="sbi-font_method" class="default-text" <?php if($sbi_font_method == 'fontfile') echo 'selected="selected"' ?>><?php _e("Font File", 'instagram-feed'); ?></option>
                        </select>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This plugin uses SVGs for all icons in the feed. Use this setting to switch to font icons.", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="sbi_br_adjust"><?php _e("Caption Line-Break Limit", 'instagram-feed'); ?></label></th>
                    <td>
                        <input type="checkbox" name="sbi_br_adjust" id="sbi_br_adjust" <?php if($sbi_br_adjust == true) echo 'checked="checked"' ?> />
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e( "Character Limits for captions are adjusted for use of new line. Disable this setting to always use the true character limit.", 'instagram-feed' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Enable Mediavine integration", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_media_vine" type="checkbox" id="sb_instagram_media_vine" <?php if($sb_instagram_media_vine == true) echo "checked"; ?> />
                        <label for="ssb_instagram_media_vine"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Enable this setting to automatically place ads if you are using Mediavine ad management", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Enable Custom Templates", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_custom_template" type="checkbox" id="sb_instagram_custom_template" <?php if($sb_instagram_custom_template == true) echo "checked"; ?> />
                        <label for="ssb_instagram_custom_template"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                       
                    </td>
                </tr>
                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Disable Admin Error Notice", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_disable_admin_notice" type="checkbox" id="sb_instagram_disable_admin_notice" <?php if($sb_instagram_disable_admin_notice == true) echo "checked"; ?> />
                        <label for="sb_instagram_disable_admin_notice"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("This will permanently disable the feed error notice that displays in the bottom right corner for admins on the front end of your site.", 'instagram-feed'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th class="bump-left"><label class="bump-left"><?php _e("Feed Issue Email Report", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sb_instagram_enable_email_report" type="checkbox" id="sb_instagram_enable_email_report" <?php if($sb_instagram_enable_email_report == 'on') echo "checked"; ?> />
                        <label for="sb_instagram_enable_email_report"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a><br />
                        <p class="sbi_tooltip"><?php _e("Instagram Feed will send a weekly notification email using your site's wp_mail() function if one or more of your feeds is not updating or is not displaying. If you're not receiving the emails in your inbox, you may need to configure an SMTP service using another plugin like WP Mail SMTP.", 'instagram-feed'); ?></p>

                        <div class="sb_instagram_box" style="display: block;">
                            <div class="sb_instagram_box_setting">
                            <label><?php _e('Schedule Weekly on', 'instagram-feed'); ?></label><br>
	                        <?php
	                        $schedule_options = array(
		                        array(
			                        'val' => 'monday',
			                        'label' => __( 'Monday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'tuesday',
			                        'label' => __( 'Tuesday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'wednesday',
			                        'label' => __( 'Wednesday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'thursday',
			                        'label' => __( 'Thursday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'friday',
			                        'label' => __( 'Friday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'saturday',
			                        'label' => __( 'Saturday', 'instagram-feed' )
		                        ),
		                        array(
			                        'val' => 'sunday',
			                        'label' => __( 'Sunday', 'instagram-feed' )
		                        ),
	                        );

	                        if ( isset( $_GET['flag'] ) ){
	                            echo '<span id="sbi-goto"></span>';
                            }
	                        ?>
                            <select name="sb_instagram_email_notification" id="sb_instagram_email_notification">
		                        <?php foreach ( $schedule_options as $schedule_option ) : ?>
                                    <option value="<?php echo esc_attr( $schedule_option['val'] ) ; ?>" <?php if ( $schedule_option['val'] === $sb_instagram_email_notification ) { echo 'selected';} ?>><?php echo esc_html( $schedule_option['label'] ) ; ?></option>
		                        <?php endforeach; ?>
                            </select>
                            </div>
                            <div class="sb_instagram_box_setting">
                                <label><?php _e('Email Recipients', 'instagram-feed'); ?></label><br><input class="regular-text" type="text" name="sb_instagram_email_notification_addresses" value="<?php echo esc_attr( $sb_instagram_email_notification_addresses ); ?>"><span class="sbi_note"><?php _e('separate multiple emails with commas', 'instagram-feed'); ?></span>
                                <br><br><?php _e( 'Emails not working?', 'instagram-feed' ) ?> <a href="#"><?php _e( 'See our related FAQ', 'instagram-feed' ) ?></a>
                            </div>
                        </div>

                    </td>
                </tr>

                <tr>
                    <?php
                    $usage_tracking = get_option( 'sbi_usage_tracking', array( 'last_send' => 0, 'enabled' => sbi_is_pro_version() ) );

                    if ( isset( $_POST['sb_instagram_enable_email_report'] ) ) {
	                    $usage_tracking['enabled'] = false;
                        if ( isset( $_POST['sbi_usage_tracking_enable'] ) ) {
	                        $usage_tracking['enabled'] = true;
                        }
                        update_option( 'sbi_usage_tracking', $usage_tracking, false );
                    }
                    $sbi_usage_tracking_enable = isset( $usage_tracking['enabled'] ) ? $usage_tracking['enabled'] : true;
                    ?>
                    <th class="bump-left"><label class="bump-left"><?php _e("Enable Usage Tracking", 'instagram-feed'); ?></label></th>
                    <td>
                        <input name="sbi_usage_tracking_enable" type="checkbox" id="sbi_usage_tracking_enable" <?php if( $sbi_usage_tracking_enable ) echo "checked"; ?> />
                        <label for="sbi_usage_tracking_enable"><?php _e('Yes', 'instagram-feed'); ?></label>
                        <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What is usage tracking?', 'instagram-feed'); ?></a>
                        <p class="sbi_tooltip"><?php _e("Understanding how you are using the plugin allows us to further improve it. The plugin will send a report in the background once per week which includes information about your plugin settings and statistics about your site, which we can use to help improve the features which matter most to you and improve your experience using the plugin. The plugin will never collect any sensitive information like access tokens, email addresses, or user information, and sending this data won't slow down your site at all. For more information,", 'instagram-feed'); ?> <a href="#"><?php _e("see here", 'instagram-feed'); ?></a>.</p>
                    </td>
                </tr>

			    <?php if ( is_multisite() && current_user_can( 'manage_network' ) ) : ?>
                    <tr>
                        <th scope="row"><label for="sbi_super_admin_only"><?php _e("License Page Super Admin Only", 'instagram-feed'); ?></label></th>
                        <td>
                            <input type="checkbox" name="sbi_super_admin_only" id="sbi_super_admin_only" <?php if($sbi_super_admin_only == true) echo 'checked="checked"' ?> />
                            <a class="sbi_tooltip_link" href="JavaScript:void(0);"><?php _e('What does this mean?', 'instagram-feed'); ?></a>
                            <p class="sbi_tooltip"><?php _e( "If you are using multisite, you can restrict access to the license page to super admin's only by enabling this setting.", 'instagram-feed' ); ?></p>
                        </td>
                    </tr>
			    <?php endif; ?>

                </tbody>
            </table>

		    <?php submit_button(); ?>

	    <?php } //End Customize Advanced tab ?>

        </form>



	    <?php if( $sbi_active_tab == 'display' ) { //Start Configure tab ?>

            <h3><?php _e('Display your Feed', 'instagram-feed'); ?></h3>
            <p><?php _e("Copy and paste the following shortcode directly into the page, post or widget where you'd like the feed to show up:", 'instagram-feed'); ?></p>
            <input type="text" value="[instagram-feed]" size="16" readonly="readonly" style="text-align: center;" onclick="this.focus();this.select()" title="<?php _e('To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).', 'instagram-feed'); ?>" />

            <h3 style="padding-top: 10px;"><?php _e( 'Multiple Feeds', 'custom-twitter-feed' ); ?></h3>
            <p><?php _e("If you'd like to display multiple feeds then you can set different settings directly in the shortcode like so:", 'instagram-feed'); ?>
                <code>[instagram-feed num=9 cols=3]</code></p>
            <p>You can display as many different feeds as you like, on either the same page or on different pages, by just using the shortcode options below. For example:<br />
                <code>[instagram-feed]</code><br />
                <code>[instagram-feed num=4 cols=4 showfollow=false]</code><br />
                <code>[instagram-feed user=smashballoon]</code><br />
            </p>
            <p><?php _e("See the table below for a full list of available shortcode options:", 'instagram-feed'); ?></p>

            <table class="sbi_shortcode_table">
                <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Shortcode option', 'instagram-feed'); ?></th>
                    <th scope="row"><?php _e('Description', 'instagram-feed'); ?></th>
                    <th scope="row"><?php _e('Example', 'instagram-feed'); ?></th>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Configure Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>type</td>
                    <td><?php _e("Display photos from a connected User Account", 'instagram-feed'); ?> (user)<br /><?php _e("Display posts from a Hashtag", 'instagram-feed'); ?> (hashtag)<br />
	                    <?php _e("Display posts that have tagged a connected User Account", 'instagram-feed'); ?> (tagged)<br />
                        <?php _e("Display a mix of feed types", 'instagram-feed'); ?> (mixed)</td>
                    <td><code>[instagram-feed type=user]</code><br /><code>[instagram-feed type=hashtag]</code><br /><code>[instagram-feed type=tagged]</code><br /><code>[instagram-feed type=mixed]</code></td>
                </tr>
                <tr>
                    <td>user</td>
                    <td><?php _e('Your Instagram user name for the account. This must be a user name from one of your connected accounts.', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed type="user" user="smashballoon"]</code></td>
                </tr>
                <tr>
                    <td>hashtag</td>
                    <td><?php _e('Any hashtag. Separate multiple IDs by commas.', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed type="hashtag" hashtag="#awesome"]</code></td>
                </tr>
                <tr>
                    <td>tagged</td>
                    <td><?php _e('Your business Instagram user name for the account. This must be a user name from one of your connected business accounts.', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed type="tagged" tagged="smashballoon"]</code></td>
                </tr>
                <tr>
                    <td>order</td>
                    <td><?php _e('The order to display the Hashtag feed posts', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed order="top"]</code><br /><code>[instagram-feed order="recent"]</code></td>
                </tr>
                <tr>
                    <td>tagged</td>
                    <td><?php _e('Your Instagram user name for the account. This must be a user name from one of your connected accounts.', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed tagged="smashballoon"]</code></td>
                </tr>


                <tr class="sbi_table_header"><td colspan=3><?php _e("Customize Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>width</td>
                    <td><?php _e("The width of your feed. Any number.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed width=50]</code></td>
                </tr>
                <tr>
                    <td>widthunit</td>
                    <td><?php _e("The unit of the width. 'px' or '%'", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed widthunit=%]</code></td>
                </tr>
                <tr>
                    <td>height</td>
                    <td><?php _e("The height of your feed. Any number.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed height=250]</code></td>
                </tr>
                <tr>
                    <td>heightunit</td>
                    <td><?php _e("The unit of the height. 'px' or '%'", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed heightunit=px]</code></td>
                </tr>
                <tr>
                    <td>background</td>
                    <td><?php _e("The background color of the feed. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed background=#ffff00]</code></td>
                </tr>


                <tr class="sbi_table_header"><td colspan=3><?php _e("Layout Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>layout</td>
                    <td><?php _e("How posts are arranged visually in the feed.", 'instagram-feed' ); ?> 'grid', 'carousel', 'masonry', or 'highlight'</td>
                    <td><code>[instagram-feed layout=grid]</code></td>
                </tr>
                <tr>
                    <td>num</td>
                    <td><?php _e("The number of photos to display initially. Maximum is 33.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed num=10]</code></td>
                </tr>
                <tr>
                    <td>nummobile</td>
                    <td><?php _e("The number of photos to display initially for mobile screens (smaller than 480 pixels).", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed nummobile=6]</code></td>
                </tr>
                <tr>
                    <td>cols</td>
                    <td><?php _e("The number of columns in your feed. 1 - 10.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed cols=5]</code></td>
                </tr>
                <tr>
                    <td>colsmobile</td>
                    <td><?php _e("The number of columns in your feed for mobile screens (smaller than 480 pixels).", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed colsmobile=2]</code></td>
                </tr>
                <tr>
                    <td>imagepadding</td>
                    <td><?php _e("The spacing around your photos", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed imagepadding=10]</code></td>
                </tr>
                <tr>
                    <td>imagepaddingunit</td>
                    <td><?php _e("The unit of the padding. 'px' or '%'", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed imagepaddingunit=px]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Carousel Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>carouselrows</td>
                    <td><?php _e("Choose 1 or 2 rows of posts in the carousel", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouselrows=1]</code></td>
                </tr>
                <tr>
                    <td>carouselloop</td>
                    <td><?php _e("Infinitely loop through posts or rewind", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouselloop=rewind]</code></td>
                </tr>
                <tr>
                    <td>carouselarrows</td>
                    <td><?php _e("Display directional arrows on the carousel", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouselarrows=true]</code></td>
                </tr>
                <tr>
                    <td>carouselpag</td>
                    <td><?php _e("Display pagination links below the carousel", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouselpag=true]</code></td>
                </tr>
                <tr>
                    <td>carouselautoplay</td>
                    <td><?php _e("Make the carousel autoplay", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouselautoplay=true]</code></td>
                </tr>
                <tr>
                    <td>carouseltime</td>
                    <td><?php _e("The interval time between slides for autoplay. Time in miliseconds.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed carouseltime=8000]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Highlight Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>highlighttype</td>
                    <td><?php _e("Choose from 3 different ways of highlighting posts.", 'instagram-feed'); ?> 'pattern', 'hashtag', 'id'.</td>
                    <td><code>[instagram-feed highlighttype=hashtag]</code></td>
                </tr>
                <tr>
                    <td>highlightpattern</td>
                    <td><?php _e("How often a post is highlighted.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed highlightpattern=7]</code></td>
                </tr>
                <tr>
                    <td>highlightoffset</td>
                    <td><?php _e("When to start the highlight pattern.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed highlightoffset=3]</code></td>
                </tr>
                <tr>
                    <td>highlighthashtag</td>
                    <td><?php _e("Highlight posts with these hashtags.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed highlighthashtag=best]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Photos Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>sortby</td>
                    <td><?php _e("Sort the posts by Newest to Oldest (none) Random (random) or Likes (likes)", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed sortby=random]</code></td>
                </tr>
                <tr>
                    <td>imageres</td>
                    <td><?php _e("The resolution/size of the photos.", 'instagram-feed'); ?> 'auto', full', 'medium' or 'thumb'.</td>
                    <td><code>[instagram-feed imageres=full]</code></td>
                </tr>
                <tr>
                    <td>media</td>
                    <td><?php _e("Display all media, only photos, or only videos", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed media=photos]</code></td>
                </tr>
                <tr>
                    <td>disablelightbox</td>
                    <td><?php _e("Whether to disable the photo Lightbox. It is enabled by default.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed disablelightbox=true]</code></td>
                </tr>
                <tr>
                    <td>captionlinks</td>
                    <td><?php _e("Whether to use urls in captions for the photo's link instead of linking to instagram.com.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed captionlinks=true]</code></td>
                </tr>
                <tr>
                    <td>offset</td>
                    <td><?php _e("Offset which post is displayed first in the feed.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed offset=1]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Lightbox Comments Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>lightboxcomments</td>
                    <td><?php _e("Whether to show comments in the lightbox for this feed.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed lightboxcomments=true]</code></td>
                </tr>
                <tr>
                    <td>numcomments</td>
                    <td><?php _e("Number of comments to show starting from the most recent.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed numcomments=10]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Photos Hover Style Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>hovercolor</td>
                    <td><?php _e("The background color when hovering over a photo. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed hovercolor=#ff0000]</code></td>
                </tr>
                <tr>
                    <td>hovertextcolor</td>
                    <td><?php _e("The text/icon color when hovering over a photo. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed hovertextcolor=#fff]</code></td>
                </tr>
                <tr>
                    <td>hoverdisplay</td>
                    <td><?php _e("The info to display when hovering over the photo. Available options:", 'instagram-feed'); ?><br />username, date, instagram, caption, likes</td>
                    <td><code>[instagram-feed hoverdisplay="date, likes"]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Header Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>showheader</td>
                    <td><?php _e("Whether to show the feed Header.", 'instagram-feed'); ?> 'true' or 'false'.</td>
                    <td><code>[instagram-feed showheader=false]</code></td>
                </tr>
                <tr>
                    <td>headerstyle</td>
                    <td><?php _e("Which header style to use. Choose from standard, boxed, or centered.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed headerstyle=boxed]</code></td>
                </tr>
                <tr>
                    <td>headersize</td>
                    <td><?php _e("Size of the header. Choose from small, medium, or large.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed headersize=medium]</code></td>
                </tr>
                <tr>
                    <td>headerprimarycolor</td>
                    <td><?php _e("The primary color to use for the <b>boxed</b> header. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed headerprimarycolor=#333]</code></td>
                </tr>
                <tr>
                    <td>headersecondarycolor</td>
                    <td><?php _e("The secondary color to use for the <b>boxed</b> header. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed headersecondarycolor=#ccc]</code></td>
                </tr>
                <tr>
                    <td>showfollowers</td>
                    <td><?php _e("Display the number of followers in the header", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed showfollowers=true]</code></td>
                </tr>
                <tr>
                    <td>showbio</td>
                    <td><?php _e("Display the bio in the header", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed showbio=true]</code></td>
                </tr>
                <tr>
                    <td>custombio</td>
                    <td><?php _e("Display a custom bio in the header", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed custombio="My custom bio."]</code></td>
                </tr>
                <tr>
                    <td>customavatar</td>
                    <td><?php _e("Display a custom avatar in the header. Enter the full URL of an image file.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed customavatar="https://example.com/avatar.jpg"]</code></td>
                </tr>
                <tr>
                    <td>headercolor</td>
                    <td><?php _e("The color of the Header text. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed headercolor=#333]</code></td>
                </tr>
                <tr>
                    <td>stories</td>
                    <td><?php _e("Include the user's Instagram story in the header if available.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed stories=true]</code></td>
                </tr>
                <tr>
                    <td>storiestime</td>
                    <td><?php _e("Length of time an image slide will display when viewing stories.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed storiestime=5000]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Caption Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>showcaption</td>
                    <td><?php _e("Whether to show the photo caption. 'true' or 'false'.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed showcaption=false]</code></td>
                </tr>
                <tr>
                    <td>captionlength</td>
                    <td><?php _e("The number of characters of the caption to display", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed captionlength=50]</code></td>
                </tr>
                <tr>
                    <td>captioncolor</td>
                    <td><?php _e("The text color of the caption. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed captioncolor=#000]</code></td>
                </tr>
                <tr>
                    <td>captionsize</td>
                    <td><?php _e("The size of the caption text. Any number.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed captionsize=24]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Likes &amp; Comments Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>showlikes</td>
                    <td><?php _e("Whether to show the Likes &amp; Comments. 'true' or 'false'.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed showlikes=false]</code></td>
                </tr>
                <tr>
                    <td>likescolor</td>
                    <td><?php _e("The color of the Likes &amp; Comments. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed likescolor=#FF0000]</code></td>
                </tr>
                <tr>
                    <td>likessize</td>
                    <td><?php _e("The size of the Likes &amp; Comments. Any number.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed likessize=14]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("'Load More' Button Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>showbutton</td>
                    <td><?php _e("Whether to show the 'Load More' button.", 'instagram-feed'); ?> 'true' or 'false'.</td>
                    <td><code>[instagram-feed showbutton=false]</code></td>
                </tr>
                <tr>
                    <td>buttoncolor</td>
                    <td><?php _e("The background color of the button. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed buttoncolor=#000]</code></td>
                </tr>
                <tr>
                    <td>buttontextcolor</td>
                    <td><?php _e("The text color of the button. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed buttontextcolor=#fff]</code></td>
                </tr>
                <tr>
                    <td>buttontext</td>
                    <td><?php _e("The text used for the button.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed buttontext="Load More Photos"]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("'Follow' Button Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>showfollow</td>
                    <td><?php _e("Whether to show the Instagram 'Follow' button.", 'instagram-feed'); ?> 'true' or 'false'.</td>
                    <td><code>[instagram-feed showfollow=true]</code></td>
                </tr>
                <tr>
                    <td>followcolor</td>
                    <td><?php _e("The background color of the button. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed followcolor=#ff0000]</code></td>
                </tr>
                <tr>
                    <td>followtextcolor</td>
                    <td><?php _e("The text color of the button. Any hex color code.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed followtextcolor=#fff]</code></td>
                </tr>
                <tr>
                    <td>followtext</td>
                    <td><?php _e("The text used for the button.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed followtext="Follow me"]</code></td>
                </tr>
                <tr class="sbi_table_header"><td colspan=3><?php _e("Auto Load More on Scroll", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>autoscroll</td>
                    <td><?php _e("Load more posts automatically as the user scrolls down the page.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed autoscroll=true]</code></td>
                </tr>
                <tr>
                    <td>autoscrolldistance</td>
                    <td><?php _e("Distance before the end of feed or page that triggers the loading of more posts.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed autoscrolldistance=200]</code></td>
                </tr>
                <tr class="sbi_table_header"><td colspan=3><?php _e("Post Filtering Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>excludewords</td>
                    <td><?php _e("Remove posts which contain certain words or hashtags in the caption.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed excludewords="bad, words"]</code></td>
                </tr>
                <tr>
                    <td>includewords</td>
                    <td><?php _e("Only display posts which contain certain words or hashtags in the caption.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed includewords="sunshine"]</code></td>
                </tr>
                <!--<tr>
                    <td>showusers</td>
                    <td><?php _e("Only display posts from this user. Separate multiple users with a comma", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed showusers="smashballoon,taylorswift"]</code></td>
                </tr>-->
                <tr>
                    <td>whitelist</td>
                    <td><?php _e("Only display posts that match one of the post ids in this \"whitelist\"", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed whitelist="2"]</code></td>
                </tr>

                <tr class="sbi_table_header"><td colspan=3><?php _e("Misc Options", 'instagram-feed'); ?></td></tr>
                <tr>
                    <td>permanent</td>
                    <td><?php _e("Feed will never look for new posts from Instagram.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed permanent="true"]</code></td>
                </tr>
                <tr>
                    <td>maxrequests</td>
                    <td><?php _e("Change the number of maximum concurrent API requests.", 'instagram-feed'); ?><br /><?php _e("This is not recommended unless directed by a member of the support team.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed maxrequests="2"]</code></td>
                </tr>
                <tr>
                    <td>customtemplate</td>
                    <td><?php _e("Whether or not the plugin should look in your theme for a custom template.", 'instagram-feed'); ?><br /><?php _e("Do not enable unless there are templates added to your theme's folder.", 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed customtemplate="true"]</code></td>
                </tr>
                <tr>
                    <td>accesstoken</td>
                    <td><?php _e('A Valid Instagram Access Token (personal accounts only). Separate multiple using commas. This is only necessary if you do not have the account connected on the "Configure" tab.', 'instagram-feed'); ?></td>
                    <td><code>[instagram-feed accesstoken="XXXXXXXXXX"]</code></td>
                </tr>

                </tbody>
            </table>

	    <?php } //End Display tab ?>


	    <?php if( $sbi_active_tab == 'support' ) { //Start Support tab ?>
            <div class="sbi_support">

                <br />
                <h3 style="padding-bottom: 10px;">Need help?</h3>

                <p>
                    <span class="sbi-support-title"><i class="fa fa-life-ring" aria-hidden="true"></i>&nbsp; <a href="#"><?php _e('Setup Directions', 'instagram-feed'); ?></a></span>
				    <?php _e('A step-by-step guide on how to setup and use the plugin.', 'instagram-feed'); ?>
                </p>

                <p>
                    <span class="sbi-support-title"><i class="fa fa-youtube-play" aria-hidden="true"></i>&nbsp; <a href="https://www.youtube.com/embed/q6ZXVU4g970" target="_blank" id="sbi-play-support-video"><?php _e('Watch a Video', 'instagram-feed'); ?></a></span>
				    <?php _e('How to setup, use, and customize the plugin.', 'instagram-feed'); ?>

                    <iframe id="sbi-support-video" src="//www.youtube.com/embed/q6ZXVU4g970?theme=light&amp;showinfo=0&amp;controls=2&amp;rel=0" width="960" height="540" frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay; encrypted-media"></iframe>
                </p>


            </div>

            <hr />

            <h3><?php _e('System Info', 'instagram-feed'); ?> &nbsp; <i style="color: #666; font-size: 11px; font-weight: normal;"><?php _e( 'Click the text below to select all', 'instagram-feed' ); ?></i></h3>


		    <?php $sbi_options = get_option('sb_instagram_settings'); ?>
            <textarea readonly="readonly" onclick="this.focus();this.select()" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)." style="width: 100%; max-width: 960px; height: 500px; white-space: pre; font-family: Menlo,Monaco,monospace;">
## SITE/SERVER INFO: ##
Site URL:                 <?php echo site_url() . "\n"; ?>
Home URL:                 <?php echo home_url() . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

## ACTIVE PLUGINS: ##
<?php
global $wpdb;
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}
?>

## PLUGIN SETTINGS: ##
sb_instagram_license => <?php echo get_option( 'sbi_license_key' ) . "\n"; ?>
sb_instagram_license_type => <?php echo SBI_PLUGIN_NAME . "\n"; ?>
<?php
foreach( $sbi_options as $key => $val ) {
    if ( $key !== 'connected_accounts' ) {
        if ( is_array( $val ) ) {
            foreach ( $val as $item ) {
                if ( is_array( $item ) ) {
                    foreach ( $item as $key2 => $val2 ) {
                        echo "$key2 => $val2\n";
                    }
                } else {
                    echo "$key => $item\n";
                }
            }
        } else {
            echo "$key => $val\n";
        }
    }

}
?>

## CONNECTED ACCOUNTS: ##<?php echo "\n";
$con_accounts = isset( $sbi_options['connected_accounts'] ) ? $sbi_options['connected_accounts'] : array();
$business_accounts = array();
$basic_accounts = array();
if ( ! empty( $con_accounts ) ) {
	foreach ( $con_accounts as $account ) {
		$type = isset( $account['type'] ) ? $account['type'] : 'personal';

		if ( $type === 'business' ) {
			$business_accounts[] = $account;
		} elseif ( $type === 'basic' ) {
			$basic_accounts[] = $account;
		}
		echo '*' . $account['user_id'] . '*' . "\n";
		var_export( $account );
		echo "\n";
	}
}
?>

## API RESPONSE: ##
<?php
$first_con_basic_account = isset( $basic_accounts[0] ) ? $basic_accounts[0] : array();
$first_con_business_account = isset( $business_accounts[0] ) ? $business_accounts[0] : array();

if ( ! empty( $first_con_basic_account ) ) {
	echo '*BASIC ACCOUNT*';
	echo "\n";
	$connection = new SB_Instagram_API_Connect( $first_con_basic_account, 'header' );
	$connection->connect();
	if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
		foreach ( $connection->get_data() as $key => $item ) {
			if ( is_array ( $item ) ) {
				foreach ( $item as $key2 => $item2 ) {
					echo $key2 . ' => ' . esc_html( $item2 ) . "\n";
				}
			} else {
				echo $key . ' => ' . esc_html( $item ) . "\n";
			}
		}
	} else {
		if ( $connection->is_wp_error() ) {
			$response = $connection->get_wp_error();
			if ( isset( $response ) && isset( $response->errors ) ) {
				foreach ( $response->errors as $key => $item ) {
					echo $key . ' => ' . $item[0] . "\n";
				}
			}
		} else {
			$error = $connection->get_data();
			var_export( $error );
		}
	}
	echo "\n";
} else {
	echo 'no connected basic accounts';
	echo "\n";
}
if ( ! empty( $first_con_business_account ) ) {
	echo '*BUSINESS ACCOUNT*';
	echo "\n";
	$connection = new SB_Instagram_API_Connect( $first_con_business_account, 'header' );
	$connection->connect();
	if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
		foreach ( $connection->get_data() as $key => $item ) {
			if ( is_array ( $item ) ) {
				foreach ( $item as $key2 => $item2 ) {
					echo $key2 . ' => ' . esc_html( $item2 ) . "\n";
				}
			} else {
				echo $key . ' => ' . esc_html( $item ) . "\n";
			}
		}
		$connection = new SB_Instagram_API_Connect_Pro( $first_con_business_account, 'recently_searched_hashtags', array( 'hashtag' => '' ) );
		$connection->connect();

		$recently_searched_data = !$connection->is_wp_error() ? $connection->get_data() : false;
		$num_hashatags_searched = $recently_searched_data && isset( $recently_searched_data ) && ! isset( $recently_searched_data['data'] ) && is_array( $recently_searched_data ) ? count( $recently_searched_data ) : 0;
		echo '*Recently Searched Hashtags*' . ' => ' . esc_html( $num_hashatags_searched ) . "\n";
	} else {
		if ( $connection->is_wp_error() ) {
			$response = $connection->get_wp_error();
			if ( isset( $response ) && isset( $response->errors ) ) {
				foreach ( $response->errors as $key => $item ) {
					echo $key . ' => ' . $item[0] . "\n";
				}
			}
		} else {
			$error = $connection->get_data();
			var_export( $error );
		}
	}
} else {
	echo 'no connected business accounts';
}
?>

## LISTS AND CACHES: ##
<?php
$sbi_current_white_names = get_option( 'sb_instagram_white_list_names', array() );

if( empty( $sbi_current_white_names ) ){
	_e("No white lists currently created", 'instagram-feed');
} else {
	$sbi_white_size = count( $sbi_current_white_names );
	$sbi_i = 1;
	echo 'IDs: ';
	foreach ( $sbi_current_white_names as $white ) {
		if( $sbi_i !== $sbi_white_size ) {
			echo $white.', ';
		} else {
			echo $white;
		}
		$sbi_i++;
	}
}
echo "\n";

if ( isset( $sbi_current_white_names[0] ) ) {
	$sb_instagram_white_lists = get_option( 'sb_instagram_white_lists_'.$sbi_current_white_names[0] , '' );
	$sb_instagram_white_list_ids = ! empty( $sb_instagram_white_lists ) ? implode( ', ', $sb_instagram_white_lists ) : '';
	echo 'White list ' . $sbi_current_white_names[0] . ': ' .$sb_instagram_white_list_ids . "\n";
}

 ?>

## Cron Events: ##
<?php
$cron = _get_cron_array();
foreach ( $cron as $key => $data ) {
	$is_target = false;
	foreach ( $data as $key2 => $val ) {
		if ( strpos( $key2, 'sbi' ) !== false || strpos( $key2, 'sb_instagram' ) !== false ) {
			$is_target = true;
			echo $key2;
			echo "\n";
		}
	}
	if ( $is_target) {
		echo date( "Y-m-d H:i:s", $key );
		echo "\n";
		echo 'Next Scheduled: ' . ((int)$key - time())/60 . ' minutes';
		echo "\n\n";
	}
}
?>
## Cron Cache Report: ##
<?php $cron_report = get_option( 'sbi_cron_report', array() );
if ( ! empty( $cron_report ) ) {
	var_export( $cron_report );
}
echo "\n";
?>

## Access Token Refresh: ##
<?php $cron_report = get_option( 'sbi_refresh_report', array() );
if ( ! empty( $cron_report ) ) {
	var_export( $cron_report );
}
echo "\n";
?>

## Resizing: ##
<?php $upload     = wp_upload_dir();
$upload_dir = $upload['basedir'];
$upload_dir = trailingslashit( $upload_dir ) . SBI_UPLOADS_NAME;
if ( file_exists( $upload_dir ) ) {
echo 'upload directory exists';
} else {
	$created = wp_mkdir_p( $upload_dir );

	if ( ! $created ) {
echo 'cannot create upload directory';
	}
}
echo "\n";
echo "\n";

$table_name      = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE );
$feeds_posts_table_name = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS );

if ( $wpdb->get_var( "show tables like '$feeds_posts_table_name'" ) != $feeds_posts_table_name ) {
	echo 'no feeds posts table';
	echo "\n";

} else {
	$last_result = $wpdb->get_results( "SELECT * FROM $feeds_posts_table_name ORDER BY id DESC LIMIT 1;" );
	if ( is_array( $last_result ) && isset( $last_result[0] ) ) {
		echo '*FEEDS POSTS TABLE*';
		echo "\n";

		foreach ( $last_result as $column ) {

			foreach ( $column as $key => $value ) {
			    echo $key . ': ' . esc_html( $value ) . "\n";;
			}
		}

	} else {
		echo 'feeds posts has no rows';
		echo "\n";
	}
}
echo "\n";

if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {
	echo 'no posts table';
	echo "\n";

} else {


	$last_result = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1;" );
	if ( is_array( $last_result ) && isset( $last_result[0] ) ) {
		echo '*POSTS TABLE*';
		echo "\n";
		foreach ( $last_result as $column ) {

			foreach ( $column as $key => $value ) {
				echo $key . ': ' . esc_html( $value ) . "\n";;
			}
		}

	} else {
		echo 'feeds posts has no rows';
		echo "\n";
	}
}

?>

## Error Log: ##
<?php
global $sb_instagram_posts_manager;
$errors = $sb_instagram_posts_manager->get_errors();
if ( ! empty( $errors ) ) :
	foreach ( $errors as $type => $error ) :
		echo $type . ': ' . $error[1] . "\n";
	endforeach;
endif;
$error_page = $sb_instagram_posts_manager->get_error_page();
if ( $error_page ) {
	echo 'Feed with error: ' . esc_url( get_the_permalink( $error_page ) ). "\n";
}
$ajax_statuses = $sb_instagram_posts_manager->get_ajax_status();
if ( ! $ajax_statuses['successful'] ) {
    ?>
## AJAX Status ##
                <?php
	echo 'test not successful';
}
?>
        </textarea>
            <div style="margin-bottom: 20px;"><input id="sbi_reset_log" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Reset Error Log' ); ?>" style="vertical-align: middle;"/></div>
            <?php
            if ( isset( $_GET['debug'] ) && $_GET['debug'] == 'true' ) : ?>
                <div><a href="<?php echo get_admin_url( null, 'admin.php?page=sb-instagram-feed&tab=support' ); ?>"><?php esc_html_e( 'Hide Custom Table Data' ); ?></a></div>

	            <?php
                $offset = isset( $_GET['offset'] ) ? (int)$_GET['offset'] : 0;
	            $show_images = isset( $_GET['images'] ) ? (int)$_GET['images'] === 1 : false;

	            $posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		    $feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;

		    $results = $wpdb->get_results( $wpdb->prepare( "SELECT 
            p.id, p.created_on, p.instagram_id, p.images_done, p.media_id, f.feed_id, p.json_data, p.time_stamp, p.top_time_stamp, p.sizes
            FROM $posts_table_name AS p 
            LEFT JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			LIMIT %d, %d", $offset, 100 ) );
		    //
		    if ( isset( $results[0] ) ) :
            ?>
            <div class="sbi_debug_table_wrap">
                <table class="sbi_debug_table widefat striped">
                    <tr>
                        <?php foreach ( $results[0] as $col => $val ) : ?>
                        <th><?php echo esc_html( $col ); ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ( $results as $result ) : ?>
                        <tr>
                    <?php foreach ( $result as $key => $val ) : ?>
                        <?php if ( $show_images && $key === 'media_id' ) : ?>
                            <td height="50"><img width="100" src="<?php echo esc_url( sbi_get_resized_uploads_url() . $val . 'low.jpg' ); ?>" /><br><span><?php echo esc_html( $val ); ?></span></td>

	                    <?php else: ?>
                            <td height="50"><span><?php echo esc_html( $val ); ?></span></td>
                        <?php endif; ?>
                    <?php endforeach; ?>

                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php
		    endif;

		    else :
                ?>
                <div><a href="<?php echo add_query_arg( 'debug', 'true', get_admin_url( null, 'admin.php?page=sb-instagram-feed&tab=support' ) ); ?>"><?php esc_html_e( 'Show Custom Table Data' ); ?></a></div>

		    <?php
            endif;
	    } //End Support tab
	    ?>

    </div> <!-- end #sbi_admin -->

<?php } //End Settings page
