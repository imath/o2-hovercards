<?php
/**
 * Functions.
 *
 * @package   o2-hovercards
 * @subpackage \inc\functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load translations.
 *
 * @since 1.0.0
 */
function o2_hovercards_load_textdomain() {
	load_plugin_textdomain(
		'o2-hovercards',
		false,
		trailingslashit( basename( o2_hovercards()->dir ) ) . 'languages'
	);
}

/**
 * Helper function used to add a new service.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *        Arguments to register a new service.
 *
 *        @type string $service  The name of the service being added.
 *                               Required.
 *        @type string $key      The regex pattern used to find the tag for this service.
 *                               Required.
 *        @type string $url      The regex replacement to get the URL of the ticket.
 *                               Required.
 *        @type string $ticket   The regex replacement to get a ticket ID from the tag.
 *                               Required.
 *        @type string $callback The name of the function used to process data before being
 *                               displayed in the hovercard.
 *                               Required.
 * }
 */
function o2_hovercards_add_service( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'service'  => '',
		'key'      => '',
		'url'      => '',
		'ticket'   => '',
		'callback' => '',
	) );

	$required = array_filter( $args );

	// Missing required argument.
	if ( count( $required ) !== count( $args ) ) {
		return false;
	}

	foreach ( $args as $key => $arg ) {
		add_filter( "o2_hovercards_regex_{$key}s", function( $r ) use ( $arg ) {
			$r[] = $arg;
			return $r;
		} );
	}

	add_filter( "o2_hovercards_{$args['service']}", $args['callback'] );

	// Service successfully registered.
	return true;
}

/**
 * Register needed JavaScript and CSS assets.
 *
 * @since 1.0.0
 */
function o2_hovercards_register_assets() {
	$o2hc = o2_hovercards();
	$min  = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {
		$min = '';
	}

	wp_register_script(
		'o2-hovercards',
		$o2hc->js_url . "script{$min}.js",
		array(
			'wp-pointer',
			'hoverIntent',
			'wp-api-request',
		),
		$o2hc->version,
		true
	);

	$style = $o2hc->css_url . "style{$min}.css";

	/**
	 * If you want to override the styles:
	 *
	 * Just put a style.css file into your theme's directory at this location:
	 * `css/o2-hovercard.css`.
	 */
	$custom = get_theme_file_path( 'css/o2-hovercards.css' );
	if ( file_exists( $custom ) ) {
		$style = get_theme_file_uri( 'css/o2-hovercards.css' );
	}

	wp_register_style(
		'o2-hovercards',
		$style,
		array(
			'wp-pointer',
		),
		$o2hc->version
	);
}

/**
 * Enqueue needed JavaScript and CSS assets.
 *
 * @since 1.0.0
 */
function o2_hovercards_enqueue_assets() {
	wp_enqueue_style( 'o2-hovercards' );

	wp_enqueue_script( 'o2-hovercards' );
	wp_localize_script(
		'o2-hovercards',
		'o2HoverCards',
		array(
			'loader'  => admin_url( 'images/spinner-2x.gif' ),
		)
	);
}

/**
 * Get regex for links.
 *
 * @since 1.0.0
 *
 * @param  string $regex
 * @return string
 */
function o2_hovercards_link_regex_map( $regex ) {
	return "/(?<![\\w-])$regex(?![\\w-])/";
}

/**
 * Apply services regex replacements.
 *
 * @since 1.0.0
 *
 * @param  string $content
 * @param  string $match
 * @return string
 */
function o2_hovercards_service_regex( $content, $match ) {
	$find = o2_hovercards()->regex;

	preg_match_all( '#[^>]+(?=<[^/]*[^a])|[^>]+$#', $content, $matches, PREG_SET_ORDER );

	foreach ( $matches as $val ) {
		$content = preg_replace( array_map( 'o2_hovercards_link_regex_map', $find ), $match, $val[0] );
	}

	return $content;
}

/**
 * Grab the tag, process it with regex to find the ID and tag and
 * return processed data as specified by the API.
 *
 * @since 1.0.0
 *
 * @param  string        $slug The service regex key.
 * @return boolean|array       False if no services were found, the service response array otherwise.
 */
function o2_hovercards_get_hovercard_info( $slug = '' ) {
	if ( ! $slug ) {
		return false;
	}

	$o2hc = o2_hovercards();
	$slug = esc_attr( $slug );
	$args = array(
		'id'      => o2_hovercards_service_regex( $slug, $o2hc->tickets ),
		'service' => o2_hovercards_service_regex( $slug, $o2hc->services ),
		'link'    => o2_hovercards_service_regex( $slug, $o2hc->urls ),
	);

	if ( $args['link'] ) {
		$args['url'] = preg_replace( "/\<a href=\"(.*?)\".*?\<\/a\>/", '$1', $args['link'] );
	}

	$service = $args['service'];
	if ( ! $service ) {
		return false;
	}

	/**
	 * Used to get the response to send from the registered service.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args {
	 *        Arguments to register a new service.
	 *
	 *        @type string $id       The parsed ticket ID.
	 *        @type string $service  The name of the registered service.
	 *        @type string $url      The parsed the URL of the ticket.
	 * }
	 */
	return apply_filters( "o2_hovercards_{$service}", $args );
}

/**
 * Limit $content to $length characters while keeping all links active.
 *
 * In order to keep all visible links active, make a new string and run it through
 * `make_clickable`, grab an array of all the links with `preg_match_all`,
 * shorten the string to size = $length, and replace anything that looks like
 * a URL with the links in the array of URLs.
 *
 * @since 1.0.0
 *
 * @param string  $content The content to excerpt.
 * @param integer $length  The length of the content in characters.
 */
function o2_hovercards_truncate( $content = '', $length = 250 ) {
	$linked = make_clickable( $content );

	// if it's already short enough, we're done
	if ( strlen( $content ) < $length ) {
		return $linked;
	}

	// Grab an array of all the anchor tags, then trim it and check for things that look like URLs.
	preg_match_all( '#<a\s+.*?href=[\'"]([^\'"]+)[\'"]\s*(?:title=[\'"]([^\'"]+)[\'"])?.*?>((?:(?!</a>).)*)</a>#i', $linked, $urls);
	$content = substr( $content, 0, $length );

	// The regex is a non-anchored pattern and does not have a single fixed starting character.
	$url_clickable = '~
		([\\s(<.,;:!?])                                    # 1: Leading whitespace, or punctuation.
		(                                                  # 2: URL.
			[\\w]{1,20}+://                                # Scheme and hier-part prefix.
			(?=\S{1,2000}\s)                               # Limit to URLs less than about 2000 characters long.
			[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+         # Non-punctuation URL character.
			(?:                                            # Unroll the Loop: Only allow puctuation URL character if followed by a non-punctuation URL character.
				[\'.,;:!?)]                                # Punctuation URL character.
				[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++     # Non-punctuation URL character.
			)*
		)
		(\)?)                                              # 3: Trailing closing parenthesis (for parethesis balancing post processing).
	~xS';

	// Tell PCRE to spend more time optimizing since, when used on a page load, it will probably be used several times.
	preg_match_all( $url_clickable, $content, $matches );

	// Set up the anchors with the trimmed text, but the pre-trimmed href.
	$replace = array();
	for( $i = 0; $i < count( $matches[2] ); $i++ ) {
		$replace[] = sprintf( "<a href='%s' rel='nofollow'>%s</a>", $urls[1][$i], $matches[2][$i] );
	}

	// Replace anything that looks like a URL with the next anchor in $replace.
	$content = str_replace( $matches[2], $replace, $content );

	return force_balance_tags( $content . ' [&hellip;]' );
}

/**
 * Build the hovercard response for the O2 Read API.
 *
 * @since 1.0.0
 *
 * @return string JSON encoded response.
 */
function o2_hovercards_get_hovercard() {
	$slug = '';

	if ( isset( $_REQUEST[ 'slug' ] ) ) {
		$slug = $_REQUEST[ 'slug' ];
	}

	$hovercard_info = o2_hovercards_get_hovercard_info( $slug );
	if ( ! $hovercard_info ) {
		return o2_API_Base::die_failure(
			'missing_hovercard_info',
			sprintf( __( 'The service for %s was not found into registered services.', 'o2-hovercards' ), $slug )
		);
	}

	$response = wp_parse_args( $hovercard_info, array(
		'title'       => '',
		'url'         => '',
		'subtitle'    => '',
		'description' => '',
		'meta'        => array()
	) );

	// Sanitize content.
	foreach ( array( 'title', 'description' ) as $key ) {
		$response[ $key ] = o2_hovercards_truncate( $response[ $key ] );

		if ( 'description' === $key ) {
			$response[ $key ] = wpautop( $response[ $key ] );
		}
	}

	// Sanitize potential comments.
	if ( isset( $response['comments'] ) && $response['comments'] ) {
		$response['comments'] = (array) $response['comments'];
		foreach ( array( 'comment', 'description' ) as $ckey ) {
			if ( ! isset( $response['comments'][ $ckey ] ) ) {
				continue;
			}

			$response['comments'][ $ckey ] = o2_hovercards_truncate( $response['comments'][ $ckey ] );
		}
	}

	return o2_API_Base::die_success( $response );
}

/**
 * Add a marker to inform JavaScript it needs to display the hovercard.
 *
 * @since 1.0.0
 *
 * @param  string $link The link to "hovercardify".
 * @return string The "hovercardified" link.
 */
function o2_hovercards_add_marker( $link = '' ) {
	return '<span class="o2-hovercardify">' . $link . '</span>';
}

/**
 * Look into the content to find registered services regex.
 *
 * @since 1.0.0
 *
 * @param  string $content The content to look into.
 * @return string          The content, possibly containing some hovercard markers.
 */
function o2_hovercards_markup_links( $content = '' ) {
	$o2hc = o2_hovercards();

	$find = $o2hc->regex;
	$replace = array_map( 'o2_hovercards_add_marker', $o2hc->urls );

	preg_match_all( '#[^>]+(?=<[^/]*[^a])|[^>]+$#', $content, $matches, PREG_SET_ORDER );

	foreach ( $matches as $val ) {
		$content = str_replace( $val[0], preg_replace( array_map( 'o2_hovercards_link_regex_map', $find ), $replace, $val[0] ), $content );
	}

	return $content;
}

/**
 * Output the template used to display hovercards.
 *
 * @since 1.0.0
 */
function o2_hovercards_template() {
	// Have a look in theme to check for template override.
	$template = locate_template( 'o2-hovercards.php', false );

	// Fallback on built-in one.
	if ( ! $template ) {
		$template = trailingslashit( o2_hovercards()->tpl_dir ) . 'index.php';
	}

	include $template;
}
