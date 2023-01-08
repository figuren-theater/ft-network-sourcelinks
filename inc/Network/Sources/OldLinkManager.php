<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Sources;

use Figuren_Theater\inc\EventManager;




class OldLinkManager implements EventManager\SubscriberInterface
{

	/**
	 *
	 */
	const SYNC_CAT_SLUG = 'sync';



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


			'after_setup_theme' => 'enable__on_setup_theme', // working


			'wp_loaded' => ['enable__on_admin', 0 ], // working
			// 'load-link.php' => ['enable__on_admin', 0 ], // too late
			// '_admin_menu' => ['enable__on_admin', 0 ], // partly working
			// 'admin_menu' => ['enable__on_admin', 0 ], // not working

			// Too late
			// 'load-link-manager.php'         => 'enable__on_admin',
			// 'load-edit-link-categories.php' => 'enable__on_admin',



			// edit existing link
			'load-link.php'                 => 'modify_link_metaboxes',
			// add new link
			'load-link-add.php'             => 'modify_link_metaboxes',


			// set post_formats on links
			'edit_link' => 'set_post_format_on_links',
			'add_link'  => 'set_post_format_on_links',
		);
	}



	/**
	 * Note that you must call 'add_theme_support()' before the init hook gets called!
	 *
	 * A good hook to use is the after_setup_theme hook.
	 */
	public function enable__on_setup_theme() : void
	{

		// \add_post_type_support( 'link', 'post-formats' );

		$post_formats = \get_post_format_slugs();
		unset( $post_formats['standard'] );
		\add_theme_support( 'post-formats', $post_formats );
	}


	public function enable__on_admin() : void
	{
#		if (is_admin())
#			return;

		\add_filter( "pre_option_link_manager_enabled", '__return_true' );



		$this->debug();
	}


	/**
	 * [register_taxonomy_args description]
	 * @param  array  $args        [description]
	 * @param  string $taxonomy    [description]
	 * @param  array  $object_type [array of strings]
	 * @return [type]              [description]
	 */
	public function register_taxonomy_args( array $args, string $taxonomy, array $object_type )
	{

#	    if ( 'post-formats' === $taxonomy ) {
#		    // Set new pseudo-post_type
#		    if ( isset( $args['object_type'] ) && is_array( $args['object_type'] ) ) {
#		    	// code...
#		    	$args['object_type'][] = 'link';
#		    } else {
#		    	$args['object_type'] = ['link'];
#
#		    }
#
#	        return $args;
#	    }

	    // Target "link_category"
	    if ( 'link_category' === $taxonomy ) {
		    // Set Hierarchical
		    $args['hierarchical'] = true;

	        return $args;
	    }

	    // Return
	    return $args;
	}


	public function modify_link_metaboxes() : void
	{
		\remove_meta_box( 'linktargetdiv', null, 'normal' );
		\remove_meta_box( 'linkadvanceddiv', null, 'normal' );

		\add_meta_box( 'ft_links_post_formats', __( 'Post Formats' ), [$this, 'ft_links_post_formats_metabox'], null, 'side' );


	}

	public function ft_links_post_formats_metabox()
	{
		?>
		<script>
		jQuery(document).ready(function(jQuery){
		  jQuery("#ft_links_post_formats").addClass("screen-reader-text");
		});
		</script>
		<?php


		global $link_id;


		echo $this->ft__post_format_meta_box( $link_id );


		// we have a link
		if ( is_int( $link_id ) && $link_id > 0  ) {
			// the existing link is in category 'sync'
			if ( $this->is_syncable_source( $link_id ) ) {
				?>
				<script>
				jQuery(document).ready(function(jQuery){
				  jQuery("#ft_links_post_formats").removeClass("screen-reader-text");
				});
				</script>
				<?php
			}
		}

		?>
		<script>
		jQuery(document).ready(function(jQuery){
		  // jQuery("#ft_links_post_formats").addClass("screen-reader-text");
		  jQuery("#categorychecklist input").on("click", function(e) {
		  	// console.log();
		  	if ( ' Sync' === e.currentTarget.nextSibling.data) {
		  		jQuery("#ft_links_post_formats").toggleClass("screen-reader-text");
		  	}
		  });
		});
		</script>
		<?php
	}



	public function ft__post_format_meta_box( $link_id = null ) {
	    // if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) ) :
	    if ( current_theme_supports( 'post-formats' ) ) :
	        $post_formats = get_theme_support( 'post-formats' );

	        if ( is_array( $post_formats[0] ) ) :
	            // $post_format = get_post_format( $post->ID );
	            $post_format = get_post_format( $link_id );
	            if ( ! $post_format ) {
	                $post_format = '0';
	            }
	            // Add in the current one if it isn't there yet, in case the current theme doesn't support it.
	            if ( $post_format && ! in_array( $post_format, $post_formats[0], true ) ) {
	                $post_formats[0][] = $post_format;
	            }
	            ?>
		        <div id="post-formats-select">
		        <fieldset>
		            <legend class="screen-reader-text"><?php _e( 'Post Formats' ); ?></legend>
		            <input type="radio" name="post_format" class="post-format" id="post-format-0" value="0" <?php checked( $post_format, '0' ); ?> /> <label for="post-format-0" class="post-format-icon post-format-standard"><?php echo get_post_format_string( 'standard' ); ?></label>
		            <?php foreach ( $post_formats[0] as $format ) : ?>
		            <br /><input type="radio" name="post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" <?php checked( $post_format, $format ); ?> /> <label for="post-format-<?php echo esc_attr( $format ); ?>" class="post-format-icon post-format-<?php echo esc_attr( $format ); ?>"><?php echo esc_html( get_post_format_string( $format ) ); ?></label>
		            <?php endforeach; ?>
		        </fieldset>
		    </div>
		            <?php
		    endif;
		endif;
	}


	public function set_post_format_on_links( int $link_id ) : void
	{
#die(var_export( [ $_POST, $_GET ] ));
/*		\do_action( 'qm/info', '{fn}: {value}', [
		    'fn' => "$_POST",
		    'value' => var_export( $_POST ),
		] );*/

		if ( isset( $_POST['post_format'] ) ) {
			if ( 0 === $_POST['post_format'] ) {
				// default post-format
				// OR
				// remove sync
			} else {

				//
				$_the_sync_term = \get_term_by( 'slug', self::SYNC_CAT_SLUG, 'link_category' );

\do_action( 'qm/warning', '{fn}: {value}', [
    'fn' => "$_the_sync_term",
    'value' => var_export( $_the_sync_term ),
] );
				$the_pf_term = \get_term_by( 'slug', $_POST['post_format'], 'link_category' );

\do_action( 'qm/warning', '{fn}: {value}', [
    'fn' => "$the_pf_term",
    'value' => var_export( $the_pf_term ),
] );
				if ( false === $the_pf_term ) {

					// create new taxonomy term
					// to relate to
					$the_pf_term_id = \wp_insert_term(
						\get_post_format_string( $_POST['post_format'] ),
						'link_category',
						[
							'parent' => $_the_sync_term->term_id,
							'slug' => $_POST['post_format'],
						]
					);

					// save post-format as term of link
					\wp_set_object_terms( $link_id, $the_pf_term_id, 'link_category', true );

					// remove the relation to other child-terms of 'sync', which where previous post-formats
					\wp_remove_object_terms(
						$link_id,
						\get_term_children(
							$_the_sync_term->term_id,
							'link_category'
						),
						'link_category'
					);

				} else {
					// remove old terms

					// and add new

				}

			}

		}

	}


	protected function is_syncable_source( int $link_id ) : bool
	{
		if ( 0 < $link_id ) {
			return (bool) \is_object_in_term( $link_id, 'link_category', [ self::SYNC_CAT_SLUG ] );
		}
		return false;
	}





	protected function debug()
	{

		// \do_action( 'qm/debug', \get_option('link_manager_enabled') );
		\do_action( 'qm/info', '{fn}: {value}', [
		    'fn' => "get_option('link_manager_enabled')",
		    'value' => \get_option('link_manager_enabled'),
		] );
		\do_action( 'qm/info', '{fn}: {value}', [
		    'fn' => "current_user_can( 'manage_links' )",
		    'value' => \current_user_can( 'manage_links' ),
		] );
#		\do_action( 'qm/info', '{fn}: {value}', [
#		    'fn' => "\get_post_type( 'link' )",
#		    'value' => var_export( \get_post_type( 'link' ), true ),
#		] );
		\do_action( 'qm/info', '{fn}: {value}', [
		    'fn' => "get_taxonomy( 'post-formats' )",
		    'value' => var_export( \get_taxonomy( 'post-formats' ), true ),
		] );
		\do_action( 'qm/info', '{fn}: {value}', [
		    'fn' => "get_taxonomy( 'link_category' )",
		    'value' => var_export( \get_taxonomy( 'link_category' ), true ),
		] );


	}
}





