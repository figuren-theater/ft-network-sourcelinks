<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Post_Types;

use Figuren_Theater\inc\EventManager;

// use Figuren_Theater\Network\Features;
use Figuren_Theater\Network\Taxonomies;
use Figuren_Theater\Network\Users;

use WP_Post;

/**
 * Responsible for registering the 'ft_link' post_type
 */

/**
 * We need this post_type on every site,
 *
 */
class Post_Type__ft_link extends Post_Type__Abstract implements EventManager\SubscriberInterface, Post_Type__CanCreatePosts__Interface
{

	/**
	 * Our growing up post_type
	 */
	const NAME = 'ft_link';

	const SLUG = '';

	/**
	 * The Class Object
	 */
	static private $instance = null;


	function __construct($arguments = null)
	{
		$this->arguments = ( $arguments ) ? $arguments : [];
	}

	/**
	 * Returns an array of hooks that this subscriber wants to register with
	 * the WordPress plugin API.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [

			// BACKEND
			// 'edit_form_top'               => 'post_content__metabox', // too early
			'edit_form_after_title'          => 'post_content__metabox',

			'add_meta_boxes_' . static::NAME => 'modify_metaboxes',

			'save_post_' . static::NAME      => [ 'find_importable_endpoint', 10, 3 ],

			// FRONTEND
			'post_type_link'                 => [ 'permalink_source_url', 10, 2 ],

		];
	}



	/**
	 * Get the post data as a wp_insert_post compatible array.
	 *
	 * @return array
	 */
	public function get_post_data() : Array
	{
		return [
			'post_author' => ( isset( $this->arguments['user_id'] ) ) ? $this->arguments['user_id'] : Users\ft_bot::id(), 
			'post_title' => $this->arguments['new_post_title'],
			'post_content' => $this->arguments['new_post_content'],
			'post_status' => 'publish', // start with private, switch to publish on later point
			'post_type' => self::NAME,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			// 'tax_input' => $this->get_post_tax(),
			// 'meta_input' => $this->get_post_meta(),
		];
	}

	/**
	 * Get all the post meta as a key-value associative array.
	 *
	 * @return array
	 */
	public function get_post_meta() : Array
	{
		return [
		];
	}

	/**
	 * Get all taxonomies and its terms (IDs) 
	 * as multidimesnional array, 
	 * properly prepared to be used 
	 * as part of wp_insert_post.
	 *
	 * Structural Example:
	 * 		'tax_input'    => array(
	 *			'hierarchical_tax'     => array( 13, 10 ),
	 *			'non_hierarchical_tax' => 'tax name 1, tax name 2',
	 *		),
	 *
	 */
	public function get_post_tax() : Array
	{
		// 0. prepare return
		$tax_input = [];

		return $tax_input;
	}


	public static function find_importable_endpoint( int $post_ID, WP_Post $post, bool $update ) : void {
		
		// run only on the first run
		if ( $update )
			return;

		// make sure we have anything to work with
		if ( empty( $post->post_content ) )
			return;

		// make sure it is a well formed URL
		$new_url = \esc_url( 
			$post->post_content,
			[
				'http',
				'https'
			],
			'db'
		);
		if ( empty( $new_url ) )
			return;

		$new_url = \untrailingslashit( $new_url );

		// well prepared,
		// let's go
		// 
		// hand the URL to our RSS-detective
		if ( static::has_importable_endpoint( $new_url ) )
			// we found something ...
			\wp_set_object_terms( 
				$post_ID,
				[
					'is-importable',
				],
				'link_category',
				true
			);

	}

	public static function has_importable_endpoint( string $new_url ) : bool {
		
		$services = [
			// Example
			// do not add any protocoll
			// 
			// 'url-to-search.domain' => '%s/importable/endpoint/',
			
			// 
			'.blogspot.com'  => '%s/feeds/posts/default',
			
			// !!
			'.jimdo.com'     => '%s/rss/blog/',
			'.jimdofree.com' => '%s/rss/blog/',
			
			// 
			'.tumblr.com'    => '%s/rss',
			
			// 
			'vimeo.com'      => '%s/videos/rss',
			
			// 
			'wix.com'        => '%s/blog-feed.xml',
			
			// 
			'wordpress.com'  => '%s/feed/',
			
			// 
			'youtube.com'    => '%s',
			
			// 
			// 'medium.com/example-site' => 'https://medium.com/feed/example-site',
			// 
			// 'twitter.com/example-site' => 'https://nitter.com/...???.../example-site/feed/',
			// 
			// 'flickr.com/example-site' => 'https://flickr.com/...???.../some-cryptic-flickr-id',


			// NO WAY
			// - other than a sarcastic blog post - 
			// 
			// facebook.com
			// weebly.com

		];

		$found = false;
		foreach ( $services as $url_to_search => $pattern ) {
	
			if ( $found )
				return $found;

			if ( false !== strpos( $new_url, $url_to_search ) ) {

				#\do_action( 'qm/info', sprintf( $pattern, $new_url ) . ' can be imported.' );
				$found = true;
				\do_action( 
					__NAMESPACE__ . '\\found_importable_endpoint',
					sprintf( $pattern, $new_url )
				);
			} 
		}

		if ( $found )
			return $found;

		\do_action( 'qm/warning', '{new_url} kann nicht importiert werden.', [
			'new_url' => $new_url,
		] );

		return $found;
	}

	protected function prepare_pt() : void {}


	protected function prepare_labels() : Array
	{
		return $this->labels = array(

			# Override the base names used for labels:
			'singular' => __('Link', 'ft-network-sourcelinks'),
			'plural'   => __('Links', 'ft-network-sourcelinks'),
			'slug'     => $this::SLUG, // must be string

		);
	}

	protected function register_post_type__default_args() : Array
	{
		return array(
			'capability_type'     => 'post',
			// 'capability_type'     => ['ft_link','ft_links'],
			'supports'            => array(
				'title',
				// 'editor',
				'author',
				// 'thumbnail',
				'excerpt',
				'custom-fields',
				// 'trackbacks',
				// 'comments',
				// 'revisions',
				// 'page-attributes',
				'post-formats',
			),

			'menu_icon'           => 'dashicons-admin-links',
			'menu_position'       => 50,

			'show_ui'             => true,

			// 'show_in_menus'       => false,
			// 'show_in_nav_menus'   => false,
			// 'show_in_admin_bar'   => false,
			'public'              => false, // 'TRUE' enables editable post_name, called 'permalink|slug'

			'publicly_queryable'  => true,  // was TRUE for long, lets see
			// 'query_var'           => false, // If false, a post type cannot be loaded at ?{query_var}={post_slug}.

			'show_in_rest'        => true, // this in combination with  'supports' => array('editor') enables the Gutenberg editor
			'hierarchical'        => false, // that to FALSE if not really needed, for performance reasons
			'description'         => '',
			'taxonomies'          => [
				// Features\UtilityFeaturesManager::TAX,
				// Taxonomies\Taxonomy__ft_site_shadow::NAME, # must be here to allow setting its terms, even when hidden
				'link_category',
			],

			// 'rewrite' => true,  // enables editable post_name, called 'permalink|slug'

			#
			// 'has_archive' => true,

			#
			'can_export' => true,




			/**
			 * Localiced Labels
			 * 
			 * ExtendedCPTs generates the default labels in English for your post type. 
			 * If you need to allow your post type labels to be localized, 
			 * then you must explicitly provide all of the labels (in the labels parameter) 
			 * so the strings can be translated. There is no shortcut for this.
			 *
			 * @source https://github.com/johnbillion/extended-cpts/pull/5#issuecomment-33756474
			 * @see https://github.com/johnbillion/extended-cpts/blob/d6d83bb41eba9a3603929244c71f3f806c2a14d8/src/PostType.php#L152
			 */
			# fallback
			'label'         => $this->labels['plural'],
			'labels'                => [
				'name'                  => __( 'Links', 'ft-network-sourcelinks' ),
				'singular_name'         => __( 'Link', 'ft-network-sourcelinks' ),
				'all_items'             => __( 'All Links', 'ft-network-sourcelinks' ),
				'archives'              => __( 'Link Archives', 'ft-network-sourcelinks' ),
				'attributes'            => __( 'Link Attributes', 'ft-network-sourcelinks' ),
				'insert_into_item'      => __( 'Insert into Link', 'ft-network-sourcelinks' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Link', 'ft-network-sourcelinks' ),
				'featured_image'        => _x( 'Image', 'ft_link', 'ft-network-sourcelinks' ),
				'set_featured_image'    => _x( 'Set image', 'ft_link', 'ft-network-sourcelinks' ),
				'remove_featured_image' => _x( 'Remove image', 'ft_link', 'ft-network-sourcelinks' ),
				'use_featured_image'    => _x( 'Use as image', 'ft_link', 'ft-network-sourcelinks' ),
				'filter_items_list'     => __( 'Filter Links list', 'ft-network-sourcelinks' ),
				'items_list_navigation' => __( 'Links list navigation', 'ft-network-sourcelinks' ),
				'items_list'            => __( 'Links list', 'ft-network-sourcelinks' ),
				'new_item'              => __( 'New Link', 'ft-network-sourcelinks' ),
				'add_new'               => __( 'Add New', 'ft-network-sourcelinks' ),
				'add_new_item'          => __( 'Add New Link', 'ft-network-sourcelinks' ),
				'edit_item'             => __( 'Edit Link', 'ft-network-sourcelinks' ),
				'view_item'             => __( 'View Link', 'ft-network-sourcelinks' ),
				'view_items'            => __( 'View Links', 'ft-network-sourcelinks' ),
				'search_items'          => __( 'Search Links', 'ft-network-sourcelinks' ),
				'not_found'             => __( 'No Links found', 'ft-network-sourcelinks' ),
				'not_found_in_trash'    => __( 'No Links found in trash', 'ft-network-sourcelinks' ),
				'parent_item_colon'     => __( 'Link:', 'ft-network-sourcelinks' ),
				'menu_name'             => __( 'Links', 'ft-network-sourcelinks' ),
			],

			// 'template'      => '',
			// 'template_lock'      => '',
		);
	}

	protected function register_extended_post_type__args() : Array
	{
		return array(

			# The "Featured Image" text used in various places
			# in the admin area can be replaced with
			# a more appropriate name for the featured image
			// 'featured_image' => __('Image'),

			#
			'enter_title_here' => __('Link Title', 'ft-network-sourcelinks'),

			#
			'quick_edit' => false,

			# Add the post type to the site's main RSS feed:
			'show_in_feed' => false,

			# Add the post type to the 'Recently Published' section of the dashboard:
			'dashboard_activity' => true,

			# An entry is added to the "At a Glance"
			# dashboard widget for your post type by default.
			// 'dashboard_glance' => false,

			# Add some custom columns to the admin screen:
			'admin_cols' => [
				// The default Title column:
				'title',
				'URL' => [
					'title'    => 'URL',
					'function' => [ $this, 'column_url'],
				],

				// moved into Feature
				// 'ueberregional-inhalte
				// '
				//Taxonomies\Taxonomy__ft_geolocation::NAME => [
				//	'taxonomy' => Taxonomies\Taxonomy__ft_geolocation::NAME
				//],
				// maybe later ..
				// Features\UtilityFeaturesManager::TAX => [
				//	'taxonomy' => Features\UtilityFeaturesManager::TAX,
				//	'title'      => 'UtilityFeatures',
				//],
				'link_category' => [
					// 'title'    => __('All Link Categories','figurentheater'),
					'taxonomy' => 'link_category'
				],

			],

			# Add some dropdown filters to the admin screen:
			'admin_filters' => [
				// moved into Feature
				// 'ueberregional-inhalte
				// '
				//'ft_link_location' => [
				//	'title'    => '🗺️ All Locations',
				//	'taxonomy' => Taxonomies\Taxonomy__ft_geolocation::NAME
				//],
				// maybe later ..
				//'ft_link_utilityfeature' => [
				//	'title'    => 'All Utility Features',
				//	'taxonomy' => Features\UtilityFeaturesManager::TAX
				//],
				'link_category' => [
					'title'    => __('All Link Categories', 'ft-network-sourcelinks'),
					'taxonomy' => 'link_category'
				],
			],

		);
	}


	/**
	 * Handles the link URL column output.
	 *
	 * For post types:
	 * Note that the function does not get passed any parameters,
	 * so it must use the global $post object.
	 *
	 * For taxonomies:
	 * The function is passed the term ID as its first parameter.
	 *
	 * @since 4.3.0
	 * @see wp-admin\includes\class-wp-links-list-table.php#L207
	 *
	 */
	public function column_url() {
		echo self::get_readable_link();
	}

	public static function get_readable_link( WP_Post|null $post = null ) {
		if( null === $post )
			global $post;

		
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
		$_url = \wp_kses( $post->post_content, [] );
		// cleanup whitespaces and line-breaks
		$_url = preg_replace('/\s+/', '', $_url );
		// last check
		if ( ! $_url = \esc_url( $_url ) )
			return '';
	
		$_short_url = \url_shorten( $_url );
		return "<a href='$_url'>$_short_url</a>";
	}



	public function modify_metaboxes() : void
	{

		\remove_meta_box( 'slugdiv', null, 'normal' );

		if( ! \current_user_can( 'manage_sites' ) )
			\remove_meta_box( 'postcustom', null, 'normal' );


		// \add_meta_box( 'ft_links_post_formats', __( 'Post Formats' ), [$this, 'ft_links_post_formats_metabox'], null, 'side' );
	}

	public function post_content__metabox( WP_Post $post ) {
		if ( self::NAME !== $post->post_type )
			return;
		?>
		<div id="addressdiv" class="postbox">
		<div class="postbox-header">
			<h2>
				<label for="post_content"><?php _e( 'Web Address' ); ?></label>
			</h2>
		</div>
		<div class="inside">
			<input type="url" name="post_content" value="<?php echo isset( $post->post_content ) ? esc_attr( $post->post_content ) : ''; ?>" id="post_content" class="code" style="width:100%" required="required" />
			<p><?php _e( 'Example: <code>https://wordpress.org/</code> &#8212; don&#8217;t forget the <code>https://</code>' ); ?></p>
		</div>
		</div>
		<?php
	}



	/**
	 * Change the post's permalink to use its source URL instead.
	 *
	 * @param string $permalink
	 * @param WP_Post $post
	 *
	 * @see  https://github.com/WordPress/wordpress.org/blob/trunk/wordpress.org/public_html/wp-content/plugins/reblog-feed/reblog-feed.php#L83
	 *
	 * @return string
	 */
	public function permalink_source_url( $permalink, $post ) {
		if ( static::NAME !== $post->post_type ) {
			return $permalink;
		}
		
		$url = \esc_url( $post->post_content );
		return $url ?? $permalink;
	}



	public static function get_instance()
	{
		if ( null === self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
}































// add_action( 'admin_menu', __NAMESPACE__.'\\debug_Post_Type__ft_link');
#debug_Post_Type__ft_link();


function debug_Post_Type__ft_link(){

	$ft_link = new Post_Type__ft_link();

	 $ft_link->has_importable_endpoint( 'https://juliaraab.de' );
	 $ft_link->has_importable_endpoint( 'https://wordpress.com/juliaraab' );
	 $ft_link->has_importable_endpoint( 'https://juliaraab.tumblr.com' );
	 $ft_link->has_importable_endpoint( 'https://juliaraab.blogspot.com' );
	 $ft_link->has_importable_endpoint( 'https://medium.com/juliaraab' );


	$option = \get_option( 'wpursstoposts_options' );

	\do_action( 'qm/info', $option );

	// \do_action( 'qm/info', '{fn}: {value}', [
		// 'fn' => "current_user_can( 'manage_links' )",
		// 'value' => \current_user_can( 'manage_links' ),
	// ] );


/*	wp_die(
		'<pre>'.
		var_export(
			array(
				__FILE__,
#$new,
#\Figuren_Theater\FT::site()->UtilityFeaturesManager,
#$current->get_post_data(),
#$current->get_post_meta(),
$current->get_post_tax(),
#$current->prepare_new_ft_level_relative_data( 242 ), // UR web
#$current_wp_site,
#				FT::site()->FeaturesManager,
			),
			true
		).
		'</pre>'
	);*/
}
