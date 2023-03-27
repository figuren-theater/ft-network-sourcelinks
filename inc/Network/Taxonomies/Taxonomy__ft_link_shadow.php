<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Taxonomies;

use Figuren_Theater;
use Figuren_Theater\inc\EventManager;

use Figuren_Theater\Network\Post_Types;

use Figuren_Theater\Data;


/**
 * 
 */
class Taxonomy__ft_link_shadow extends Taxonomy__Abstract implements EventManager\SubscriberInterface
{

	/**
	 * Our growing up post_type
	 */
	const NAME = 'ft_link_shadow';


	/**
	 * Returns an array of hooks that this subscriber wants to register with
	 * the WordPress plugin API.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : Array
	{
		return array(

			/**
			 * Filters the arguments for registering a post type.
			 *
			 * @see https://developer.wordpress.org/reference/hooks/register_taxonomy_args/
			 */
			// 'register_taxonomy_args' => ['register_taxonomy_args', 20, 3],

			'manage_edit-'.static::NAME.'_columns' => 'manage_columns',
	

		);
	}





	/**
	 * [register_taxonomy_args description]
	 *
	 * @param  array  $args        [description]
	 * @param  string $taxonomy    [description]
	 * @param  array  $object_type [array of strings]
	 * @return [type]              [description]
	 */
	/*public function register_taxonomy_args( array $args, string $taxonomy, array $object_type )
	{

		// Target "link_category"
		if ( self::NAME !== $taxonomy )
			return $args;

		return $args;
	}*/


	public function manage_columns( array $columns ) : array {

		if ( false === \get_taxonomy(self::NAME)->rewrite )
			unset( $columns['slug'] );

		return $columns;
	}



	protected function prepare_tax() : void
	{
		// Register shadow connection between this taxonomy and post_type
		$ft_link__TAX_shadow = new TAX_Shadow( $this::NAME, Post_Types\Post_Type__ft_link::NAME );
		Figuren_Theater\FT::site()->EventManager->add_subscriber( $ft_link__TAX_shadow ); 
	}


	public function prepare_post_types() : array
	{
		return $this->post_types = [
			'post',
			Post_Types\Post_Type__ft_link::NAME,
			Data\Feed_Pull::FEED_POSTTYPE,
			// 'event',
			// 'ft_job',
		];
	}


	protected function prepare_labels() : array
	{
		return $this->labels = [
			# Override the base names used for labels:
			'singular' => __('Import Source','figurentheater'),
			'plural'   => __('Import Sources','figurentheater'),
			'slug'     => '' #TODO
		];
	}

	public function register_taxonomy__default_args() : array
	{
		return [
			'label'              => $this->labels['plural'], // fallback
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'hierarchical'       => false,
			'show_tagcloud'      => false,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_in_quick_edit' => false,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => false,
			#'capabilities' => array(
			#		'manage_terms'  =>   'manage_'.$this->tax,
			#		'manage_terms'  =>   'manage_categories',
			#	'manage_terms'  =>   'manage_sites',
			#	'edit_terms'    =>   'edit_' . $this::NAME, // this should only be done by the CRON
			#	'delete_terms'  =>   'delete_' . $this::NAME, // this should only be done by the CRON
			#		'assign_terms'  =>   'assign_'.$this->tax,
			#	'assign_terms'  =>   'edit_posts',
			#),
		];

	}


	/**
	 * Default arguments for custom taxonomies
	 * Several of these differ from the defaults in WordPress' register_taxonomy() function.
	 * 
	 * https://github.com/johnbillion/extended-cpts/wiki/Registering-taxonomies#default-arguments-for-custom-taxonomies
	 */
	protected function register_extended_taxonomy__args() : array {
		return [
			# Use radio buttons in the meta box for this taxonomy on the post editing screen:
			// 'meta_box' => 'simple', //KEEP DISABLED // triggers JS problems in Gutenberg when editing 'ft_production'

			# Show this taxonomy in the 'At a Glance' dashboard widget:
			'dashboard_glance' => false,

			# Add a custom column to the admin screen:
			#'admin_cols' => [
			#	'updated' => [
			#		'title'       => 'Updated',
			#		'meta_key'    => 'updated_date',
			#		'date_format' => 'd/m/Y'
			#	],
			#],
		];
	}



} // END Class Taxonomy__ft_link_shadow
