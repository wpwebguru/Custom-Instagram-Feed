<?php
/**
 * Includes functions related to actions while in the admin area.
 *
 * - All AJAX related features
 * - Enqueueing of JS and CSS files
 * - Settings link on "Plugins" page
 * - Creation of local avatar image files
 * - Connecting accounts on the "Configure" tab
 * - Displaying admin notices
 * - Clearing caches
 * - License renewal
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function sb_instagram_admin_style() {
	wp_register_style( 'sb_instagram_admin_css', SBI_PLUGIN_URL . 'css/sb-instagram-admin.css', array(), SBIVER );
	wp_enqueue_style( 'sb_instagram_font_awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
	wp_enqueue_style( 'sb_instagram_admin_css' );
	wp_enqueue_style( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'sb_instagram_admin_style' );

function sb_instagram_admin_scripts() {
	wp_enqueue_script( 'sb_instagram_admin_js', SBI_PLUGIN_URL . 'js/sb-instagram-admin.js', array(), SBIVER );
	wp_localize_script( 'sb_instagram_admin_js', 'sbiA', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'sbi_nonce' => wp_create_nonce( 'sbi_nonce' )
		)
	);
	$strings = array(
		'addon_activate'                  => esc_html__( 'Activate', 'instagram-feed' ),
		'addon_activated'                 => esc_html__( 'Activated', 'instagram-feed' ),
		'addon_active'                    => esc_html__( 'Active', 'instagram-feed' ),
		'addon_deactivate'                => esc_html__( 'Deactivate', 'instagram-feed' ),
		'addon_inactive'                  => esc_html__( 'Inactive', 'instagram-feed' ),
		'addon_install'                   => esc_html__( 'Install Addon', 'instagram-feed' ),
		'addon_error'                     => esc_html__( 'Could not install addon. Please download from wpforms.com and install manually.', 'instagram-feed' ),
		'plugin_error'                    => esc_html__( 'Could not install a plugin. Please download from WordPress.org and install manually.', 'instagram-feed' ),
		'addon_search'                    => esc_html__( 'Searching Addons', 'instagram-feed' ),
		'ajax_url'                        => admin_url( 'admin-ajax.php' ),
		'cancel'                          => esc_html__( 'Cancel', 'instagram-feed' ),
		'close'                           => esc_html__( 'Close', 'instagram-feed' ),
		'nonce'                           => wp_create_nonce( 'sbi-admin' ),
		'almost_done'                     => esc_html__( 'Almost Done', 'instagram-feed' ),
		'oops'                            => esc_html__( 'Oops!', 'instagram-feed' ),
		'ok'                              => esc_html__( 'OK', 'instagram-feed' ),
		'plugin_install_activate_btn'     => esc_html__( 'Install and Activate', 'instagram-feed' ),
		'plugin_install_activate_confirm' => esc_html__( 'needs to be installed and activated to import its forms. Would you like us to install and activate it for you?', 'instagram-feed' ),
		'plugin_activate_btn'             => esc_html__( 'Activate', 'instagram-feed' ),
	);
	$strings = apply_filters( 'sbi_admin_strings', $strings );
	wp_localize_script(
		'sb_instagram_admin_js',
		'sbi_admin',
		$strings
	);
	if( !wp_script_is('jquery-ui-draggable') ) {
		wp_enqueue_script(
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-draggable'
			)
		);
	}
	wp_enqueue_script(
		array(
			'hoverIntent',
			'wp-color-picker'
		)
	);
}
add_action( 'admin_enqueue_scripts', 'sb_instagram_admin_scripts' );

// Add a Settings link to the plugin on the Plugins page
$sbi_plugin_file = 'instagram-feed-pro/instagram-feed.php';
add_filter( "plugin_action_links_{$sbi_plugin_file}", 'sbi_add_settings_link', 10, 2 );

//modify the link by unshifting the array
function sbi_add_settings_link( $links, $file ) {
	$sbi_settings_link = '<a href="' . admin_url( 'admin.php?page=sb-instagram-feed' ) . '">' . __( 'Settings', 'instagram-feed' ) . '</a>';
	array_unshift( $links, $sbi_settings_link );

	return $links;
}

/**
 * Called via ajax to automatically save access token and access token secret
 * retrieved with the big blue button
 */
function sbi_auto_save_tokens() {
	$nonce = $_POST['sbi_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'sbi_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	wp_cache_delete ( 'alloptions', 'options' );

	$options = sbi_get_database_settings();
	$new_access_token = isset( $_POST['access_token'] ) ? sanitize_text_field( $_POST['access_token'] ) : false;
	$split_token = $new_access_token ? explode( '.', $new_access_token ) : array();
	$new_user_id = isset( $split_token[0] ) ? $split_token[0] : '';

	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();
	$test_connection_data = sbi_account_data_for_token( $new_access_token );

	$connected_accounts[ $new_user_id ] = array(
		'access_token' => sbi_get_parts( $new_access_token ),
		'user_id' => $test_connection_data['id'],
		'username' => $test_connection_data['username'],
		'is_valid' => true,
		'last_checked' => $test_connection_data['last_checked'],
		'profile_picture' => $test_connection_data['profile_picture'],
	);

	delete_transient( SBI_USE_BACKUP_PREFIX . 'sbi_'  . $new_user_id );

	if ( !$options['sb_instagram_disable_resize'] ) {
		if ( sbi_create_local_avatar( $test_connection_data['username'], $test_connection_data['profile_picture'] ) ) {
			$connected_accounts[ $new_user_id ]['local_avatar'] = true;
		}
	} else {
		$connected_accounts[ $new_user_id ]['local_avatar'] = false;
	}

	$options['connected_accounts'] = $connected_accounts;

	update_option( 'sb_instagram_settings', $options );

	echo wp_json_encode( $connected_accounts[ $new_user_id ] );

	die();
}
add_action( 'wp_ajax_sbi_auto_save_tokens', 'sbi_auto_save_tokens' );
function sbi_delete_local_avatar( $username ) {
	var_dump( 'deleting' );
	$upload = wp_upload_dir();

	$image_files = glob( trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $username . '.jpg'  ); // get all matching images
	foreach ( $image_files as $file ) { // iterate files
		if ( is_file( $file ) ) {
			unlink( $file );
		}
	}
}

function sbi_create_local_avatar( $username, $file_name ) {
	$image_editor = wp_get_image_editor( $file_name );

	if ( ! is_wp_error( $image_editor ) ) {
		$upload = wp_upload_dir();

		$full_file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $username  . '.jpg';

		$saved_image = $image_editor->save( $full_file_name );

		if ( ! $saved_image ) {
			global $sb_instagram_posts_manager;

			$sb_instagram_posts_manager->add_error( 'image_editor_save', array(
				__( 'Error saving edited image.', 'instagram-feed' ),
				$full_file_name
			) );
		} else {
			return true;
		}
	} else {
		global $sb_instagram_posts_manager;

		$message = __( 'Error editing image.', 'instagram-feed' );
		if ( isset( $image_editor ) && isset( $image_editor->errors ) ) {
			foreach ( $image_editor->errors as $key => $item ) {
				$message .= ' ' . $key . '- ' . $item[0] . ' |';
			}
		}

		$sb_instagram_posts_manager->add_error( 'image_editor', array( $file_name, $message ) );
	}
	return false;
}

function sbi_connect_basic_account( $new_account_details ) {

	$options = sbi_get_database_settings();
	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

	$accounts_to_save = array();
	$old_account_user_id = '';
	$ids_to_save = array();
	$user_ids = is_array( $options[ 'sb_instagram_user_id' ] ) ? $options[ 'sb_instagram_user_id' ] : explode( ',', str_replace( ' ', '', $options[ 'sb_instagram_user_id' ] ) );

	$profile_picture = '';

	// do not connect as a basic display account if already connected as a business account
	if ( isset( $connected_accounts[ $new_account_details['user_id'] ] )
         && isset( $connected_accounts[ $new_account_details['user_id'] ]['type'] )
        && $connected_accounts[ $new_account_details['user_id'] ]['type'] === 'business' ) {
	    return $options;
    }

	foreach ( $connected_accounts as $account ) {
	    $account_type = isset( $account['type'] ) ? $account['type'] : 'personal';
	    if ( ($account['username'] !== $new_account_details['username'])
              || $account_type === 'business' ) {
		    $accounts_to_save[ $account['user_id'] ] = $account;
        } else {
		    $old_account_user_id = $account['user_id'];
		    $profile_picture = isset( $account['profile_picture'] ) ? $account['profile_picture'] : '';
	    }
	}

	foreach ( $user_ids as $id ) {
		if ( $id === $old_account_user_id ) {
			$ids_to_save[] = $new_account_details['user_id'];
		} else {
			$ids_to_save[] = $id;
		}
	}

	$accounts_to_save[ $new_account_details['user_id'] ] = array(
		'access_token' => sbi_fixer( $new_account_details['access_token'] ),
		'user_id' => $new_account_details['user_id'],
		'username' => $new_account_details['username'],
		'is_valid' => true,
		'last_checked' => time(),
		'expires_timestamp' => $new_account_details['expires_timestamp'],
		'profile_picture' => $profile_picture,
		'account_type' => strtolower( $new_account_details['account_type'] ),
		'type' => 'basic',
	);

	if ( ! empty( $old_account_user_id ) && $old_account_user_id !== $new_account_details['user_id'] ) {
		$accounts_to_save[ $new_account_details['user_id'] ]['old_user_id'] = $old_account_user_id;

		// get last saved header data
		$fuzzy_matches = sbi_fuzzy_matching_header_data( $old_account_user_id );
		if ( ! empty( $fuzzy_matches[0] ) ) {
		    $header_data = sbi_find_matching_data_from_results( $fuzzy_matches, $old_account_user_id );
            $bio = SB_Instagram_Parse::get_bio( $header_data );
			$accounts_to_save[ $new_account_details['user_id'] ]['bio'] = sbi_sanitize_emoji( $bio );
        }

    }

	if ( ! empty( $profile_picture ) && !$options['sb_instagram_disable_resize'] ) {
		if ( sbi_create_local_avatar( $new_account_details['username'], $profile_picture ) ) {
			$accounts_to_save[ $new_account_details['user_id'] ]['local_avatar'] = true;
		}
	} else {
		$accounts_to_save[ $new_account_details['user_id'] ]['local_avatar'] = false;
	}

	delete_transient( SBI_USE_BACKUP_PREFIX . 'sbi_'  . $new_account_details['user_id'] );

	$options['connected_accounts'] = $accounts_to_save;
	$options['sb_instagram_user_id'] = $ids_to_save;

	update_option( 'sb_instagram_settings', $options );
	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_error( 'at_' . $new_account_details['username'] );
	$sb_instagram_posts_manager->remove_error( 'api' );

	return $options;
}

function sbi_fuzzy_matching_header_data( $user_id ) {

	if ( empty( $user_id ) || strlen( $user_id ) < 4 ) {
		return array();
	}
	global $wpdb;
	$escaped_id = esc_sql( $user_id );

	$values = $wpdb->get_results( "
    SELECT option_value
    FROM $wpdb->options
    WHERE option_name LIKE ('%!sbi\_header\_".$escaped_id."%')
    LIMIT 10", ARRAY_A );

	$regular_values = $wpdb->get_results( "
    SELECT option_value
    FROM $wpdb->options
    WHERE option_name LIKE ('%sbi\_header\_".$escaped_id."%')
    LIMIT 10", ARRAY_A );

	$values = array_merge( $values, $regular_values );

	return $values;
}

function sbi_find_matching_data_from_results( $results, $user_id ) {

    $match = array();

    $i = 0;

    while( empty( $match ) && isset( $results[ $i ] ) ) {
	    if ( ! empty( $results[ $i ] ) ) {
		    $header_data = json_decode( $results[ $i ]['option_value'], true );
		    if ( isset( $header_data['id'] ) && (string)$header_data['id'] === (string)$user_id ) {
			    $match = $header_data;
		    }
	    }
        $i++;
    }

    return $match;
}

function sbi_matches_existing_personal( $new_account_details ) {

	$options = sbi_get_database_settings();
	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

	$matches_one_account = false;
	$accounts_to_save = array();
	foreach ( $connected_accounts as $account ) {
	    $account_type = isset( $account['type'] ) ? $account['type'] : 'personal';
		if ( ($account_type === 'personal' || $account_type === 'basic')
            && $account['username'] == $new_account_details['username'] ) {
			$matches_one_account = true;


		}
	}

	return $matches_one_account;

}

function sbi_connect_business_accounts() {
	$nonce = $_POST['sbi_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'sbi_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	$accounts = isset( $_POST['accounts'] ) ? json_decode( stripslashes( $_POST['accounts'] ), true ) : false;
	$options = sbi_get_database_settings();
	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();
	$new_user_names = array();

	foreach ( $accounts as $account ) {
		$access_token = isset( $account['access_token'] ) ? $account['access_token'] : '';
		$page_access_token = isset( $account['page_access_token'] ) ? $account['page_access_token'] : '';
		$username = isset( $account['username'] ) ? $account['username'] : '';
		$name = isset( $account['name'] ) ? $account['name'] : '';
		$profile_picture = isset( $account['profile_picture_url'] ) ? $account['profile_picture_url'] : '';
		$user_id = isset( $account['id'] ) ? $account['id'] : '';
		$type = 'business';

		$connected_accounts[ $user_id ] = array(
			'access_token' => $access_token,
			'page_access_token' => $page_access_token,
			'user_id' => $user_id,
			'username' => $username,
			'is_valid' => true,
			'last_checked' => time(),
			'profile_picture' => $profile_picture,
			'name' => sbi_sanitize_emoji( $name ),
			'type' => $type,
			'use_tagged' => '1'
		);
		$new_user_names[] = $username;

		if ( !$options['sb_instagram_disable_resize'] ) {
			if ( sbi_create_local_avatar( $username, $profile_picture ) ) {
				$connected_accounts[ $user_id ]['local_avatar'] = true;
			}
		} else {
			$connected_accounts[ $user_id ]['local_avatar'] = false;
		}
		global $sb_instagram_posts_manager;

		$sb_instagram_posts_manager->remove_error( 'at_' . $username );
		delete_transient( SBI_USE_BACKUP_PREFIX . 'sbi_'  . $user_id );
	}

	$accounts_to_save = array();
	foreach ( $connected_accounts as $account ) {
		$account_type = isset( $account['type'] ) ? $account['type'] : 'personal';
		if ( $account_type !== 'personal' || ! in_array( $account['username'], $new_user_names, true ) ) {
			$accounts_to_save[ $account['user_id'] ] = $account;
		}
	}

	$options['connected_accounts'] = $accounts_to_save;

	update_option( 'sb_instagram_settings', $options );
	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_error( 'at_' . $username );
	$sb_instagram_posts_manager->remove_error( 'api' );

	echo wp_json_encode( $accounts_to_save );

	die();
}
add_action( 'wp_ajax_sbi_connect_business_accounts', 'sbi_connect_business_accounts' );

function sbi_auto_save_id() {
	$nonce = $_POST['sbi_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'sbi_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}
	if ( current_user_can( 'edit_posts' ) && isset( $_POST['id'] ) ) {
		$options = get_option( 'sb_instagram_settings', array() );

		$options['sb_instagram_user_id'] = array( sanitize_text_field( $_POST['id'] ) );

		update_option( 'sb_instagram_settings', $options );
	}
	die();
}
add_action( 'wp_ajax_sbi_auto_save_id', 'sbi_auto_save_id' );

function sbi_formatted_error( $response ) {
	if ( isset( $response['error'] ) ) {
		$error = '<p>' . sprintf( __( 'API error %s:', 'instagram-feed' ), $response['error']['code'] ) . ' ' . $response['error']['message'] . '</p>';
		$error .= '<p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a></p>';

		return $error;
	} else {
		$message = '<p>' . sprintf( __( 'Error connecting to %s.', 'instagram-feed' ), $response['url'] ). '</p>';
		if ( isset( $response['response'] ) && isset( $response['response']->errors ) ) {
			foreach ( $response['response']->errors as $key => $item ) {
				'<p>' .$message .= ' '.$key . ' - ' . $item[0] . '</p>';
			}
		}
		$message .= '<p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a></p>';

		return $message;
	}
}

function sbi_test_token() {
	$access_token = isset( $_POST['access_token'] ) ? trim( sanitize_text_field( $_POST['access_token'] ) ) : false;
	$account_id = isset( $_POST['account_id'] ) ? sanitize_text_field( $_POST['account_id'] ) : false;

	$return = sbi_connect_new_account( $access_token, $account_id );

	echo $return;
	die();
}
add_action( 'wp_ajax_sbi_test_token', 'sbi_test_token' );

function sbi_delete_account() {
	$nonce = $_POST['sbi_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'sbi_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}
	$account_id = isset( $_POST['account_id'] ) ? sanitize_text_field( $_POST['account_id'] ) : false;

	sbi_do_account_delete( $account_id );

	die();
}
add_action( 'wp_ajax_sbi_delete_account', 'sbi_delete_account' );

function sbi_do_account_delete( $account_id ) {
	$options = get_option( 'sb_instagram_settings', array() );
	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

	wp_cache_delete ( 'alloptions', 'options' );
	$username = $connected_accounts[ $account_id ]['username'];

	$num_times_used = 0;

	$new_con_accounts = array();
	foreach ( $connected_accounts as $connected_account ) {

		if ( $connected_account['username'] === $username ) {
			$num_times_used++;
		}

		if ( $connected_account['username'] !== '' && $account_id !== $connected_account['user_id'] && ! empty( $connected_account['user_id'] ) ) {
			$new_con_accounts[ $connected_account['user_id'] ] = $connected_account;
		}
	}

	if ( $num_times_used < 2 ) {
		sbi_delete_local_avatar( $username );
	}


	$options['connected_accounts'] = $new_con_accounts;

	update_option( 'sb_instagram_settings', $options );
	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_error( 'at_' . $username );
	$sb_instagram_posts_manager->remove_error( 'api' );
}

function sbi_account_data_for_token( $access_token ) {
	$return = array(
		'id' => false,
		'username' => false,
		'is_valid' => false,
		'last_checked' => time()
	);
	$url = 'https://api.instagram.com/v1/users/self/?access_token=' . sbi_maybe_clean( $access_token );
	$args = array(
		'timeout' => 60,
		'sslverify' => false
	);
	$result = wp_remote_get( $url, $args );

	if ( ! is_wp_error( $result ) ) {
		$data = json_decode( $result['body'] );
	} else {
		$data = array();
	}

	if ( isset( $data->data->id ) ) {
		$return['id'] = $data->data->id;
		$return['username'] = $data->data->username;
		$return['is_valid'] = true;
		$return['profile_picture'] = $data->data->profile_picture;

	} elseif ( isset( $data->error_type ) && $data->error_type === 'OAuthRateLimitException' ) {
		$return['error_message'] = 'This account\'s access token is currently over the rate limit. Try removing this access token from all feeds and wait an hour before reconnecting.';
	} else {
		$return = false;
	}

	$sbi_options = get_option( 'sb_instagram_settings', array() );
	$sbi_options['sb_instagram_at'] = '';
	update_option( 'sb_instagram_settings', $sbi_options );

	return $return;
}

function sbi_get_connected_accounts_data( $sb_instagram_at ) {
	$sbi_options = get_option( 'sb_instagram_settings' );
	$return = array();
	$return['connected_accounts'] = isset( $sbi_options['connected_accounts'] ) ? $sbi_options['connected_accounts'] : array();

	if ( ! empty( $return['connected_accounts'] ) ) {
		$return['access_token'] = '';
	} else {
		$return['access_token'] = $sb_instagram_at;
	}

	if ( ! sbi_is_after_deprecation_deadline() && empty( $connected_accounts ) && ! empty( $sb_instagram_at ) ) {
		$tokens = explode(',', $sb_instagram_at );
		$user_ids = array();

		foreach ( $tokens as $token ) {
			$account = sbi_account_data_for_token( $token );
			if ( isset( $account['is_valid'] ) ) {
				$split = explode( '.', $token );
				$return['connected_accounts'][ $split[0] ] = array(
					'access_token' => sbi_get_parts( $token ),
					'user_id' => $split[0],
					'username' => '',
					'is_valid' => true,
					'last_checked' => time(),
					'profile_picture' => ''
				);
				$user_ids[] = $split[0];
			}

		}

		$sbi_options['connected_accounts'] = $return['connected_accounts'];
		$sbi_options['sb_instagram_at'] = '';
		$sbi_options['sb_instagram_user_id'] = $user_ids;

		$return['user_ids'] = $user_ids;

		update_option( 'sb_instagram_settings', $sbi_options );
	}

	return $return;
}

function sbi_business_account_request( $url, $account, $remove_access_token = true ) {
	$args = array(
		'timeout' => 60,
		'sslverify' => false
	);
	$result = wp_remote_get( $url, $args );

	if ( ! is_wp_error( $result ) ) {
		$response_no_at = $remove_access_token ? str_replace( sbi_maybe_clean( $account['access_token'] ), '{accesstoken}', $result['body'] ) : $result['body'];
		return $response_no_at;
	} else {
		return wp_json_encode( $result );
	}
}

function sbi_after_connection() {

	if ( isset( $_POST['access_token'] ) ) {
		$access_token = sanitize_text_field( $_POST['access_token'] );
		$account_info = 	sbi_account_data_for_token( $access_token );
		echo json_encode( $account_info );
	}

	die();
}
add_action( 'wp_ajax_sbi_after_connection', 'sbi_after_connection' );

function sbi_clear_backups() {
	$nonce = isset( $_POST['sbi_nonce'] ) ? sanitize_text_field( $_POST['sbi_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'sbi_nonce' ) ) {
		die ( 'You did not do this the right way!' );
	}

	//Delete all transients
	global $wpdb;
	$table_name = $wpdb->prefix . "options";
	$wpdb->query( "
    DELETE
    FROM $table_name
    WHERE `option_name` LIKE ('%!sbi\_%')
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

	die();
}
add_action( 'wp_ajax_sbi_clear_backups', 'sbi_clear_backups' );

function sbi_clear_comment_cache() {

	if ( delete_transient( 'sbinst_comment_cache' ) ) {
		return true;
	} elseif ( ! get_transient( 'sbinst_comment_cache' ) ) {
		return true;
	}

	die();
}
add_action( 'wp_ajax_sbi_clear_comment_cache', 'sbi_clear_comment_cache' );

function sbi_reset_resized() {

	global $sb_instagram_posts_manager;
	$sb_instagram_posts_manager->delete_all_sbi_instagram_posts();
	delete_option( 'sbi_top_api_calls' );

	echo "1";

	die();
}
add_action( 'wp_ajax_sbi_reset_resized', 'sbi_reset_resized' );

function sbi_reset_log() {
	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_all_errors();

	echo "1";

	die();
}
add_action( 'wp_ajax_sbi_reset_log', 'sbi_reset_log' );

function sbi_clear_white_lists() {
	global $wpdb;
	$table_name = $wpdb->prefix . "options";
	$result = $wpdb->query("
    DELETE
    FROM $table_name
    WHERE `option_name` LIKE ('%sb_instagram_white_lists_%')
    ");
	$result = $wpdb->query("
    DELETE
    FROM $table_name
    WHERE `option_name` LIKE ('%sb_wlupdated_%')
    ");
	delete_option( 'sb_instagram_white_list_names' );
	delete_option( 'sb_permanent_white_lists' );
	return $result;

	die();
}
add_action( 'wp_ajax_sbi_clear_white_lists', 'sbi_clear_white_lists' );

function sbi_disable_permanent_white_lists() {
	delete_option( 'sb_permanent_white_lists' );
	die();
}
add_action( 'wp_ajax_sbi_disable_permanent_white_lists', 'sbi_disable_permanent_white_lists' );

add_action('admin_notices', 'sbi_admin_error_notices');
function sbi_admin_error_notices() {


	if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'sb-instagram-feed' )) ) {
		global $sb_instagram_posts_manager;

		$errors = $sb_instagram_posts_manager->get_errors();
		if ( ! empty( $errors ) && ( isset( $errors['database_create_posts'] ) || isset( $errors['database_create_posts_feeds'] ) || isset( $errors['upload_dir'] ) || isset( $errors['ajax'] )  ) ) : ?>
            <div class="notice notice-warning is-dismissible sbi-admin-notice">

				<?php foreach ( $sb_instagram_posts_manager->get_errors() as $type => $error ) : ?>
					<?php if ( (in_array( $type, array( 'database_create_posts', 'database_create_posts_feeds', 'upload_dir' ) ) && !$sb_instagram_posts_manager->image_resizing_disabled() ) ) : ?>
                        <p><strong><?php echo $error[0]; ?></strong></p>
                        <p><?php _e( 'Note for support', 'instagram-feed' ); ?>: <?php echo $error[1]; ?></p>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ( ( isset( $errors['database_create_posts'] ) || isset( $errors['database_create_posts_feeds'] ) || isset( $errors['upload_dir'] ) ) && !$sb_instagram_posts_manager->image_resizing_disabled() ) : ?>
                    <p><?php _e( sprintf( 'Visit our %s page for help', '<a href="https://smashballoon.com/instagram-feed/support/faq/" target="_blank">FAQ</a>' ), 'instagram-feed' ); ?></p>
				<?php endif; ?>

				<?php foreach ( $sb_instagram_posts_manager->get_errors() as $type => $error ) : ?>
					<?php if (in_array( $type, array( 'ajax' ) )) : ?>
                        <p class="sbi-admin-error" data-sbi-type="ajax"><strong><?php echo $error[0]; ?></strong></p>
                        <p><?php echo $error[1]; ?></p>
					<?php endif; ?>
				<?php endforeach; ?>

            </div>

		<?php endif;
		$errors = $sb_instagram_posts_manager->get_critical_errors();

		if ( $sb_instagram_posts_manager->are_critical_errors() && ! empty( $errors ) ) : ?>
            <div class="notice notice-warning is-dismissible sbi-admin-notice">
                <p><strong><?php echo esc_html__( 'Instagram Feed is encountering an error and your feeds may not be updating due to the following reasons:', 'instagram-feed') ; ?></strong></p>

				<?php echo $errors; ?>

				<?php
				$error_page = $sb_instagram_posts_manager->get_error_page();
				if ( $error_page ) {
					echo '<a href="' . get_the_permalink( $error_page ) . '" class="sbi-clear-errors-visit-page sbi-space-left button button-secondary">' . __( 'View Feed and Retry', 'instagram-feed' ) . '</a>';
				}
				?>
            </div>
		<?php endif;
	}

}

function sbi_get_user_names_of_personal_accounts_not_also_already_updated() {
	$sbi_options = get_option( 'sb_instagram_settings', array() );
	$users_in_personal_accounts = array();
	$non_personal_account_users = array();

	$connected_accounts = isset( $sbi_options['connected_accounts'] ) ? $sbi_options['connected_accounts'] : array();

	if ( ! empty( $connected_accounts ) ) {

		foreach ( $connected_accounts as $account ) {
			$account_type = isset( $account['type'] ) ? $account['type'] : 'personal';

			if ( $account_type === 'personal' ) {
				$users_in_personal_accounts[] = $account['username'];
			} else {
				$non_personal_account_users[] = $account['username'];
			}

		}

		if ( ! empty( $users_in_personal_accounts ) ) {
			$user_accounts_that_need_updating = array();
			foreach ( $users_in_personal_accounts as $personal_user ) {
				if ( ! in_array( $personal_user, $non_personal_account_users, true ) && $personal_user !== '' ) {
					$user_accounts_that_need_updating[] = $personal_user;
				}
			}

			return $user_accounts_that_need_updating;
		}
	} elseif ( empty( $connected_accounts ) && ! empty( $sbi_options['sb_instagram_at'] ) ) {
		return array( 'your Instagram feed');
	}

	return array();
}

function sbi_reconnect_accounts_notice() {
	if( ! current_user_can( 'manage_options' ) ) return;

	$should_show_link = ! isset( $_GET['page'] ) || $_GET['page'] !== 'sb-instagram-feed';
	$personal_accounts_that_need_updating = sbi_get_user_names_of_personal_accounts_not_also_already_updated();
	if ( empty( $personal_accounts_that_need_updating ) ) {
	    return;
    } else {
		$total = count( $personal_accounts_that_need_updating );
		if ( $total > 1 ) {
			$user_string = '';
			$i = 0;

			foreach ( $personal_accounts_that_need_updating as $username ) {
				if ( ($i + 1) === $total ) {
					$user_string .= ' and ' . $username;
				} else {
					if ( $i !== 0 ) {
						$user_string .= ', ' . $username;
					} else {
						$user_string .= $username;
					}
				}
				$i++;
			}
		} else {
			$user_string = $personal_accounts_that_need_updating[0];
		}

		if ( sbi_is_after_deprecation_deadline() ) {
			$notice_class = 'notice-error';
			$error = '<p><b>' . sprintf( __( 'Error: Instagram Feed plugin - account for %s needs to be reconnected.', 'instagram-feed' ), '<em>'.$user_string.'</em>' ) . '</b><br>' . __( 'Due to recent Instagram platform changes some Instagram accounts will need to be reconnected in the plugin in order for them to continue updating.', 'instagram-feed' );
		} else {
			$notice_class = 'notice-warning';
			$error = '<p><b>' . sprintf( __( 'Warning: Instagram Feed plugin - account for %s needs to be reconnected.', 'instagram-feed' ), '<em>'.$user_string.'</em>' ) . '</b><br>' . __( 'Due to Instagram platform changes on June 1, 2020, some Instagram accounts will need to be reconnected in the plugin to avoid disruption to your feeds.', 'instagram-feed' );
		}
		if( !$should_show_link ) $error .= __( ' Use the big blue button below to reconnect your account.', 'instagram-feed' );
	}
    $url = admin_url( '?page=sb-instagram-feed' );

?>
    <div class="notice <?php echo $notice_class; ?> is-dismissible">
	    <?php echo $error; ?>
        <p>
        <?php if ( $should_show_link ) : ?>
        <a href="<?php echo $url; ?>" class="button-primary" style="margin-right:10px;"><i class="fa fa-instagram" aria-hidden="true"></i> &nbsp;Reconnect on Settings Page</a>
        <?php endif; ?>
        <a href="https://smashballoon.com/instagram-api-changes-march-2-2020/" target="_blank" rel="noopener">See more details</a>
        </p>
    </div>

<?php

}
add_action( 'admin_notices', 'sbi_reconnect_accounts_notice' );

function sbi_account_type_display( $type ) {
    if ( $type === 'basic' ) {
        return 'personal (new API)';
    }
    return $type;
}

function sbi_expiration_notice(){
	//Only display notice to admins
	if( !current_user_can( 'manage_options' ) ) return;

	//If the user is re-checking the license key then use the API below to recheck it
	( isset( $_GET['sbichecklicense'] ) ) ? $sbi_check_license = true : $sbi_check_license = false;

	$sbi_license = trim( get_option( 'sbi_license_key' ) );

	//If there's no license key then don't do anything
	if( empty($sbi_license) || !isset($sbi_license) && !$sbi_check_license ) return;

	//Is there already license data in the db?
	if( get_option( 'sbi_license_data' ) && !$sbi_check_license ){
		//Yes
		//Get license data from the db and convert the object to an array
		$sbi_license_data = (array) get_option( 'sbi_license_data' );
	} else {
		//No
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

		//Store license data in db
		update_option( 'sbi_license_data', $sbi_license_data );
	}

	//Number of days until license expires
	$sbi_date1 = isset( $sbi_license_data['expires'] ) ? $sbi_license_data['expires'] : $sbi_date1 = '2036-12-31 23:59:59'; //If expires param isn't set yet then set it to be a date to avoid PHP notice
	if( $sbi_date1 == 'lifetime' ) $sbi_date1 = '2036-12-31 23:59:59';
	$sbi_date2 = date('Y-m-d');
	$sbi_interval = round(abs(strtotime($sbi_date2)-strtotime($sbi_date1))/86400);

	//Is license expired?
	( $sbi_interval == 0 || strtotime($sbi_date1) < strtotime($sbi_date2) ) ? $sbi_license_expired = true : $sbi_license_expired = false;

	//If expired date is returned as 1970 (or any other 20th century year) then it means that the correct expired date was not returned and so don't show the renewal notice
	if( $sbi_date1[0] == '1' ) $sbi_license_expired = false;

	//If there's no expired date then don't show the expired notification
	if( empty($sbi_date1) || !isset($sbi_date1) ) $sbi_license_expired = false;

	//Is license missing - ie. on very first check
	if( isset($sbi_license_data['error']) ){
		if( $sbi_license_data['error'] == 'missing' ) $sbi_license_expired = false;
	}

	//If license expires in less than 30 days and it isn't currently expired then show the expire countdown instead of the expiration notice
	if($sbi_interval < 30 && !$sbi_license_expired){
		$sbi_expire_countdown = true;
	} else {
		$sbi_expire_countdown = false;
	}

	global $sbi_download_id;

	//Is the license expired?
	if( ($sbi_license_expired || $sbi_expire_countdown) || $sbi_check_license ) {

		//If they've already dismissed the countdown notice then don't show it here
		global $current_user;
		$user_id = $current_user->ID;
		if( $sbi_expire_countdown && get_user_meta($user_id, 'sbi_ignore_notice') ) return;


		$sbi_license_activation_error = false;
		if( $sbi_license_data["success"] == false ) $sbi_license_activation_error = true;

		//If expire countdown then add the countdown class to the notice box
		if($sbi_expire_countdown){
			$sbi_expired_box_classes = "sbi-license-expired sbi-license-countdown";
			$sbi_expired_box_msg = "<b>Important:</b> Your Instagram Feed Pro license key expires in " . $sbi_interval . " days.";
		} else if( $sbi_license_activation_error ){
			$sbi_expired_box_classes = "sbi-license-expired";
			$sbi_expired_box_msg = "<b>Issue activating license.</b> Please ensure that you entered your license key correctly. If you continue to have an issue please see <a href='https://smashballoon.com/my-license-key-wont-activate/' target='_blank'>here</a>.";
		} else {
			$sbi_expired_box_classes = "sbi-license-expired";
			$sbi_expired_box_msg = "<b>Important:</b> Your Instagram Feed Pro license key has expired. You are no longer receiving updates.";
		}

		//Create the re-check link using the existing query string in the URL
		$sbi_url = '?' . $_SERVER["QUERY_STRING"];
		//Determine the separator
		( !empty($sbi_url) && $sbi_url != '' ) ? $separator = '&' : $separator = '';
		//Add the param to check license if it doesn't already exist in URL
		if( strpos($sbi_url, 'sbichecklicense') === false ) $sbi_url .= $separator . "sbichecklicense=true";

		//Create the notice message
		if( !$sbi_license_activation_error ) $sbi_expired_box_msg .= " &nbsp;<a href='https://smashballoon.com/checkout/?edd_license_key=".$sbi_license."&download_id=".$sbi_download_id."' target='_blank'>Renew License</a>&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='javascript:void(0);' id='sbi-why-renew-show' onclick='sbiShowReasons()'>Why renew?</a><a href='javascript:void(0);' id='sbi-why-renew-hide' onclick='sbiHideReasons()' style='display: none;'>Hide text</a> <a href='".$sbi_url."' class='sbi-button'>Re-check License</a></p>
            <div id='sbi-why-renew' style='display: none;'>
                <h4>Customer Support</h4>
                <p>Without a valid license key you will no longer be able to receive updates or support for the Instagram Feed plugin. A renewed license key grants you access to our top-notch, quick and effective support for another full year.</p>

                <h4>Maintenance Upates</h4>
                <p>With both WordPress and the Instagram API being updated on a regular basis we stay on top of the latest changes and provide frequent updates to keep pace.</p>

                <h4>New Feature Updates</h4>
                <p>We're continually adding new features to the plugin, based on both customer suggestions and our own ideas for ways to make it better, more useful, more customizable, more robust and just more awesome! Renew your license to prevent from missing out on any of the new features added in the future.</p>
            </div>";

		if( $sbi_check_license && !$sbi_license_expired && !$sbi_expire_countdown ){
			$sbi_expired_box_classes = "sbi-license-expired sbi-license-valid";
			$sbi_expired_box_msg = "Thanks ".$sbi_license_data["customer_name"].", your Instagram Feed Pro license key is valid.";
		}

		_e("
        <div class='".$sbi_expired_box_classes."'>");
		if( $sbi_expire_countdown ) _e("<a style='float:right; color: #dd3d36; text-decoration: none;' href='" .esc_url( add_query_arg( 'sbi_nag_ignore', '0' ) ). "'>Dismiss</a>");
		_e("<p>".$sbi_expired_box_msg." 
        </div>
        <script type='text/javascript'>
        function sbiShowReasons() {
            document.getElementById('sbi-why-renew').style.display = 'block';
            document.getElementById('sbi-why-renew-show').style.display = 'none';
            document.getElementById('sbi-why-renew-hide').style.display = 'inline';
        }
        function sbiHideReasons() {
            document.getElementById('sbi-why-renew').style.display = 'none';
            document.getElementById('sbi-why-renew-show').style.display = 'inline';
            document.getElementById('sbi-why-renew-hide').style.display = 'none';
        }
        </script>
        ");
	}

}

/* Display a license expired notice that can be dismissed */
add_action('admin_notices', 'sbi_renew_license_notice');
function sbi_renew_license_notice() {
	//Only display notice to admins
	if( !current_user_can( 'manage_options' ) ) return;

	//Show this notice on every page apart from the Instagram Feed settings pages
	isset($_GET['page'])? $sbi_check_page = $_GET['page'] : $sbi_check_page = '';
	if ( $sbi_check_page !== 'sb-instagram-feed' && $sbi_check_page !== 'sb-instagram-license' ) {

		//If the user is re-checking the license key then use the API below to recheck it
		( isset( $_GET['sbichecklicense'] ) ) ? $sbi_check_license = true : $sbi_check_license = false;

		$sbi_license = trim( get_option( 'sbi_license_key' ) );

		global $current_user;
		$user_id = $current_user->ID;

		// Use this to show notice again
		//delete_user_meta($user_id, 'sbi_ignore_notice');

		/* Check that the license exists and the user hasn't already clicked to ignore the message */
		if( empty($sbi_license) || !isset($sbi_license) || get_user_meta($user_id, 'sbi_ignore_notice') && !$sbi_check_license ) return;

		//Is there already license data in the db?
		if( get_option( 'sbi_license_data' ) && !$sbi_check_license ){
			//Yes
			//Get license data from the db and convert the object to an array
			$sbi_license_data = (array) get_option( 'sbi_license_data' );
		} else {
			//No
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

			//Store license data in db
			update_option( 'sbi_license_data', $sbi_license_data );

		}

		//Number of days until license expires
		$sbi_date1 = isset( $sbi_license_data['expires'] ) ? $sbi_license_data['expires'] : 'lifetime';
		if( $sbi_date1 == 'lifetime' ) $sbi_date1 = '2036-12-31 23:59:59';
		$sbi_date2 = date('Y-m-d');
		$sbi_interval = round(abs(strtotime($sbi_date2)-strtotime($sbi_date1))/86400);

		//Is license expired?
		( $sbi_interval == 0 || strtotime($sbi_date1) < strtotime($sbi_date2) ) ? $sbi_license_expired = true : $sbi_license_expired = false;

		//If expired date is returned as 1970 (or any other 20th century year) then it means that the correct expired date was not returned and so don't show the renewal notice
		if( $sbi_date1[0] == '1' ) $sbi_license_expired = false;

		//If there's no expired date then don't show the expired notification
		if( empty($sbi_date1) || !isset($sbi_date1) ) $sbi_license_expired = false;

		//Is license missing - ie. on very first check
		if( isset($sbi_license_data['error']) ){
			if( $sbi_license_data['error'] == 'missing' ) $sbi_license_expired = false;
		}

		//If license expires in less than 30 days and it isn't currently expired then show the expire countdown instead of the expiration notice
		if($sbi_interval < 30 && !$sbi_license_expired){
			$sbi_expire_countdown = true;
		} else {
			$sbi_expire_countdown = false;
		}


		//Is the license expired?
		if( ($sbi_license_expired || $sbi_expire_countdown) || $sbi_check_license ) {

			global $sbi_download_id;

			//If expire countdown then add the countdown class to the notice box
			if($sbi_expire_countdown){
				$sbi_expired_box_classes = "sbi-license-expired sbi-license-countdown";
				$sbi_expired_box_msg = "Hey ".$sbi_license_data["customer_name"].", your Custom Feeds for Instagram Pro license key expires in " . $sbi_interval . " days.";
			} else {
				$sbi_expired_box_classes = "sbi-license-expired";
				$sbi_expired_box_msg = "Hey ".$sbi_license_data["customer_name"].", your Custom Feeds for Instagram Pro license key has expired.";
			}

			//Create the re-check link using the existing query string in the URL
			$sbi_url = '?' . $_SERVER["QUERY_STRING"];
			//Determine the separator
			( !empty($sbi_url) && $sbi_url != '' ) ? $separator = '&' : $separator = '';
			//Add the param to check license if it doesn't already exist in URL
			if( strpos($sbi_url, 'sbichecklicense') === false ) $sbi_url .= $separator . "sbichecklicense=true";

			//Create the notice message
			$sbi_expired_box_msg .= " Click <a href='https://smashballoon.com/checkout/?edd_license_key=".$sbi_license."&download_id=".$sbi_download_id."' target='_blank'>here</a> to renew your license. <a href='javascript:void(0);' id='sbi-why-renew-show' onclick='sbiShowReasons()'>Why renew?</a><a href='javascript:void(0);' id='sbi-why-renew-hide' onclick='sbiHideReasons()' style='display: none;'>Hide text</a> <a href='".$sbi_url."' class='sbi-button'>Re-check License</a></p>
                <div id='sbi-why-renew' style='display: none;'>
                    <h4>Customer Support</h4>
                    <p>Without a valid license key you will no longer be able to receive updates or support for the Custom Feeds for Instagram plugin. A renewed license key grants you access to our top-notch, quick and effective support for another full year.</p>

                    <h4>Maintenance Upates</h4>
                    <p>With both WordPress and the Instagram API being updated on a regular basis we stay on top of the latest changes and provide frequent updates to keep pace.</p>

                    <h4>New Feature Updates</h4>
                    <p>We're continually adding new features to the plugin, based on both customer suggestions and our own ideas for ways to make it better, more useful, more customizable, more robust and just more awesome! Renew your license to prevent from missing out on any of the new features added in the future.</p>
                </div>";

			if( $sbi_check_license && !$sbi_license_expired && !$sbi_expire_countdown ){
				$sbi_expired_box_classes = "sbi-license-expired sbi-license-valid";
				$sbi_expired_box_msg = "Thanks ".$sbi_license_data["customer_name"].", your Custom Feeds for Instagram Pro license key is valid.";
			}

			_e("
            <div class='".$sbi_expired_box_classes."'>
                <a style='float:right; color: #dd3d36; text-decoration: none;' href='" .esc_url( add_query_arg( 'sbi_nag_ignore', '0' ) ). "'>Dismiss</a>
                <p>".$sbi_expired_box_msg." 
            </div>
            <script type='text/javascript'>
            function sbiShowReasons() {
                document.getElementById('sbi-why-renew').style.display = 'block';
                document.getElementById('sbi-why-renew-show').style.display = 'none';
                document.getElementById('sbi-why-renew-hide').style.display = 'inline';
            }
            function sbiHideReasons() {
                document.getElementById('sbi-why-renew').style.display = 'none';
                document.getElementById('sbi-why-renew-show').style.display = 'inline';
                document.getElementById('sbi-why-renew-hide').style.display = 'none';
            }
            </script>
            ");
		}

	}
}
add_action('admin_init', 'sbi_nag_ignore');
function sbi_nag_ignore() {
	global $current_user;
	$user_id = $current_user->ID;
	if ( isset($_GET['sbi_nag_ignore']) && '0' == $_GET['sbi_nag_ignore'] ) {
		add_user_meta($user_id, 'sbi_ignore_notice', 'true', true);
	}
}

function sbi_disable_welcome() {

	add_user_meta(get_current_user_id(), 'sbi_disable_welcome', 'true', true);

	echo "1";

	die();
}
add_action( 'wp_ajax_sbi_disable_welcome', 'sbi_disable_welcome' );

function sbi_admin_hide_unrelated_notices() {

	// Bail if we're not on a sbi screen or page.
	if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'sb-instagram-feed') === false ) {
		return;
	}

	// Extra banned classes and callbacks from third-party plugins.
	$blacklist = array(
		'classes'   => array(),
		'callbacks' => array(
			'sbidb_admin_notice', // 'Database for sbi' plugin.
		),
	);

	global $wp_filter;

	foreach ( array( 'user_admin_notices', 'admin_notices', 'all_admin_notices' ) as $notices_type ) {
		if ( empty( $wp_filter[ $notices_type ]->callbacks ) || ! is_array( $wp_filter[ $notices_type ]->callbacks ) ) {
			continue;
		}
		foreach ( $wp_filter[ $notices_type ]->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
					unset( $wp_filter[ $notices_type ]->callbacks[ $priority ][ $name ] );
					continue;
				}
				$class = ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ? strtolower( get_class( $arr['function'][0] ) ) : '';
				if (
					! empty( $class ) &&
					strpos( $class, 'sbi' ) !== false &&
					! in_array( $class, $blacklist['classes'], true )
				) {
					continue;
				}
				if (
					! empty( $name ) && (
						strpos( $name, 'sbi' ) === false ||
						in_array( $class, $blacklist['classes'], true ) ||
						in_array( $name, $blacklist['callbacks'], true )
					)
				) {
					unset( $wp_filter[ $notices_type ]->callbacks[ $priority ][ $name ] );
				}
			}
		}
	}
}
add_action( 'admin_print_scripts', 'sbi_admin_hide_unrelated_notices' );

function sbi_connect_new_account( $access_token, $account_id ) {
	$split_id = explode( ' ', trim( $account_id ) );
	$account_id = preg_replace("/[^A-Za-z0-9 ]/", '', $split_id[0] );
	if ( ! empty( $account_id ) ) {
		$split_token = explode( ' ', trim( $access_token ) );
		$access_token = preg_replace("/[^A-Za-z0-9 ]/", '', $split_token[0] );
	}


	$options = sbi_get_database_settings();
	$connected_accounts =  isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

	if ( $access_token ) {
		wp_cache_delete ( 'alloptions', 'options' );

		$number_dots = substr_count ( $access_token , '.' );
		$test_connection_data = array( 'error_message' => 'A successful connection could not be made. Please make sure your Access Token is valid.');

		if ( $number_dots > 1 ) {
			$split_token = explode( '.', $access_token );
			$new_user_id = isset( $split_token[0] ) ? $split_token[0] : '';

			$test_connection_data = sbi_account_data_for_token( $access_token );
		} else if (! empty( $account_id ) ) {

			if ( sbi_code_check( $access_token ) ) {
				$data = array(
					'access_token' => $access_token,
					'user_id' => $account_id,
					'type' => 'basic'
				);
				$basic_account_attempt = new SB_Instagram_API_Connect( $data, 'header', array() );
				$basic_account_attempt->connect();

				if ( !$basic_account_attempt->is_wp_error() && ! $basic_account_attempt->is_instagram_error() ) {
					$new_data = $basic_account_attempt->get_data();

					$basic_account_access_token_connect = new SB_Instagram_API_Connect( $data, 'access_token', array() );
					$basic_account_access_token_connect->connect();
					if ( !$basic_account_access_token_connect->is_wp_error() && ! $basic_account_access_token_connect->is_instagram_error() ) {

						$token_data = $basic_account_access_token_connect->get_data();
						$expires_in = $token_data['expires_in'];
						$expires_timestamp = time() + $expires_in;
						$account_type = isset( $new_data['account_type'] ) ? $new_data['account_type'] : 'personal';

						$new_connected_account = array(
							'access_token' => $access_token,
							'account_type' => $account_type,
							'user_id' => $new_data['id'],
							'username' => $new_data['username'],
							'expires_timestamp' => $expires_timestamp,
							'type' => 'basic'
						);

						$updated_options = sbi_connect_basic_account( $new_connected_account );

						return wp_json_encode( $updated_options['connected_accounts'][ $new_data['id'] ] );

					} else {
						if ( $basic_account_access_token_connect->is_wp_error() ) {
							$error = $basic_account_access_token_connect->get_wp_error();
						} else {
							$error = $basic_account_access_token_connect->get_data();
						}
						return sbi_formatted_error( $error );
					}

				} else {
					if ( $basic_account_attempt->is_wp_error() ) {
						$error = $basic_account_attempt->get_wp_error();
					} else {
						$error = $basic_account_attempt->get_data();
					}
					return sbi_formatted_error( $error );
				}
			}

			$url = 'https://graph.facebook.com/'.$account_id.'?fields=biography,id,username,website,followers_count,media_count,profile_picture_url,name&access_token='.sbi_maybe_clean( $access_token );
			$json = json_decode( sbi_business_account_request( $url, array( 'access_token' => $access_token ) ), true );

			if ( isset( $json['error'] ) && $json['error']['type'] === 'OAuthException' ) {
				$data = array(
					'access_token' => $access_token,
					'user_id' => $account_id,
					'type' => 'basic'
				);
				$basic_account_attempt = new SB_Instagram_API_Connect( $data, 'header', array() );
				$basic_account_attempt->connect();

				if ( !$basic_account_attempt->is_wp_error() && ! $basic_account_attempt->is_instagram_error() ) {
					$new_data = $basic_account_attempt->get_data();

					$basic_account_access_token_connect = new SB_Instagram_API_Connect( $data, 'access_token', array() );
					$basic_account_access_token_connect->connect();
					if ( !$basic_account_access_token_connect->is_wp_error() && ! $basic_account_access_token_connect->is_instagram_error() ) {

						$token_data = $basic_account_access_token_connect->get_data();
						$expires_in = $token_data['expires_in'];
						$expires_timestamp = time() + $expires_in;

						$new_connected_account = array(
							'access_token' => $access_token,
							'account_type' => $new_data['account_type'],
							'user_id' => $new_data['id'],
							'username' => $new_data['username'],
							'expires_timestamp' => $expires_timestamp,
							'type' => 'basic'
						);

						$updated_options = sbi_connect_basic_account( $new_connected_account );

						return wp_json_encode( $updated_options['connected_accounts'][ $new_data['id'] ] );

					} else {
						if ( $basic_account_access_token_connect->is_wp_error() ) {
							$error = $basic_account_access_token_connect->get_wp_error();
						} else {
							$error = $basic_account_access_token_connect->get_data();
						}
						return sbi_formatted_error( $error );
					}

				} else {
					if ( $basic_account_attempt->is_wp_error() ) {
						$error = $basic_account_attempt->get_wp_error();
					} else {
						$error = $basic_account_attempt->get_data();
					}
					return sbi_formatted_error( $error );
				}

			} else {
				if ( isset( $json['id'] ) ) {
					$new_user_id = $json['id'];
					$test_connection_data = array(
						'access_token' => $access_token,
						'id' => $json['id'],
						'username' => $json['username'],
						'type' => 'business',
						'is_valid' => true,
						'last_checked' => time(),
						'profile_picture' => $json['profile_picture_url']
					);
				}
			}


			global $sb_instagram_posts_manager;

			$sb_instagram_posts_manager->remove_error( 'at_' . $json['username'] );
			delete_transient( SBI_USE_BACKUP_PREFIX . 'sbi_'  . $json['id'] );

		}

		if ( isset( $test_connection_data['error_message'] ) ) {
			return $test_connection_data['error_message'];
		} elseif ( $test_connection_data !== false && ! empty( $new_user_id ) ) {
			$username = $test_connection_data['username'] ? $test_connection_data['username'] : $connected_accounts[ $new_user_id ]['username'];
			$user_id = $test_connection_data['id'] ? $test_connection_data['id'] : $connected_accounts[ $new_user_id ]['user_id'];
			$profile_picture = $test_connection_data['profile_picture'] ? $test_connection_data['profile_picture'] : $connected_accounts[ $new_user_id ]['profile_picture'];
			$type = isset( $test_connection_data['type'] ) ? $test_connection_data['type'] : 'personal';
			$connected_accounts[ $new_user_id ] = array(
				'access_token' => sbi_get_parts( $access_token ),
				'user_id' => $user_id,
				'username' => $username,
				'type' => $type,
				'is_valid' => true,
				'last_checked' => $test_connection_data['last_checked'],
				'profile_picture' => $profile_picture
			);

			if ( !$options['sb_instagram_disable_resize'] ) {
				if ( sbi_create_local_avatar( $username, $profile_picture ) ) {
					$connected_accounts[ $new_user_id ]['local_avatar'] = true;
				}
			} else {
				$connected_accounts[ $new_user_id ]['local_avatar'] = false;
			}

			if ( $type === 'business' ) {
				$url = 'https://graph.facebook.com/'.$user_id.'/tags?user_id='.$user_id.'&fields=id&limit=1&access_token='.sbi_maybe_clean( $access_token );
				$args = array(
					'timeout' => 60,
					'sslverify' => false
				);
				$response = wp_remote_get( $url, $args );

				if ( ! is_wp_error( $response ) ) {
					// certain ways of representing the html for double quotes causes errors so replaced here.
					$response = json_decode( str_replace( '%22', '&rdquo;', $response['body'] ), true );
					if ( isset( $response['data'] ) ) {
						$connected_accounts[ $new_user_id ]['use_tagged'] = '1';
					}
				} else {
					return sbi_formatted_error( $response );
				}
			}

			delete_transient( SBI_USE_BACKUP_PREFIX . 'sbi_'  . $user_id );
			global $sb_instagram_posts_manager;

			$sb_instagram_posts_manager->remove_error( 'at_' . $username );
			$options['connected_accounts'] = $connected_accounts;

			update_option( 'sb_instagram_settings', $options );

			return wp_json_encode( $connected_accounts[ $new_user_id ] );
		} else {
			return 'A successful connection could not be made. Please make sure your Access Token is valid.';
		}

	}

	return '';
}

function sbi_no_js_connected_account_management() {
    if ( ! current_user_can( 'manage_instagram_feed_options' ) ) {
        return;
    }
    if ( isset( $_POST['sb_manual_at'] ) ) {
	    $access_token = isset( $_POST['sb_manual_at'] ) ? trim( sanitize_text_field( $_POST['sb_manual_at'] ) ) : false;
	    $account_id = isset( $_POST['sb_manual_account_id'] ) ? sanitize_text_field( $_POST['sb_manual_account_id'] ) : false;
	    if ( ! $access_token || ! $account_id ) {
		    return;
	    }
	    sbi_connect_new_account( $access_token, $account_id );
    } elseif ( isset( $_GET['disconnect'] ) && isset( $_GET['page'] ) && $_GET['page'] === 'sb-instagram-feed' ) {
        $account_id = sanitize_text_field( $_GET['disconnect'] );
	    sbi_do_account_delete( $account_id );
    }

}
add_action( 'admin_init', 'sbi_no_js_connected_account_management' );