<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Sources;

use Figuren_Theater\inc\EventManager;


use Figuren_Theater\Network\Post_Types;

/**
 *
 * @package         Ft_Network_Sourcelinks
 */

/**
 * This class handles one major use-cases for external URLs in WordPress
 *
 * 2. Show your social-networks in your privacy-statement
 *
 */
class LinksListsShortcode implements EventManager\SubscriberInterface
{

	const TAG = 'ft_links_list';


	/**
	 * Returns an array of hooks that this subscriber wants to register with
	 * the WordPress plugin API.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : Array
	{
		return array(

			'init' => 'register_shortcodes',

			'admin_menu' => 'enable__on_admin', //

		);
	}
	public function register_shortcodes() : void
	{
		//
		\add_shortcode( self::TAG, [$this, 'shortcode__ft_links_list' ] );
	}

	public function enable__on_admin() : void
	{
		// $this->debug();
	}


	public static function shortcode__ft_links_list( Array|String $atts, String|null $content, String $shortcode_tag )
	{
	    // Init our ft sources manager
		$management = Management::init();
		// get ft_link posts doing a 'normal' query
		$urls = $management::get_urls();
		// sort ASC by URL
		$urls = \wp_list_sort( $urls, 'post_content' );

	    //
	    $atts = \shortcode_atts( array(
	        // 'foo' => 'no foo',
	        // 'baz' => 'default baz'
	    ), $atts, $shortcode_tag );

	    $output  = "<!-- wp:list -->\n<ul>\n";
		foreach ($urls as $link ) {
			// code...
			$output .= "<li>" . Post_Types\Post_Type__ft_link::get_readable_link( $link ) . "</li>\n";
		}

	   	$output .= "</ul>\n<!-- /wp:list -->\n";
		return $output;
	}






	protected function debug()
	{

		\do_action( 'qm/debug', \do_shortcode( '[ft_links_list]' ) );

	}
}
