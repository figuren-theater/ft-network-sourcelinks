<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Taxonomies;

use Figuren_Theater\inc\EventManager;

use Figuren_Theater\Network\Post_Types;



/**
 * 
 */
class Taxonomy__link_category extends Taxonomy__Abstract implements EventManager\SubscriberInterface
{

	/**
	 * Our growing up post_type
	 */
	const NAME = 'link_category';


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
			'register_taxonomy_args' => ['register_taxonomy_args', 20, 3],

			'admin_footer-edit-tags.php' => 'css_helper',
			'admin_footer-term.php'      => 'css_helper',

			'manage_edit-link_category_columns' => 'manage_columns',
	
			// prevent this tax from being overwritten by distribution
			// because this is THE LONG WAY problem
			// 'dt_syncable_taxonomies' => ['dt_syncable_taxonomies', 10, 2 ],

			// Add our menu-Icon to the 'At a Glance' Dashboard Widget
			// 'admin_head-index.php' => 'show_icon_at_a_glance',
			
			// Create initial terms and first 'ft_link'
			// kinda register_activation_hook 
			// with multisite in mind
			'load-post-new.php'  => 'is_activation_needed',
			'load-edit.php'      => 'is_activation_needed',
			'load-edit-tags.php' => 'is_activation_needed',
			

			// 'Figuren_Theater\Network\Setup\insert_first_content'              => 'activation',
			'Figuren_Theater\Onboarding\Sites\Installation\insert_first_content' => 'activation',
			'Figuren_Theater\Network\Taxonomies\link_category\needs_activation'  => 'activation',

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
	public function register_taxonomy_args( array $args, string $taxonomy, array $object_type )
	{

		// Target "link_category"
		if ( self::NAME !== $taxonomy )
			return $args;

		// $args['rewrite'] = true; // DEBUG ONLY

		// Set Hierarchical
		$args['hierarchical'] = true;

		//
		$args['show_admin_column'] = true;

		//
		$args['show_in_rest'] = true;

		//
		unset( $args['cap'] );
		$args['capabilities'] = [
			'manage_terms' => 'manage_sites', // use this to prevent our users from accidents
			'edit_terms'   => 'manage_sites', // use this to prevent our users from accidents
			'delete_terms' => 'manage_sites', // use this to prevent our users from accidents
			'assign_terms' => 'edit_posts',
		];

		//
		$args['show_tagcloud'] = false;

		//
		$args['show_in_quick_edit'] = false;

		// Set new pseudo-post_type
		// and remove old 'link' object type
		unset( $args['object_type'] );
			// if ( isset( $args['object_type'] ) && is_array( $args['object_type'] ) ) {
			// 	// code...
			// 	$args['object_type'][] = Post_Types\Post_Type__ft_link::NAME;
			// } else {
			$args['object_type'] = [ Post_Types\Post_Type__ft_link::NAME ];
			// }

		//
		$Term__link_category__own = new Term__link_category__own;
		$args['default_term'] = [
			$Term__link_category__own->name,
				// rewriting is disabled, so this could be empty 
			$Term__link_category__own::SLUG, 
				// BUT the slug is heavily needed to query for ft_link(s) 
				// without knowing the term_ids of the relevant link_category
			$Term__link_category__own->description
		];

		return $args;
	}


	public function manage_columns( Array $columns ) : Array
	{
		if ( false === \get_taxonomy(self::NAME)->rewrite )
			unset( $columns['slug'] );

		return $columns;
	}

	public function css_helper()
	{
		if ( self::NAME !== \get_current_screen()->taxonomy )
			return;

		if ( false !== \get_taxonomy(self::NAME)->rewrite )
			return;

		echo "<style>
			/* helper for non-rewritten post_types,
			   to hide term-slug fields, without compromising WP-native JS events
			*/
			.form-field.term-slug-wrap * {
				max-height: 0;
				height: 0;
				line-height: 0;
				font-size: 0;
				min-height: 0px;
				border: 0 transparent;
				background: transparent;
			}</style>";
	}



	/**
	 * Fires in head section for a specific admin page.
	 *
	 * The dynamic portion of the hook name, `$hook_suffix`, refers to the hook suffix
	 * for the admin page.
	 *
	 * @since WP 2.1.0
	 */
	public function is_activation_needed()
	{
		// global $typenow, $taxnow;
		global $typenow;

		if ( 
			Post_Types\Post_Type__ft_link::NAME !== $typenow
			// ||
			// self::NAME !== $taxnow
		)
			return;

		// should be the same as in 
		// T:\figuren\htdocs\wp\wp-admin\includes\class-wp-terms-list-table.php#L115
		$args = array(
			'taxonomy'   => self::NAME,
			'search'     => '',
			'page'       => 0,
			'number'     => 20,
			'hide_empty' => 0,
		);

#		\do_action( 'qm/debug', 'is_activation_needed happened!' );
		// \do_action( 'qm/debug', \get_terms( $args) );
		$link_categories = \get_terms( $args);
		$_action = __NAMESPACE__.'\\'.self::NAME.'\\needs_activation';
		if ( empty( $link_categories ) ){
#					\do_action( 'qm/debug', $_action );
					\do_action( $_action );
		}
		// \do_action( 'qm/debug', \get_option( 'default_term_'.self::NAME ) );

	}


	public function activation()
	{
		// 1. create 'link_category' taxonomy terms

		/**
		 * Add a new term to the database.
		 *
		 * A non-existent term is inserted in the following sequence:
		 * 1. The term is added to the term table, then related to the taxonomy.
		 * 2. If everything is correct, several actions are fired.
		 * 3. The 'term_id_filter' is evaluated.
		 * 4. The term cache is cleaned.
		 * 5. Several more actions are fired.
		 * 6. An array is returned containing the `term_id` and `term_taxonomy_id`.
		 *
		 * If the 'slug' argument is not empty, then it is checked to see if the term
		 * is invalid. If it is not a valid, existing term, it is added and the term_id
		 * is given.
		 *
		 * If the taxonomy is hierarchical, and the 'parent' argument is not empty,
		 * the term is inserted and the term_id will be given.
		 *
		 * Error handling:
		 * If `$taxonomy` does not exist or `$term` is empty,
		 * a WP_Error object will be returned.
		 *
		 * If the term already exists on the same hierarchical level,
		 * or the term slug and name are not unique, a WP_Error object will be returned.
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @since 2.3.0
		 *
		 * @param string       $term     The term name to add.
		 * @param string       $taxonomy The taxonomy to which to add the term.
		 * @param array|string $args {
		 *     Optional. Array or query string of arguments for inserting a term.
		 *
		 *     @type string $alias_of    Slug of the term to make this term an alias of.
		 *                               Default empty string. Accepts a term slug.
		 *     @type string $description The term description. Default empty string.
		 *     @type int    $parent      The id of the parent term. Default 0.
		 *     @type string $slug        The term slug to use. Default empty string.
		 * }
		 * @return array|WP_Error {
		 *     An array of the new term data, WP_Error otherwise.
		 *
		 *     @type int        $term_id          The new term ID.
		 *     @type int|string $term_taxonomy_id The new term taxonomy ID. Can be a numeric string.
		 * }
		 */
		$Term__link_category__own = $this->insert_term( new Term__link_category__own );
		// \do_action( 'qm/debug', $Term__link_category__own );
		$Term__link_category__imprint = $this->insert_term( new Term__link_category__imprint );
		$this->insert_term( new Term__link_category__privacy );

		
		// 2. create option('default_link_category')	
		if ( ! is_a( $Term__link_category__own, 'WP_Error' ) && isset($Term__link_category__own['term_id']) )
		{
			$_default_term_id = (int) $Term__link_category__own['term_id'];
			$update_option = \update_option( 
				'default_term_'.self::NAME, 
				$_default_term_id,
				false
			);
			// \do_action( 'qm/debug', $update_option );
		}
		

		// 3. create first 'ft_link' using site_url()
		$first_link = new Post_Types\Post_Type__ft_link( [
			'new_post_title' => \get_bloginfo( 'name' ),
			'new_post_content' => \get_site_url(),
		] );
		$first_link_id = \wp_insert_post( $first_link->get_post_data() );
		if ( is_int( $first_link_id  ) )
		{
			if ( ! is_a( $Term__link_category__imprint, 'WP_Error' ) && isset($Term__link_category__imprint['term_id']) )
			{
				$_imprint_term_id = (int) $Term__link_category__imprint['term_id'];
				\wp_set_object_terms( $first_link_id, [ $_default_term_id, $_imprint_term_id ], self::NAME );
			}
		}


		// 4. redirect to current_screen
		// to make additions visible
		if ( strpos( \current_action(), 'needs_activation') && \wp_redirect( \add_query_arg( ['ft_link_category_setup_done',''] ) ) )
			exit;

	}

	protected function insert_term( $term )
	{
		

		return \wp_insert_term( 
			$term->name, 
			self::NAME, 
			[
				'slug' => $term::SLUG,
				'description' => $term->description,
			]
		);
	}

} // END Class Taxonomy__link_category
