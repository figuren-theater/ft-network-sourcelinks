<?php
/**
 * Main Plugin File for the 'ft-network-sourcelinks'
 * and all related stuff
 *
 * @package ft-network-sourcelinks
 * @version version
 * @author  Carsten Bach
 */

declare(strict_types=1);
namespace Figuren_Theater\Network\Sources;

use Figuren_Theater\inc\EventManager;

use Figuren_Theater\Network\Blocks;
use Figuren_Theater\Network\Options;
use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Taxonomies;


/**
 * Plugin Name:     f.t | NETWORK Sources Management
 * Description:     Manage external Links as 'other' personal profiles or external sources. Handles syncing content from thoose sites, (NOT YET: using RSS-Bridge, friends,) and the old native WordPress Link-Manager a little modified.
 * Plugin URI:      https://figuren.theater
 * Author:          Carsten Bach
 * Author URI:      https://carsten-bach.de
 * Text Domain:     ft-network-sourcelinks
 * Domain Path:     /languages
 * Version:         0.4.0
 *
 * @package         Ft_Network_Sourcelinks
 */

/**
 * This class handles all the major use-cases for external URLs in WordPress
 *
 * 1. Importing your social-media content
 * 2. Show your social-networks in your privacy-statement
 * 3. Prepare the Social-Link(s) Blocks with defaults from your social-networks
 * 4. Setting your URLs as Option of third-party plugins, like for yoasts 'wpseo_social' option
 * 5. Have all links in place for your 'Impressum'
 *
 * and all of the dependencies of thoose workflows, like
 * - a custom post_type to handle the URLs
 */
class Management implements EventManager\SubscriberInterface
{

	/**
	 * [$urls description]
	 *
	 * @var array
	 */
	protected static $urls = [];


	function __construct()
	{
		$this->plugin_dir_path = \plugin_dir_path( __FILE__ );
	}

	/**
	 * Returns an array of hooks that this subscriber wants to register with
	 * the WordPress plugin API.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : Array
	{
		return array(

			'plugins_loaded' => 'enable', //

			// load language files
			// 'init' => ['i18n', 0 ],

			// IS THIS NEEDED SOMEWHERE ???
			// https://github.com/WordPress/gutenberg/issues/36785
			// 'render_block_data' => 'custom_query_block_attributes',



			// TEMP DISABLED, until sync works, read on down the code
			// 'after_setup_theme' => 'enable__on_setup_theme', // working


			// 
			// 'admin_menu' => 'enable__on_admin', //
		);
	}



	public function enable() : void
	{

		\load_plugin_textdomain( 
			'ft-network-sourcelinks', 
			false,
			dirname( \plugin_basename( __FILE__ ) ) . '/languages'
			// $this->plugin_dir_path . '/languages'
		);
		

		// 1. Register our post_type 'ft_link'
		\Figuren_Theater\API::get('PT')->add(
			Post_Types\Post_Type__ft_link::NAME,
			Post_Types\Post_Type__ft_link::get_instance()
		);

		// 2. Re-Use old and existing 'link_category'
		\Figuren_Theater\FT::site()->EventManager->add_subscriber( new Taxonomies\Taxonomy__link_category );

		// 3. Importing your social-media content
		// \Figuren_Theater\FT::site()->EventManager->add_subscriber( new ... );

		// 4. Show your social-networks in your privacy-statement
		// (disabled in favor of Blocks, loaded next)
		// \Figuren_Theater\FT::site()->EventManager->add_subscriber( new LinksListsShortcode() );

		// 5. Prepare the Social-Link(s) Blocks with defaults from your social-networks
		// 5.1 Load some new Blocks
		\Figuren_Theater\FT::site()->EventManager->add_subscriber( new Blocks\Register_Blocks );

		// 5.2 Load some Block Patterns
		\Figuren_Theater\FT::site()->EventManager->add_subscriber( new Blocks\Patterns );



		// 6. Setting your URLs as Options of third-party plugins, like for yoasts 'wpseo_social' option
		// \Figuren_Theater\FT::site()->EventManager->add_subscriber( new OptionsBridge( $this->get_urls() ) );
		\Figuren_Theater\FT::site()->EventManager->add_subscriber( new Options\Preset__wpseo_social );

	}

	/* 			// IS THIS NEEDED SOMEWHERE ???
	public function custom_query_block_attributes( $parsed_block ) {
		if ( 'core/query' === $parsed_block['blockName'] ) {
			// If the block has a `taxQuery` attribute, then find the corresponding cat ID and set the `categoryIds` attribute.
			// TODO: support multiple?
			if ( 
				isset( $parsed_block[ 'attrs' ][ 'query' ][ 'search' ] )
				&&
				strpos( $parsed_block[ 'attrs' ][ 'query' ][ 'search' ], 'link_category:' )
			) {
				// die(var_dump($parsed_block));
				

				#if ( is_string( $parsed_block[ 'attrs' ][ 'query' ][ 'taxQuery' ]['link_category'][0] ) )
				$_link_cat = array_flip( explode(':', $parsed_block[ 'attrs' ][ 'query' ][ 'search' ] ) );
				$tax_term = get_term_by( 
					'slug',
					$_link_cat[0],
					'link_category',
				);
				if ( $tax_term ) {
					$parsed_block[ 'attrs' ][ 'query' ][ 'taxQuery' ]['link_category'] = [ $tax_term->term_id ];
				}
					$parsed_block[ 'attrs' ][ 'query' ][ 'search' ] = '';
			}
		}

		return $parsed_block;
	}*/



	public function i18n()
	{

		\load_plugin_textdomain( 
			'ft-network-sourcelinks', 
			false,
			dirname( \plugin_basename( __FILE__ ) ) . '/languages'
			// $this->plugin_dir_path . '/languages'
		);
		
		/*
		\wp_set_script_translations(
			'figurentheater-figurentheater-production-duration-editor-script',
			'ft-network-sourcelinks',
			\plugin_dir_path( __FILE__ ) . 'languages'
		);

		\wp_set_script_translations(
			'figurentheater-figurentheater-production-premiere-editor-script',
			'ft-network-sourcelinks',
			\plugin_dir_path( __FILE__ ) . 'languages'
		);
		*/
	}

		  

	/**
	 * @todo TEMPORARILY DISABLED
	 *       but needed when sync/importing starts
	 *       so we can assign different post_formats based on the 
	 *       source_links post_format
	 *
	 * 
	 * Note that you must call 'add_theme_support()' before the init hook gets called!
	 *
	 * A good hook to use is the after_setup_theme hook.
	public function enable__on_setup_theme() : void
	{

		$post_formats = \get_post_format_slugs();
		unset( $post_formats['standard'] );
		\add_theme_support( 'post-formats', $post_formats );
	}
	 */

	/*
	public function enable__on_admin() : void
	{
		$this->debug();
	}*/


	public static function get_urls() : Array
	{
		// minimal caching
		if( isset( self::$urls ) && !empty( self::$urls ) )
			return self::$urls;

		$ft_query = \Figuren_Theater\FT_Query::init();

		//
		return self::$urls = $ft_query->find_many_by_type(
			Post_Types\Post_Type__ft_link::NAME,
			'publish',
			[
				'cache_results' => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,

				'suppress_filters' => true,
			]
		);

	}



	public static function init()
	{
		static $instance;

		if ( NULL === $instance ) {
			$instance = new self;
		}

		return $instance;
	}

	protected function debug()
	{

		#		\do_action( 'qm/debug', $this->get_urls() );
		
		#		\do_action( 'qm/info', '{fn}: {value}', [
		#		    'fn' => "get_taxonomy( 'link_category' )",
		#		    'value' => var_export( \get_taxonomy( 'link_category' ), true ),
		#		] );
		#
		
		#		\do_action( 'qm/info', '{fn}: {value}', [
		#		    'fn' => "\get_post_type( 'link' )",
		#		    'value' => var_export( \get_post_type( 'link' ), true ),
		#		] );

	}
}



// instantiate the loader
$loader = new \Figuren_Theater\Psr4AutoloaderClass;
// register the autoloader
$loader->register();
// register the base directories for the namespace prefix
$loader->addNamespace( 'Figuren_Theater', dirname( __FILE__ ) . '/inc', true );





// $management = new Management;
$management = Management::init();

// // 7.4. Register the Manager to our site
// if ( ! is_a( \Figuren_Theater\FT::site()->EventManager, 'EventManager' ))
// 	return;

// if ( ! method_exists( \Figuren_Theater\FT::site()->EventManager, 'add_subscriber'))
// 	return;

\Figuren_Theater\FT::site()->EventManager->add_subscriber( $management );


// runs once, on activation
// 
// after reading a loooong thread at
// https://core.trac.wordpress.org/ticket/14170
// I know now, that we should follow a new path
// because of multisite vs. register_activation_hook
// 
// let us now do this from within the taxonomy,
// when visiting the links edit.php
/*
\register_activation_hook( __FILE__, function(){
	// create 'link_category' taxonomy terms
	
	// create first 'ft_link' using site_url()
	
	// add_option('default_link_category')
	
} );*/
