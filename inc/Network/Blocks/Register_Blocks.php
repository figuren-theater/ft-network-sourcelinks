<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Blocks;

use Figuren_Theater\inc\EventManager;

use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Sources;
use Figuren_Theater\Network\Taxonomies;



/**
 * 
 */
class Register_Blocks implements EventManager\SubscriberInterface
{


	const STATIC_BLOCKS = [
		'social-links',
	];

	const DYNAMIC_BLOCKS = [
		'filtered-links',
	];

	const blocks_assets = [
		// '',
	];

	function __construct()
	{
		$management = Sources\Management::init();

		$this->plugin_dir_path   = $management->plugin_dir_path;
		$this->abs_path_to_build = $management->plugin_dir_path . 'build/';
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

			'init' => ['init', 0],
			
			// https://developer.wordpress.org/reference/hooks/enqueue_block_assets/
			// 'enqueue_block_assets' => 'enqueue_assets',
			
			// TEMP disabled, for not needed right now
			// 'enqueue_block_editor_assets' => 'enqueue_assets',
		);
	}


	public function init() 
	{
		//
		// \add_action( 'init', [ $this, 'i18n' ], 1 );

		// 
		\add_action( 'init', [ $this, 'dynamic_blocks_init' ], 9 );

		//
		\add_action( 'init', [ $this, 'static_blocks_init' ], 10 );

		// 
		\add_action( 'init', [ $this, 'dynamic_blocks_assets' ], 11 );

		// \do_action( 'qm/debug', $this->abs_path_to_build );
	}



	public function i18n()
	{

/*		\load_plugin_textdomain( 
			'ft-network-sourcelinks', 
			false,
			// dirname( \plugin_basename( __FILE__ ) ) . '/languages'
			$this->plugin_dir_path . '/languages'
		);*/
		
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

		$assets = $this->get_assets();
		foreach ( $assets as $asset ) {
			$this->register_asset( $asset );
		}
		*/
	}


	public function static_blocks_init() {

		foreach ( self::STATIC_BLOCKS as $block ) {
			//
			\register_block_type( $this->abs_path_to_build . $block);
			//
			$this->register_asset( $block );
		}
	}




	public function dynamic_blocks_init() {

		foreach ( self::DYNAMIC_BLOCKS as $block ) {
			require( $this->abs_path_to_build . $block . '/index.php' );
		}
	}

	public function dynamic_blocks_assets() {

		foreach ( self::DYNAMIC_BLOCKS as $block ) {
			$this->register_asset( $block );
		}
	}

/*

	public function enqueue_assets()
	{
		foreach ( self::BLOCK_ASSETS as $asset ) {
			$this->enqueue_asset( $asset );
		}
	}


*/

	protected function register_asset( string $asset )
	{
		$dir = $this->abs_path_to_build . "$asset/";

		$script_asset_path = $dir . "$asset.asset.php";
		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				"You need to run `npm start` or `npm run build` for the '$asset' block-asset first."
			);
		}
		$index_js     = "build/$asset.js";
		$script_asset = require( $script_asset_path );

		\wp_register_script( 
			"figurentheater-$asset",
			\plugins_url( $index_js, $this->abs_path_to_build ),
			$script_asset['dependencies'],
			$script_asset['version']
		);


		\wp_set_script_translations(
			"figurentheater-$asset",
			'ft-network-sourcelinks',
			$this->plugin_dir_path . '/languages'
		);
		\wp_set_script_translations(
			"figurentheater-$asset-editor-script",
			'ft-network-sourcelinks',
			$this->plugin_dir_path . '/languages'
		);
	}



	protected function enqueue_asset( string $asset )
	{
		\wp_enqueue_script( "figurentheater-$asset" );
	}




} // END Class Link_Blocks
