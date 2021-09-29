<?php
/**
 * Class SB_Instagram_Feed_Pro
 *
 * The Pro class mostly adds additional methods
 * used in the "display_instagram" function for supporting
 * additional features.
 *
 * @since 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Feed_Pro extends SB_Instagram_Feed
{
	/**
	 * @var array
	 */
	private $username_avatars;

	public function set_next_pages( $next_pages ) {
		$this->next_pages = $next_pages;
	}

	/**
	 * the API_Connect class can use either a premade url or
	 * settings from a connected account, type, and parameters
	 *
	 * @param array|string $connected_account_or_page
	 * @param null $type
	 * @param null $params
	 *
	 * @return object|SB_Instagram_API_Connect_Pro
	 */
	public function make_api_connection( $connected_account_or_page, $type = NULL, $params = NULL ) {
		return new SB_Instagram_API_Connect_Pro( $connected_account_or_page, $type, $params );
	}

	/**
	 * Used to trigger the appending of recent hashtag posts
	 * that are only available in the custom database tables
	 *
	 * @return bool
	 */
	public function out_of_next_pages() {
		return $this->get_next_pages() === false;
	}

	/**
	 * Only recent hashtag feeds need posts from the db currently
	 * but this could change in the future.
	 *
	 * @param array $settings
	 * @param array $feed_type_and_terms
	 *
	 * @return bool
	 * @since 5.0
	 */
	public function should_look_for_db_only_posts( $settings, $feed_type_and_terms ) {
		return isset( $feed_type_and_terms['hashtags_recent'] );
	}

	/**
	 * Recent hashtag feeds need older posts added from the database
	 * as they aren't available from the API after 24 hours
	 *
	 * @param string $feed_id
	 * @param array $settings
	 * @param array $feed_type_and_terms
	 *
	 * @since 5.0
	 * @since 5.1 added "GROUP BY" clause to prevent duplicate post retrieval
	 */
	public function add_db_only_posts( $feed_id, $settings, $feed_type_and_terms ) {
		if ( isset( $settings['db_query_feed_id'] ) ) {
			$query_feed_id = $settings['db_query_feed_id'];
		} else {
			$feed_id_array = explode( '#', $feed_id );
			$query_feed_id = $feed_id_array[0];
		}

		$post_data = $this->get_post_data();
		$last_post_data_set = array_slice( $post_data, -$settings['minnum'], $settings['minnum'] );

		$earliest_time_stamp = date( 'Y-m-d H:i:s', time() - (24*60*60) );
		if ( ! isset( $feed_type_and_terms['hashtags_recent'] ) && isset( $last_post_data_set[0] ) ) {
			// usort($last_post_data_set, 'sbi_date_sort' ); not accurate, instead sort all posts by placeholder time stamp after
			// looping through the last post set. this is only necessary from randomly sorted hashtag posts, should be rare

			$last_post = array_slice( $last_post_data_set, -1 );

			$earliest_time_stamp = SB_Instagram_Feed_Pro::get_time_stamp( $feed_id, $last_post[0]['id']);
		}

		$this->add_report( 'Earliest time stamp used ' . $earliest_time_stamp );

		$post_set = SB_Instagram_Feed_Pro::get_post_set_from_db( $query_feed_id, 0, $earliest_time_stamp );

		if ( isset( $post_set[0] ) ) {

			$this->add_report( 'Db returned posts: ' . count( $post_set ) );

			foreach ( $post_set as $post ) {
				$decoded = json_decode( $post, true );
				if ( isset( $decoded['id'] ) ) {
					$decoded = $this->filter_posts( array( $decoded ), $settings );

					if ( ! empty( $decoded[0] ) ) {
						array_push( $post_data, $decoded[0] );
					}
				}
			}

			$this->set_post_data( $post_data );
		} else {
			$this->add_report( 'Db returned no posts' );
		}

	}

	/**
	 * Queries the database for post data. Useful for getting posts no longer available in the API
	 * or getting them more efficiently.
	 *
	 * @param string $feed_id
	 * @param int $offset
	 * @param string $max_timestamp formatted date
	 * @param int $num_posts
	 *
	 * @return array
	 *
	 * @since 5.0
	 */
	public static function get_post_set_from_db( $feed_id, $offset, $max_timestamp, $num_posts = 200 ) {
		global $wpdb;

		$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;
		// this will help cover small changes in the number of posts requested
		// as well as posts stored when in moderation mode
		$feed_id_variant_1 = substr( $feed_id, 0, -1 );
		$feed_id_variant_2 = substr( $feed_id, 0, -2 );
		$feed_id_variant_3 = substr( $feed_id, 0, -3 );
		$feed_id_variant_4 = substr( $feed_id, 0, -4 );

		$additional_where = " OR BINARY f.feed_id = '" . esc_sql( $feed_id_variant_1 ) .
		                    "' OR BINARY f.feed_id = '" . esc_sql( $feed_id_variant_2 ) .
		                    "' OR BINARY f.feed_id = '" . esc_sql( $feed_id_variant_3 ) .
		                    "' OR BINARY f.feed_id = '" . esc_sql( $feed_id_variant_4 ) . "'";

		/**
		 * The additional "WHERE" clause to find all posts desired in the feed
		 *
		 * @param string $addtional_where  Escaped "WHERE" clause
		 * @param array $feed_id           Transient name and ID of feed based on settings
		 *
		 * @since 5.2
		 */
		$additional_where = apply_filters( 'sbi_db_query_additional_where', $additional_where, $feed_id );

		$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.json_data FROM $posts_table_name AS p INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			WHERE BINARY f.feed_id = %s
			{$additional_where}
			AND p.time_stamp < '$max_timestamp'
			GROUP BY p.instagram_id
			ORDER BY p.time_stamp
			DESC LIMIT %d, %d", $feed_id, $offset, $num_posts ) );

		return $results;
	}

	public static function get_time_stamp( $feed_id, $post_id ) {
		global $wpdb;

		$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;
		$feed_id_array = explode( '#', $feed_id );
		$feed_id = $feed_id_array[0];
		$is_top_post_feed = (substr( $feed_id, 4, 1 ) === '+');

		$timestamp_column = $is_top_post_feed ? 'top_time_stamp' : 'time_stamp';

		$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.$timestamp_column FROM $posts_table_name AS p INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			WHERE f.instagram_id = %s 
			ORDER BY p.$timestamp_column
			ASC LIMIT 1", $post_id ) );

		$return = isset( $results[0] ) ? $results[0] : date( 'Y-m-d H:i:s' );

		return $return;
	}

	/**
	 * Connects to the Instagram API to retrieve the current "story"
	 *
	 * @param $settings
	 * @param array $feed_types_and_terms
	 * @param array $connected_accounts_for_feed
	 *
	 * @since 5.0
	 */
	public function set_remote_stories_data( $settings, $feed_types_and_terms, $connected_accounts_for_feed ) {
		$first_user = isset( $feed_types_and_terms['users'][0] ) ? $feed_types_and_terms['users'][0]['term'] : '';
		$header_data = $this->get_header_data();

		if ( isset( $connected_accounts_for_feed[ $first_user ] ) && isset( $connected_accounts_for_feed[ $first_user ]['type'] ) && $connected_accounts_for_feed[ $first_user ]['type'] === 'business' ) {
			$connection = new SB_Instagram_API_Connect_Pro( $connected_accounts_for_feed[ $first_user ], 'stories', array() );

			$connection->connect();

			if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
				$stories_data = $connection->get_data();

				if ( isset( $stories_data ) ) {
					// story posts are reverse chronological but we want them chronological to match Instagram
					$header_data['stories'] = array_reverse( $stories_data );
					$this->set_header_data( $header_data );
				}
			} else {
				if ( $connection->is_wp_error() ) {
					SB_Instagram_API_Connect::handle_wp_remote_get_error( $connection->get_wp_error() );
				} else {
					SB_Instagram_API_Connect::handle_instagram_error( $connection->get_data(), $connected_accounts_for_feed[ $first_user ], 'header' );
				}
			}
		}
	}

	/**
	 * The header is only displayed when the setting is enabled and
	 * an account has been connected
	 * Overwritten in the Pro version
	 *
	 * @param array $settings settings specific to this feed
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 *
	 * @return bool
	 *
	 * @since 5.2
	 */
	public function need_header( $settings, $feed_types_and_terms ) {
		$showheader = ($settings['showheader'] === 'on' || $settings['showheader'] === 'true' || $settings['showheader'] === true);
		return ($showheader && (isset( $feed_types_and_terms['users'] ) || isset( $feed_types_and_terms['tagged'] )));
	}

	/**
	 * There isn't much info available for non-user feeds so just a basic
	 * amount of header data is set
	 *
	 * @param $feed_types_and_terms
	 *
	 * @since 5.0
	 */
	public function set_generic_header_data( $feed_types_and_terms ) {
		if ( isset( $feed_types_and_terms['hashtags_top'][0] ) || isset( $feed_types_and_terms['hashtags_recent'][0] ) ) {
			$term_and_params = isset( $feed_types_and_terms['hashtags_top'][0] ) ? $feed_types_and_terms['hashtags_top'][0] : $feed_types_and_terms['hashtags_recent'][0];
			$this->set_header_data( array( 'term' => $term_and_params['hashtag_name'] ) );
		}
	}

	/**
	 *
	 * @param $feed_types_and_terms
	 *
	 * @return first user ID for header
	 *
	 * @since 5.2
	 */
	public function get_first_user( $feed_types_and_terms ) {
		if ( isset( $feed_types_and_terms['users'][0] ) ) {
			return $feed_types_and_terms['users'][0]['term'];
		} elseif ( isset( $feed_types_and_terms['tagged'][0] ) ) {
			return $feed_types_and_terms['tagged'][0]['term'];
		} else {
			return '';
		}
	}

	/**
	 * Uses the settings to determine if avatars are going to be used.
	 * Can make feed creation faster if not.
	 *
	 * @param $settings
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	public function need_avatars( $settings ) {
		if ( isset( $settings['type'] ) && $settings['type'] === 'hashtag' ) {
			return false;
		} elseif ( isset( $settings['disablelightbox'] ) && ($settings['disablelightbox'] === 'true' || $settings['disablelightbox'] === 'on') ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Available avatars are added to the feed as an attribute so they can be used in the lightbox
	 *
	 * @param $connected_accounts_in_feed
	 * @param $feed_types_and_terms
	 *
	 * @since 5.0
	 */
	public function set_up_feed_avatars( $connected_accounts_in_feed, $feed_types_and_terms ) {
		$header_data = $this->get_header_data();
		$ids_usernames_already_available = array();

		if ( ! empty( $header_data ) ) {
			if ( isset( $header_data['profile_picture_url'] ) ) {
				$this->set_avatar( $header_data['username'], $header_data['profile_picture_url'] );
				$ids_usernames_already_available[] = $header_data['id'];
				$ids_usernames_already_available[] = $header_data['username'];

				if ( isset( $feed_types_and_terms['users'][0]['term'] )
				     && isset( $connected_accounts_in_feed[ $feed_types_and_terms['users'][0]['term'] ]['local_avatar'] )
				     && (int)$connected_accounts_in_feed[ $feed_types_and_terms['users'][0]['term'] ]['local_avatar'] === 1 ) {
					$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $feed_types_and_terms['users'][0]['term'] ]['username'], 1 );
				} elseif ( isset( $feed_types_and_terms['tagged'][0]['term'] )
                     && isset( $connected_accounts_in_feed[ $feed_types_and_terms['tagged'][0]['term'] ]['local_avatar'] )
                     && (int)$connected_accounts_in_feed[ $feed_types_and_terms['tagged'][0]['term'] ]['local_avatar'] === 1 ) {
					$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $feed_types_and_terms['tagged'][0]['term'] ]['username'], 1 );
				} else {
					if ( isset( $feed_types_and_terms['users'][0]['term'] ) ) {
						$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $feed_types_and_terms['users'][0]['term'] ]['username'], 0 );
					} else {
						$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $feed_types_and_terms['tagged'][0]['term'] ]['username'], 0 );
					}
				}
			}
		}

		if ( isset( $feed_types_and_terms['users'] ) ) {
			foreach ( $feed_types_and_terms['users'] as $term_and_params ) {
				$user = $term_and_params['term'];

				if ( ! in_array( $user, $ids_usernames_already_available )
				     && ! in_array( $connected_accounts_in_feed[ $user ]['username'], $ids_usernames_already_available )
						&& isset( $connected_accounts_in_feed[ $user ]['profile_picture'] ) ) {
					$this->set_avatar( $connected_accounts_in_feed[ $user ]['username'], $connected_accounts_in_feed[ $user ]['profile_picture'] );

					if ( isset( $connected_accounts_in_feed[ $user ]['local_avatar'] ) && (int)$connected_accounts_in_feed[ $user ]['local_avatar'] === 1 ) {
						$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $user ]['username'], 1 );
					} else {
						$this->set_avatar( 'LCL' . $connected_accounts_in_feed[ $user ]['username'], 0 );
					}

					$ids_usernames_already_available[] = $user;
					$ids_usernames_already_available[] = $connected_accounts_in_feed[ $user ]['username'];
				}
			}
		}

	}

	/**
	 * Creates a key value pair of the username and the url of
	 * the avatar image
	 *
	 * @param $name
	 * @param $url
	 *
	 * @since 5.0
	 */
	public function set_avatar( $name, $url ) {
		$this->username_avatars[ $name ] = $url;
	}

	/**
	 * @return array
	 */
	public function get_username_avatars() {
		return $this->username_avatars;
	}

	/**
	 * Finds the earliest time stamp used for the posts from that
	 * match the feed ID
	 *
	 * @param $feed_id
	 *
	 * @return false|string
	 *
	 * @since 5.0
	 */
	public function get_earliest_time_stamp( $feed_id ) {
		global $wpdb;

		$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;
		$feed_id_array = explode( '#', $feed_id );
		$feed_id = $feed_id_array[0];
		$is_top_post_feed = (substr( $feed_id, 4, 1 ) === '+');

		$timestamp_column = $is_top_post_feed ? 'top_time_stamp' : 'time_stamp';

		$results = $wpdb->get_col( $wpdb->prepare( "SELECT p.$timestamp_column FROM $posts_table_name AS p INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id 
			WHERE BINARY f.feed_id = %s 
			ORDER BY p.$timestamp_column
			ASC LIMIT 1", $feed_id ) );

		$return = isset( $results[0] ) ? $results[0] : date( 'Y-m-d H:i:s' );

		return $return;
	}

	/**
	 * Used for filtering a single API request worth of posts
	 *
	 * @param $post_set
	 *
	 * @return mixed
	 *
	 * @since 5.0
	 * @since 5.1 support for filtering "includes any includeword
	 *  and also does not include any excludeword"
	 */
	protected function filter_posts( $post_set, $settings = array() ) {

		if ( empty( $settings['includewords'] )
		     && empty( $settings['excludewords'] )
		     && empty( $settings['whitelist'] )
		     && empty( $settings['hidephotos'] )
		     && $settings['media'] === 'all') {
			return $post_set;
		}

		$includewords = ! empty( $settings['includewords'] ) ? explode( ',', $settings['includewords'] ) : array();
		$excludewords = ! empty( $settings['excludewords'] ) ? explode( ',', $settings['excludewords'] ) : array();
		$hide_photos = ! empty( $settings['hidephotos'] ) && empty( $settings['doingModerationMode'] ) ? explode( ',', str_replace( ' ', '', $settings['hidephotos'] ) ) : array();
		$white_list = ! empty( $settings['whitelist'] ) && empty( $settings['doingModerationMode'] ) ? get_option( 'sb_instagram_white_lists_'.$settings['whitelist'], array() ) : false;
		$media_filter = $settings['media'] !== 'all' ? $settings['media'] : false;

		$filtered_posts = array();
		foreach ( $post_set as $post ) {
			$keep_post = false;

			$padded_caption = ' ' . str_replace( array( '+', '%0A' ), ' ',  urlencode( str_replace( array( '#', '@' ), array( ' HASHTAG', ' MENTION' ), strtolower( SB_Instagram_Parse_Pro::get_caption( $post ) ) ) ) ) . ' ';
			$id = SB_Instagram_Parse_Pro::get_post_id( $post );

			$is_hidden = false;
			$passes_media_filter = true;
			if ( ! empty( $hide_photos )
			     && (in_array( $id, $hide_photos, true ) || in_array( 'sbi_' . $id, $hide_photos, true )) ) {
				$is_hidden = true;
				if ( $white_list ) {
					if ( in_array( $id, $white_list, true ) || in_array( 'sbi_' . $id, $white_list, true ) ) {
						$is_hidden = false;
					}
				}
			}

			if ( $media_filter ) {
				$media_type = SB_Instagram_Parse_Pro::get_media_type( $post );
				if ( $media_filter === 'videos' ) {
					if ( $media_type !== 'video' ) {
						$passes_media_filter = false;
					}
				} else {
					if ( $media_type === 'video' ) {
						$passes_media_filter = false;
					}
				}
			}

			// any blocked photos will not pass any additional filters so don't bother processing
			if ( ! $is_hidden && $passes_media_filter ) {
				$is_on_white_list = false;
				$has_includeword = false;
				$has_excludeword = false;
				$passes_word_filter = false;

				if ( $white_list ) {
					if ( in_array( $id, $white_list, true ) || in_array( 'sbi_' . $id, $white_list, true ) ) {
						$is_on_white_list = true;
					}
				} elseif ( ! empty( $includewords ) || ! empty( $excludewords ) ) {
					if ( ! empty( $includewords ) ) {
						foreach ( $includewords as $includeword ) {
							if ( ! empty( $includeword ) ) {
								if ( strpos( $includeword, '#' ) === 0 && SB_Instagram_Parse_Pro::get_tags( $post ) ) {
									$tags            = SB_Instagram_Parse_Pro::get_tags( $post );
									$this_tag        = str_replace( '#', '', strtolower( $includeword ) );
									$has_includeword = in_array( $this_tag, $tags, true );
								} else {
									$converted_includeword = trim( str_replace( '+', ' ', urlencode( str_replace( array( '#', '@' ), array( ' HASHTAG', ' MENTION' ), strtolower( $includeword ) ) ) ) );

									if ( preg_match( '/\b' . $converted_includeword . '\b/i', $padded_caption, $matches ) ) {
										$has_includeword = true;
									}
								}
							}
						}
					}

					if ( ! empty( $excludewords ) ) {
						foreach ( $excludewords as $excludeword ) {
							if ( ! empty( $excludeword ) ) {
								if ( strpos( $excludeword, '#' ) === 0 && SB_Instagram_Parse_Pro::get_tags( $post ) ) {
									$tags = SB_Instagram_Parse_Pro::get_tags( $post );
									$this_tag = str_replace( '#', '', strtolower( $excludeword ) );
									$has_excludeword = in_array( $this_tag, $tags, true );
								} else {
									$converted_excludeword = trim( str_replace('+', ' ', urlencode( str_replace( array( '#', '@' ), array( ' HASHTAG', ' MENTION' ), strtolower( $excludeword ) ) ) ) );
									if ( preg_match('/\b'.$converted_excludeword.'\b/i', $padded_caption, $matches ) ) {
										$has_excludeword = true;
									}
								}
							}
						}
					}
					if ( ! empty( $excludewords ) && ! empty( $includewords ) ) {
						$passes_word_filter = $has_includeword && ! $has_excludeword;
					} elseif ( ! empty( $includewords ) ) {
						$passes_word_filter = $has_includeword;
					} else {
						$passes_word_filter = !$has_excludeword;
					}

				} else {
					// no other filters so it belongs in the feed
					$keep_post = true;
				}

				if ( $is_on_white_list || $passes_word_filter ) {
					$keep_post = true;
				}
			}

			$keep_post = apply_filters( 'sbi_passes_filter', $keep_post, $post, $settings );
			if ( $keep_post ) {
				$filtered_posts[] = $post;
			}

		}

		return $filtered_posts;
	}

	/**
	 * Sorts a post set based on sorting settings. Sorting by "alternate"
	 * is done when merging posts for efficiency's sake so the post set is
	 * just returned as it is.
	 *
	 * @param array $post_set
	 * @param array $settings
	 *
	 * @return mixed|array
	 *
	 * @since 5.5
	 */
	protected function sort_posts( $post_set, $settings ) {
		if ( empty( $post_set ) ) {
			return $post_set;
		}

		// sorting done with "merge_posts" to be more efficient
		if ( $settings['sortby'] === 'alternate' || $settings['sortby'] === 'api' ) {
			$return_post_set = $post_set;
		} elseif ( $settings['sortby'] === 'random' ) {
			/*
             * randomly selects posts in a random order. Cache saves posts
             * in this random order so paginating does not cause some posts to show up
             * twice or not at all
             */
			usort($post_set, 'sbi_rand_sort' );
			$return_post_set = $post_set;

		} elseif ( $settings['sortby'] === 'likes' ) {
			usort($post_set, 'sbi_likes_sort' );
			$return_post_set = $post_set;
		} else {
			// compares posted on dates of posts
			usort($post_set, 'sbi_date_sort' );
			$return_post_set = $post_set;
		}

		/**
		 * Apply a custom sorting of posts
		 *
		 * @param array $return_post_set    Ordered set of filtered posts
		 * @param array $settings           Settings for this feed
		 *
		 * @since 2.1/5.2
		 */

		return apply_filters( 'sbi_sorted_posts', $return_post_set, $settings );
	}

	/**
	 * Total number of IDs in the white list already exist in the feed. Used
	 * to prevent further pagination when no more white listed posts will be
	 * found
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	protected function feed_is_complete( $settings, $offset = 0 ) {
		if ( ! empty( $settings['whitelist_ids'] ) ) {
			if ( isset( $settings['doingModerationMode'] ) && $settings['doingModerationMode'] ) {
				return false;
			}
			$total_posts_loaded = $settings['num'] + $offset;

			if ( (int)$settings['whitelist_num'] <= $total_posts_loaded ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Adds various data attributes to the main feed divthat are used
	 * by the JavaScript file to layout the feed, trigger certain features,
	 * and launchvmoderation mode
	 *
	 * @param $other_atts
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	protected function add_other_atts( $other_atts, $settings ) {
		$options_att_arr = array();

		$layout = $settings['layout'];
		if ( ! in_array( $layout, array( 'masonry', 'highlight', 'carousel' ) ) ) {
			$layout = 'grid';
		}

		if ( $layout === 'carousel' ) {
			$arrows = $settings['carouselarrows'] == 'true' || $settings['carouselarrows'] == 'on' || $settings['carouselarrows'] == 1 || $settings['carouselarrows'] == '1';
			$pag = $settings['carouselpag'] == 'true' || $settings['carouselpag'] == 'on' || $settings['carouselpag'] == 1 || $settings['carouselpag'] == '1';
			$autoplay = $settings['carouselautoplay'] == 'true' || $settings['carouselautoplay'] == 'on' || $settings['carouselautoplay'] == 1 || $settings['carouselautoplay'] == '1';
			$time = $autoplay ? (int)$settings['carouseltime'] : false;
			$loop = ! empty( $settings['carouselloop'] ) && ($settings['carouselloop'] !== 'rewind') ? false : true;
			$rows = ! empty( $settings['carouselrows'] ) ? min( (int)$settings['carouselrows'], 2 ) : 1;
			$options_att_arr['carousel'] = array( $arrows, $pag, $autoplay, $time, $loop, $rows );
		} elseif ( $layout === 'highlight' ) {
			$type = trim( $settings['highlighttype'] );
			$pattern = trim( $settings['highlightpattern'] );
			$offset = (int)trim( $settings['highlightoffset'] );
			$hashtag = str_replace( ',', '|', trim( str_replace( array( '#', ' '), '', $settings['highlighthashtag'] ) ) );
			$ids = str_replace( ',', '|', trim( str_replace( array( 'sbi_', ''), '', $settings['highlightids'] ) ) );
			$options_att_arr['highlight'] = array( $type, $pattern, $offset, $hashtag, $ids );
		} elseif ( $layout === 'masonry' ) {
			$options_att_arr['masonry'] = true;
		} else {
			$options_att_arr['grid'] = true;
		}
		$autoscroll = $settings['autoscroll'] == 'true' || $settings['autoscroll'] == 'on' || $settings['autoscroll'] == 1 || $settings['autoscroll'] == '1';

		if ( $autoscroll ) {
			$options_att_arr['autoscroll'] = max( 20, (int)$settings['autoscrolldistance'] );
		}

		$mediavine = $settings['mediavine'] == 'true' || $settings['mediavine'] == 'on' || $settings['mediavine'] === true;
		if ( $mediavine ) {
			$options_att_arr['mediavine'] = true;
		}

		if ( isset( $settings['feed_avatars'] ) ) {
			$options_att_arr['avatars'] = $settings['feed_avatars'];
		}

		$disablelightbox = $settings['disablelightbox'] == 'true' || $settings['disablelightbox'] == 'on' || $settings['disablelightbox'] == 1 || $settings['disablelightbox'] == '1';
		if ( $disablelightbox ) {
			$options_att_arr['disablelightbox'] = true;
		} else {
			$lightboxcomments = $settings['lightboxcomments'] == 'true' || $settings['lightboxcomments'] == 'on' || $settings['lightboxcomments'] == 1 || $settings['lightboxcomments'] == '1';
			if ( $lightboxcomments ) {
				$options_att_arr['lightboxcomments'] = max( 1, (int)$settings['numcomments'] );
			}
		}

		$captionlinks = $settings['captionlinks'] == 'true' || $settings['captionlinks'] == 'on' || $settings['captionlinks'] == 1 || $settings['captionlinks'] == '1';
		if ( $captionlinks ) {
			$options_att_arr['captionlinks'] = true;
		}

		// disable mobile refers to disabling a different mobile layout from desktop
		$disable_mobile = $settings['disablemobile'];
		( $disable_mobile == 'on' || $disable_mobile == 'true' || $disable_mobile == true ) ? $disable_mobile = true : $disable_mobile = false;
		if( $settings[ 'disablemobile' ] === 'false' ) $disable_mobile = '';
		if ( $disable_mobile !== true && $settings['colsmobile'] !== 'same' ) {
			$colsmobile = (int)( $settings['colsmobile'] ) > 0 ? (int)$settings['colsmobile'] : 'auto';
		} else {
			$colsmobile = (int)( $settings['cols'] ) > 0 ? (int)$settings['cols'] : 4;
		}
		$options_att_arr['colsmobile'] = $colsmobile;

		if ( ! empty( $settings['captionsize'] ) ) {
			$options_att_arr['captionsize'] = (int)$settings['captionsize'];
		}

		if ( ! empty( $settings['captionlength'] ) ) {
			$options_att_arr['captionlength'] = (int)$settings['captionlength'];
		}

		if ( ! empty( $settings['cache_all'] ) ) {
			$options_att_arr['cache_all'] = true;
		}

		$moderation_mode = isset( $settings['doingModerationMode'] );
		if ( $moderation_mode ) {
			$mod_index = isset( $_GET['sbi_moderation_index'] ) ? sanitize_text_field( substr( $_GET['sbi_moderation_index'], 0, 10 ) ) : '0';
			$options_att_arr['modindex'] = $mod_index;


			if ( ! empty( $settings['whitelist'] ) ) {
				$white_list_name = $settings['whitelist'];
				$white_list_ids = ! empty( $settings['whitelist'] ) ? get_option( 'sb_instagram_white_lists_'.$settings['whitelist'], array() ) : false;
				$options_att_arr['whiteListName'] = $white_list_name;
				$options_att_arr['whiteListIDs'] = $white_list_ids;
			}


			$hide_photos = ! empty( $settings['hidephotos'] ) ? explode( ',', str_replace( ' ', '', $settings['hidephotos'] ) ) : array();
			if ( ! empty( $hide_photos ) ) {
				$options_att_arr['hidePhotos'] = $hide_photos;
			}
		}

		if ( $settings['addModerationModeLink'] ) {
			$options_att_arr['moderationLink'] = true;
		}

		$other_atts .= ' data-options="'.esc_attr( wp_json_encode( $options_att_arr ) ).'"';

		return $other_atts;
	}

	public function maybe_offset_posts( $offset ) {
		if ( empty( $offset ) ) {
			return;
		}

		$post_data = $this->get_post_data();

		if ( ! empty( $post_data ) ) {
			$post_data = array_slice( $post_data, $offset );

			$this->set_post_data( $post_data );
		}

	}


	/**
	 * Creates an array of standard classes to be added to the main feed div.
	 *
	 * @param $settings
	 *
	 * @return array
	 *
	 * @since 5.0
	 */
	protected function add_classes( $settings ) {
		$classes = array();

		$moderation_mode = (isset ( $_GET['sbi_moderation_mode'] ) && $_GET['sbi_moderation_mode'] === 'true' && current_user_can( 'edit_posts' ));

		if ( $moderation_mode ) {
			$classes[] = 'sbi_moderation_mode';
		}
		return array();
	}

	/**
	 * When the feed is loaded with AJAX, the JavaScript for the plugin
	 * needs to be triggered again. This function is a workaround that adds
	 * the file and settings to the page whenever the feed is generated.
	 *
	 * @return string
	 *
	 * @since 5.0
	 */
	public static function get_ajax_page_load_html() {
		$sbi_options = sbi_get_database_settings();
		$font_method = isset( $sbi_options['sbi_font_method'] ) ? $sbi_options['sbi_font_method'] : 'svg';
		$upload = wp_upload_dir();
		$resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( SBI_UPLOADS_NAME );
		$br_adjust = isset( $sbi_options['sbi_br_adjust'] ) && ($sbi_options['sbi_br_adjust'] == 'false' || $sbi_options['sbi_br_adjust'] == '0' || $sbi_options['sbi_br_adjust'] == false) ? false : true;

		$js_options = array(
			'font_method' => $font_method,
			'placeholder' => trailingslashit( SBI_PLUGIN_URL ) . 'img/placeholder.png',
			'resized_url' => $resized_url,
			'br_adjust' => $br_adjust
		);
		if ( isset( $sbi_options['sb_instagram_disable_mob_swipe'] ) && $sbi_options['sb_instagram_disable_mob_swipe'] ) {
			$js_options['no_mob_swipe'] = true;
		}

		$encoded_options = wp_json_encode( $js_options );

		$js_option_html = '<script type="text/javascript">var sb_instagram_js_options = ' . $encoded_options . ';</script>';
		$js_option_html .= "<script type='text/javascript' src='" . trailingslashit( SBI_PLUGIN_URL ) . 'js/sb-instagram.min.js?ver=' . SBIVER . "'></script>";

		return $js_option_html;
	}

	/**
	 * Can trigger a second attempt at getting posts from the API
	 *
	 * @param string $type
	 * @param array $connected_account_with_error
	 * @param int $attempts
	 *
	 * @return bool
	 *
	 * @since 2.0/5.1.1
	 */
	protected function can_try_another_request( $type, $connected_account_with_error, $attempts = 0 ) {

		if ( $type !== 'hashtags_recent'
			&& $type !== 'hashtags_top' ) {
			return false;
		}

		if ( (int)$attempts <= 5 ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns a business connected account not among the array of connected accounts already used
	 *
	 * @param string $type
	 * @param array $attempted_connected_accounts
	 *
	 * @return mixed
	 *
	 * @since 2.0/5.1.1
	 */
	protected function get_different_connected_account( $type, $attempted_connected_accounts ) {

		$settings = sbi_get_database_settings();
		$attempted_keys = $attempted_connected_accounts;

		foreach ( $settings['connected_accounts'] as $connected_account_key => $ca_data ) {
			if ( isset( $ca_data['type'] )
			     && $ca_data['type'] === 'business'
			     && ! in_array( (string)$connected_account_key, $attempted_keys, true ) ) {

				return $ca_data;
			}
		}

		return false;
	}

	protected function handle_no_posts_found( $settings = array(), $feed_types_and_terms = array() ) {
		global $sb_instagram_posts_manager;

		$error = '<p><b>' . __( 'No posts found.', 'instagram-feed' ) . '</b>';
		$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
		$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

		if ( $this->one_post_found
		     && (! empty( $settings['includewords'] )
			     || ! empty( $settings['excludewords'] )
			     || ! empty( $settings['whitelist'] )
			     || ! empty( $settings['hidephotos'] )
			     || ! $settings['media'] === 'all') ) {

			if ( current_user_can( $cap ) ) {
				if ( ! empty( $settings['whitelist'] ) ) {
					$error .= '<p>' . __( 'Use <a href="https://smashballoon.com/guide-to-moderation-mode/">moderation mode</a> to add posts to your whitelist.', 'instagram-feed' ) . '</p>';
				} else {
					$error .= '<p>' . __( 'You may be filtering out too many posts or there could be a cache conflict. See <a href="https://smashballoon.com/post-filtering-not-working/">this page</a> for troubleshooting steps.', 'instagram-feed' ) . '</p>';
				}
			} else {
				if ( ! empty( $settings['whitelist'] ) ) {
					$error .= '<p>' . __( 'Use moderation mode to add posts to your whitelist.', 'instagram-feed' ) . '</p>';
				} else {
					$error .= '<p>' . __( 'You may be filtering out too many posts or there could be a cache conflict.', 'instagram-feed' ) . '</p>';
				}
			}

		} else {
			if ( $settings['type'] === 'hashtag' ) {
				if ( $settings['order'] === 'recent' ) {
					$error .= '<p>' . __( 'No posts made to this hashtag within the last 24 hours.', 'instagram-feed' ) . '</p>';
				} else {
					$error .= '<p>' . __( 'No posts made to this hashtag.', 'instagram-feed' ) . '</p>';
				}
			} else {
				$error .= '<p>' . __( 'Make sure this account has posts available on instagram.com.', 'instagram-feed' ) . '</p>';
			}
		}

		if ( current_user_can( $cap ) ) {
			$error .= '<p><a href="https://smashballoon.com/instagram-feed/docs/errors/">' . __( 'Click here to troubleshoot', 'instagram-feed' ) . '</a></p>';
		}

		$sb_instagram_posts_manager->add_frontend_error( 'noposts', $error );
	}
}