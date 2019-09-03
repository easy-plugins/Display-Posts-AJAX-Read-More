<?php
/**
 * @package   Easy Plugins: Display Posts - AJAX Read More
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2019 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Easy Plugins: Display Posts - AJAX Read More
 * Plugin URI:        https://connections-pro.com/
 * Description:       An extension for the Easy Plugins: Display Posts plugin which adds support for loading entire post in place when clicking the read more link.
 * Version:           1.0
 * Author:            Steven A. Zahm
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       display-posts-shortcode-ajax-read-more-search
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Display_Posts_AJAX_Read_More' ) ) {

	final class Display_Posts_AJAX_Read_More {

		const VERSION = '1.0';

		/**
		 * @var Display_Posts_AJAX_Read_More Stores the instance of this class.
		 *
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * @var string The absolute path this this file.
		 *
		 * @since 1.0
		 */
		private $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @since 1.0
		 */
		private $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @since 1.0
		 */
		private $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @since 1.0
		 */
		private $basename = '';

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @since 1.0
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * The main plugin instance.
		 *
		 * @since 1.0
		 *
		 * @return self
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

				self::$instance = $self = new self;

				$self->file     = __FILE__;
				$self->url      = plugin_dir_url( $self->file );
				$self->path     = plugin_dir_path( $self->file );
				$self->basename = plugin_basename( $self->file );

				$self->hooks();
				//$self->registerJavaScripts();
			}

			return self::$instance;

		}

		/**
		 * @since 1.0
		 */
		private function hooks() {

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueueScript' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'registerJavaScripts' ) );
			add_filter( 'display_posts_shortcode_args', array( __CLASS__, 'shortcodeArgs' ), 10, 2 );
			add_filter( 'display_posts_shortcode_output', array( __CLASS__, 'addPostID' ), 10, 11 );
		}

		/**
		 * @since 1.0
		 */
		public function getPath() {

			return $this->path;
		}

		/**
		 * @since 1.0
		 */
		public function getURL() {

			return $this->url;
		}

		/**
		 * Callback for the `wp_enqueue_scripts` action.
		 *
		 * @since 1.0
		 */
		public static function enqueueScript() {

			$debug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;

			$path = Display_Posts_AJAX_Read_More()->getPath();
			$url  = Display_Posts_AJAX_Read_More()->getURL();

			$min     = $debug ? '' : '.min';
			$version = $debug ? self::VERSION . '-' . filemtime( "{$path}assets/css/public{$min}.css" ): self::VERSION;

			wp_register_style(
				'dps-ajax-read-more',
				"{$url}assets/css/public{$min}.css",
				array(),
				$version
			);
		}

		/**
		 * @since 1.0
		 */
		public static function registerJavaScripts() {

			$debug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;

			$path = Display_Posts_AJAX_Read_More()->getPath();
			$url  = Display_Posts_AJAX_Read_More()->getURL();

			$min     = $debug ? '' : '.min';
			$version = $debug ? self::VERSION . '-' . filemtime( "{$path}assets/js/listing-ajax-read-more{$min}.js" ): self::VERSION;

			wp_register_script(
				'dps-ajax-read-more',
				"{$url}assets/js/listing-ajax-read-more{$min}.js",
				array( 'wp-api-request', 'jquery' ),
				$version,
				TRUE
			);
		}

		/**
		 * @since 1.0
		 *
		 * @return array
		 */
		public function getDefaults() {

			return array(
				'excerpt_more_ajax'  => FALSE,
			);
		}

		/**
		 * @since 1.0
		 *
		 * @param array $untrusted The user defined shortcode attributes.
		 *
		 * @return array
		 */
		public function shortcodeAtts( $untrusted ) {

			$defaults = Display_Posts_AJAX_Read_More()->getDefaults();
			$atts     = shortcode_atts( $defaults, $untrusted, FALSE );

			self::toBoolean( $atts['excerpt_more_ajax'] );

			return $atts;
		}

		/**
		 * Callback for the `display_posts_shortcode_args` filter.
		 *
		 * @since 1.0
		 *
		 * @param array $args          Parsed arguments to pass to WP_Query.
		 * @param array $original_atts Original attributes passed to the shortcode.
		 *
		 * @return array mixed
		 */
		public static function shortcodeArgs( $args, $original_atts ) {

			$options = Display_Posts_AJAX_Read_More()->shortcodeAtts( $original_atts );

			if ( TRUE === $options['excerpt_more_ajax'] ) {

				wp_enqueue_script( 'dps-ajax-read-more' );
				wp_enqueue_style( 'dps-ajax-read-more' );
			}

			return $args;
		}

		/**
		 * Callback for the `display_posts_shortcode_output` filter.
		 *
		 * @since 1.0
		 *
		 * @param string $html          The shortcode's HTML output.
		 * @param array  $original_atts Original attributes passed to the shortcode.
		 * @param string $image         HTML markup for the post's featured image element.
		 * @param string $title         HTML markup for the post's title element.
		 * @param string $date          HTML markup for the post's date element.
		 * @param string $excerpt       HTML markup for the post's excerpt element.
		 * @param string $inner_wrapper Type of container to use for the post's inner wrapper element.
		 * @param string $content       The post's content.
		 * @param array  $class         Space-separated list of post classes to supply to the $inner_wrapper element.
		 * @param string $author        HTML markup for the post's author.
		 * @param string $category_display_text
		 *
		 * @return mixed
		 */
		public static function addPostID(
			$html,
			$original_atts,
			$image,
			$title,
			$date,
			$excerpt,
			$inner_wrapper,
			$content,
			$class,
			$author,
			$category_display_text
		) {

			$options = Display_Posts_AJAX_Read_More()->shortcodeAtts( $original_atts );

			if ( TRUE !== $options['excerpt_more_ajax'] ) return $html;

			$id           = get_the_ID();
			$post_id      = "post-{$id}";
			$class[]      = $post_id;
			$post_id_span = "<span id='{$post_id}' style='display: none;' data-post-id='{$id}'></span>";
			$loading      = '<span class="dps-arm-loading-overlay" style="display: none;"><span class="dps-arm-loading"></span></span>';

			$html = '<' . $inner_wrapper . ' class="' . implode( ' ', $class ) . '">' . $post_id_span . $loading . $image . $title . $date . $author . $category_display_text . $excerpt . $content . '</' . $inner_wrapper . '>';

			return $html;
		}

		/**
		 * Converts the following strings: yes/no; true/false and 0/1 to boolean values.
		 * If the supplied string does not match one of those values the method will return NULL.
		 *
		 * @since 1.0
		 *
		 * @param string|int|bool $value
		 *
		 * @return bool
		 */
		public static function toBoolean( &$value ) {

			// Already a bool, return it.
			if ( is_bool( $value ) ) return $value;

			$value = filter_var( strtolower( $value ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

			if ( is_null( $value ) ) {

				$value = FALSE;
			}

			return $value;
		}
	}

	/**
	 * @since 1.0
	 *
	 * @return Display_Posts_AJAX_Read_More
	 */
	function Display_Posts_AJAX_Read_More() {

		return Display_Posts_AJAX_Read_More::instance();
	}

	Display_Posts_AJAX_Read_More();
}
