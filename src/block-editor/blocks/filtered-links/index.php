<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Blocks\Filtered_Links;

use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Taxonomies;



/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function block_init() {
	\register_block_type(
		__DIR__,
		array(
			'render_callback' => __NAMESPACE__.'\\render_block',
		)
	);
	/*
		$block = 'filtered-links';
		\wp_set_script_translations(
			"theatrebase-theatrebase-production-$block-editor-script",
			'ft-network-sourcelinks',
			\plugin_dir_path( dirname((dirname(__FILE__)) ) ) . 'languages'
		);
	*/
}
\add_action( 'init', __NAMESPACE__.'\\block_init' );




/**
 * Renders the `figurentheater/filtered-links` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string 
 */
function render_block( $attributes, $content, $block ) {

	if (empty($attributes['link_category_slug']))
		return '';

	// $slug = Taxonomies\Term__link_category__own::SLUG;
	$slug = $attributes['link_category_slug'];
	$links = get_relevant_links( $slug );
#die(var_export([$slug,$links],true));

	if (empty($links))
		return '';


	// TODO #29 // consider using $attributes['tagName']
	// which is not used at the moment, so it defaults to: ul
	$tag_name = empty( $attributes['tagName'] ) ? 'ul' : $attributes['tagName'];
	
	// get and merge wrapper attributes with text-align CSS class
	$wrapper_attributes = \get_block_wrapper_attributes();

	// // 
	// $tag_name = empty( $attributes['tagName'] ) ? 'pre' : $attributes['tagName'];
	// return sprintf(
	// 	'<%1$s %2$s>%3$s</%1$s>',
	// 	$tag_name,
	// 	$wrapper_attributes,
	// 	var_export( [$slug,$links], true )
	// );

	$listed_links = implode("\n", array_map(
		function( $link ) use ( $attributes )
		{
			// die(var_export([$_url,$link->post_content],true));

			// @todo #25
			// we have this string-cleaning now 3 times
			// 
			// - plugins\ft-network-sourcelinks\src\block-editor\blocks\filtered-links\index.php#L90
			// - plugins\ft-network-sourcelinks\inc\Network\Post_Types\Post_Type__ft_link.php#L330
			// - plugins\ft-network-sourcelinks\inc\Network\Options\Preset__wpseo_social.php#L142
			// 
			// 
			// cleanup html
			// especially from wp_auto_p
			$_url = \wp_kses( $link->post_content, [] );
			// cleanup whitespaces and line-breaks
			$_url = preg_replace('/\s+/', '', $_url );
			// last check
			if ( ! $_url = \esc_url( $_url ) )
				return '';

			if ( $attributes['humanReadable'] )
				return sprintf(
					'<li><a href="%1$s" title="%2$s">%3$s</a></li>',
					$_url,
					$link->post_title,
					\url_shorten( $_url )
				);

			// else ;)
			return sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				$_url,
				$link->post_title,
			);
		},
		$links
	) );

	// 
	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$listed_links
	);
}

function get_relevant_links( string $slug )
{
	$query = \Figuren_Theater\FT_Query::init();

	$link_category = Taxonomies\Taxonomy__link_category::NAME;

	// 
	return $query->find_many_by_type(
		Post_Types\Post_Type__ft_link::NAME,
		'publish',
		[
			'tax_query' => array(
				array(
					'taxonomy' => $link_category,
					'field'    => 'slug',
					'terms'    => $slug,
					'include_children' => true,
				),
			),
		]
	);

}
