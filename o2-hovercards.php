<?php // phpcs:ignore WordPress.Files.FileName.
/**
 * HoverCards for [o2](https://geto2.com/)
 *
 * @package   o2-hovercards
 * @author    imath
 * @license   GPL-2.0+
 * @link      https://imathi.eu
 *
 * @wordpress-plugin
 * Plugin Name:       o2 HoverCards
 * Plugin URI:        https://github.com/imath/o2-hovercards
 * Description:       o2 add-on to bring HoverCards to preview content of external services like Trac tickets or GitHub issues.
 * Version:           1.0.0-beta
 * Author:            imath
 * Author URI:        https://github.com/imath
 * Text Domain:       o2-hovercards
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/o2-hovercards
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'O2_HoverCards' ) ) :
	/**
	 * Main Class
	 *
	 * @since 1.0.0
	 */
	class O2_HoverCards {
		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			$this->inc();
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since 1.0.0
		 */
		public static function start() {

			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Load needed files.
		 *
		 * @since 1.0.0
		 */
		private function inc() {
			$inc_path = plugin_dir_path( __FILE__ ) . 'inc/';

			require $inc_path . 'globals.php';
			require $inc_path . 'functions.php';
			require $inc_path . 'hooks.php';
		}
	}

endif;

/**
 * Start plugin.
 *
 * @since 1.0.0
 *
 * @return O2_HoverCards The main instance of the plugin.
 */
function o2_hovercards() {
	return O2_HoverCards::start();
}
add_action( 'o2_loaded', 'o2_hovercards', 9 );
