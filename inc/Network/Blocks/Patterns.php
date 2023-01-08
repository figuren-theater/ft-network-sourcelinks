<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Blocks;

use Figuren_Theater\inc\EventManager;

use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Taxonomies;



/**
 * 
 */
class Patterns implements EventManager\SubscriberInterface
{

	/**
	 * Returns an array of hooks that this subscriber wants to register with
	 * the WordPress plugin API.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : Array
	{
		return array(

			'init' => 'register_block_patterns',



		);
	}


	public static function register_block_patterns()
	{
		\register_block_pattern(
			'ft-ns-important-links',
			self::important_links_block_pattern()
		);

		\register_block_pattern(
			'ft-ns-important-links-posttemplate',
			self::important_links_block_pattern_posttemplate()
		);

		// \do_action( 'qm/warning', \parse_blocks( self::important_links_block_pattern()['content'] ) );
	}


/*
	protected static function important_links_block_pattern() : Array
	{
		$_ft_link_pt = Post_Types\Post_Type__ft_link::NAME;

		// add new pattern
		return [
			'title'      => __( 'Important Links', 'ft-network-sourcelinks' ),
			'description'=> __( 'Important Links', 'ft-network-sourcelinks' ),
			'categories' => array( 'query' ),
			'blockTypes' => array( 'core/query' ),
			'content'    => '
<!-- wp:query {"query":{"perPage":"25","pages":"1","offset":0,"postType":"'. $_ft_link_pt .'","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"link_category":[]}},"displayLayout":{"type":"list"}} -->
<div class="wp-block-query">

	<!-- wp:post-template -->
	<!-- wp:separator {"color":"primary"} -->
	<hr class="wp-block-separator has-text-color has-background has-primary-background-color has-primary-color"/>
	<!-- /wp:separator -->

	<!-- wp:group {"style":{"spacing":{"padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px"}}},"layout":{"type":"flex","allowOrientation":false,"flexWrap":"wrap","justifyContent":"space-between"},"fontSize":"small"} -->
	<div class="wp-block-group has-small-font-size" style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"lineHeight":"1.5","fontStyle":"italic","fontWeight":"500"},"spacing":{"margin":{"bottom":"0px","top":"0px","right":"0px","left":"0px"}}},"fontSize":"small"} /-->

	<!-- wp:post-content {"layout":{"inherit":false}} /--></div>
	<!-- /wp:group -->
	<!-- /wp:post-template -->
</div>
<!-- /wp:query -->',
		];

	}*/




	protected static function important_links_block_pattern() : Array
	{
		$_ft_link_pt = Post_Types\Post_Type__ft_link::NAME;
		$link_category = Taxonomies\Taxonomy__link_category::NAME;

		// add new pattern
		return [
			'title'      => __( 'Important Links', 'ft-network-sourcelinks' ),
			'description'=> __( 'Important Links', 'ft-network-sourcelinks' ),
			'categories' => array( 'query' ),
			'blockTypes' => array( 'core/query' ),
			'content'    => '<!-- wp:query {"query":{"perPage":"25","pages":"1","offset":0,"postType":"'. $_ft_link_pt .'","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":{"'.$link_category.'":[]}},"displayLayout":{"type":"list"}} -->
<div class="wp-block-query"><!-- wp:pattern {"slug":"ft-ns-important-links-posttemplate"} /-->
</div>
<!-- /wp:query -->',
		];

	}

	protected static function important_links_block_pattern_posttemplate() : Array
	{
		// $_default_link_cat_id = intval( \get_option('default_link_category') );
	
		// add new pattern
		return [
			'title'      => __( 'Important Links Post Template', 'ft-network-sourcelinks' ),
			// 'description'=> __( 'Important Links', 'ft-network-sourcelinks' ),
			// 'categories' => array( 'query' ),
			// 'blockTypes' => array( 'core/post-template' ),
			'content'    => '<!-- wp:post-template -->
<!-- wp:separator {"color":"primary"} -->
<hr class="wp-block-separator has-text-color has-background has-primary-background-color has-primary-color"/>
<!-- /wp:separator -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px"}}},"layout":{"type":"flex","allowOrientation":false,"flexWrap":"wrap","justifyContent":"space-between"},"fontSize":"small"} -->
<div class="wp-block-group has-small-font-size" style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:post-title {"level":4,"isLink":true,"style":{"elements":{"link":{"color":{"text":"var:preset|color|primary"}}},"typography":{"lineHeight":"1.5","fontStyle":"italic","fontWeight":"500"},"spacing":{"margin":{"bottom":"0px","top":"0px","right":"0px","left":"0px"}}},"fontSize":"small"} /-->

<!-- wp:post-content {"layout":{"inherit":false}} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->',
		];

	}




} // END Class Patterns
