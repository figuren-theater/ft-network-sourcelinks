<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Options;

use Figuren_Theater\inc\EventManager;


use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Sources;
use Figuren_Theater\Network\Taxonomies;

/**
 *
 * @package         Ft_Network_Sourcelinks
 */

/**
 * This class handles one major use-cases for external URLs in WordPress
 *
 * 4. Setting your URLs as Option of third-party plugins, like for yoasts 'wpseo_social' option
 *
 */
class Preset__wpseo_social implements EventManager\SubscriberInterface
{


	/**
	 * [$urls description]
	 * @var array
	 */
	protected $urls = [];

	protected $query = null;


	// function __construct( Array $urls )
	function __construct()
	{
		$this->query = \Figuren_Theater\FT_Query::init();
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
			// 'admin_menu' => 'enable__on_admin', //
			'pre_option_wpseo_social' => ['pre_option_wpseo_social',100]
		);
	}


	protected function prepare_urls() : Array
	{

		if( !empty( $this->urls ) )
			return $this->urls;

		$management = Sources\Management::init();
		$urls       = $management::get_urls();

		if( empty( $urls ) )
			return []; // we can't do anything with nothing

		$link_category = Taxonomies\Taxonomy__link_category::NAME;

		// get all IDs of 'Links' in our 'Own' 'link_category' now,
		// to prevent multiple DB lookups, depending on the amount of links
		$_social_links = $this->query->find_many_by_type(
			Post_Types\Post_Type__ft_link::NAME,
			'publish',
			[
				'fields' => 'ids',

				// 'update_post_meta_cache' => false,
				// 'update_post_term_cache' => false,

				'tax_query' => array(
					array(
						'taxonomy' => $link_category,
						// 'field'    => 'term_id',
						// 'field'    => 'term_taxonomy_id', // WRONG
						'field'    => 'slug',
						// 'terms'    => intval( \get_option("default_{$link_category}") ),
						'terms'    => Taxonomies\Term__link_category__own::SLUG,
						'include_children' => true,
					),
				),
			]
		);
		if( is_a( $_social_links, 'WP_Error' ) )
			return []; // we can't do anything with nothing

		// prepare output
		$_urls = [];

		// go over each links and transform their content
		// into a (somehow) readable title
		array_walk(
			$urls,
			function( $v, $k ) use ( &$_urls, $_social_links )   {


				// everything till now?
				if( !$v instanceof \WP_Post  )
					return;

				// make sure this is a personell profile
				// and not any arbitary link,
				// to grab content from
				//
				// the 'default_link_category'
				// is used as 'Socials'-kinda-builtin term-holder

				// is_object_in_term creates n-DB singular requests,
				// based on the amount of links to check
				// against the 'default_link_category'
				if ( ! in_array( $v->ID, $_social_links ) )
					return;

				// clean up url as pseudo label
				// so this makes
				// https://instagram.com -> instagram
			
				// @TODO
				// we have this string-cleaning now 3 times
				// 
				// - plugins\ft-network-sourcelinks\src\block-editor\blocks\filtered-links\index.php#L90
				// - plugins\ft-network-sourcelinks\inc\Network\Post_Types\Post_Type__ft_link.php#L330
				// - plugins\ft-network-sourcelinks\inc\Network\Options\Preset__wpseo_social.php#L142
				// 
				// 
				// cleanup html
				// especially from wp_auto_p
				$_url = \wp_kses( $v->post_content, [] );
				// cleanup whitespaces and line-breaks
				$_url = preg_replace('/\s+/', '', $_url );
				// last check
				if ( ! $_url = \esc_url( $_url ) )
					return;


				// get "sub.sub.domainname.tld"
				$_clean_url = parse_url( $_url, PHP_URL_HOST );
#wp_die( var_export([$_clean_url,$_url],true));
				// cut into pieces
				$_clean_url = explode( '.', $_clean_url );
				// strip .TLD
				array_pop($_clean_url);
				// re-glue pieces
				$_clean_url = join( '.', $_clean_url );
				// strip www.
				$_clean_url = str_replace( 'www.', '', $_clean_url );

				// add to output
				$_urls[ $_clean_url ] = $_url;
			}
		);

		return $this->urls = $_urls;
	}


	/**
	 * [pre_option_wpseo_social description]
	 *
	 * 2022.06.08: Added Array|bool for situations´, 
	 *             where 'wpseo_social' is not set and/or not filtered yet.
	 *             Just to prevent fatals ;).
	 *
	 * @package project_name
	 * @version version
	 * @author  Carsten Bach
	 *
	 * @param   bool         $wpseo_social [description]
	 * @return  [type]                     [description]
	 */
	public function pre_option_wpseo_social( Array|bool $wpseo_social ) : Array
	{
		$wpseo_social = ( is_array( $wpseo_social ) ) ? $wpseo_social : [];

		//
		$this->prepare_urls();

		if( empty( $this->urls ) )
			return $wpseo_social;


		$_urls = $this->urls;


		if ( isset( $_urls['facebook'] ) && ( !isset( $wpseo_social['facebook_site'] ) || empty( $wpseo_social['facebook_site'] ))) {
			$wpseo_social['facebook_site'] = $_urls['facebook'];
		}

		if ( isset( $_urls['instagram'] ) && ( !isset( $wpseo_social['instagram_url'] ) || empty( $wpseo_social['instagram_url'] ))) {
			$wpseo_social['instagram_url'] = $_urls['instagram'];
		}

		if ( isset( $_urls['linkedin'] ) && ( !isset( $wpseo_social['linkedin_url'] ) || empty( $wpseo_social['linkedin_url'] ))) {
			$wpseo_social['linkedin_url'] = $_urls['linkedin'];
		}

		if ( isset( $_urls['myspace'] ) && ( !isset( $wpseo_social['myspace_url'] ) || empty( $wpseo_social['myspace_url'] ))) {
			$wpseo_social['myspace_url'] = $_urls['myspace'];
		}

		if ( isset( $_urls['pinterest'] ) && ( !isset( $wpseo_social['pinterest_url'] ) || empty( $wpseo_social['pinterest_url'] ))) {
			$wpseo_social['pinterest_url'] = $_urls['pinterest'];
			// @TODO add handling for post_meta 'pinterestverify'
		}

		if ( isset( $_urls['twitter'] ) && ( !isset( $wpseo_social['twitter_site'] ) || empty( $wpseo_social['twitter_site'] ))) {
			$_urls['twitter'] = untrailingslashit( $_urls['twitter'] );
			$_urls['twitter'] = str_replace( 'https://twitter.com/', '', $_urls['twitter'] );
			$wpseo_social['twitter_site'] = $_urls['twitter'];
		}

		if ( isset( $_urls['youtube'] ) && ( !isset( $wpseo_social['youtube_url'] ) || empty( $wpseo_social['youtube_url'] ))) {
			$wpseo_social['youtube_url'] = $_urls['youtube'];
		}

		if ( isset( $_urls['wikipedia'] ) && ( !isset( $wpseo_social['wikipedia_url'] ) || empty( $wpseo_social['wikipedia_url'] ))) {
			$wpseo_social['wikipedia_url'] = $_urls['wikipedia'];
		}



		// free up some memory
		unset( $_urls );

		// code...
		return $wpseo_social;
	}



	public function enable__on_admin() : void
	{
		// $this->debug();
	}


	protected function debug()
	{
		\do_action( 'qm/debug', $this->urls );
		\do_action( 'qm/debug', \get_option('wpseo_social') );
	}
}
