/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */

import { useSelect } from '@wordpress/data';

import { store as coreDataStore } from '@wordpress/core-data';

import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element'; //React.createElement

import { 
    Spinner,
} from '@wordpress/components';




/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
// import './editor.scss';



const getHostnameFromRegex = ( url ) => {
  // run against regex
  const matches = url.match(/^https?\:\/\/([^\/?#]+)(?:[\/?#]|$)/i);
  // extract hostname (will be null if no match is found)
  if ( matches ) {

  	const domain = matches[1].split('.');
  	domain.pop();
  	return domain.join('.');
  }
}



/**
 * [buildBlock description]
 *
 * Normally the 'ft_link' posttype lacks of the 
 * normal args 'content', because its pt definition
 * doesn't define 'Editor' as pt support.
 *
 * BUT, we added a post_permalink_filter,
 * which grabs the post_permalink field
 * and uses the post_content for it.
 *
 * So we can use 'post.link' over here.
 *
 * @package ft-network-sources
 * @version 2022.04.01
 * @author  Carsten Bach
 *
 * @see  https://developer.wordpress.org/rest-api/using-the-rest-api/backbone-javascript-client/#default-values
 *
 * @param   {object}     post [description]
 * @return  {[type]}          [description]
 */
const buildBlock = ( post ) =>
{
  return [ 'core/social-link', {
		label:   post.title.rendered,
		service: getHostnameFromRegex( post.link ),
		url:     post.link
	} ];
}



/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit() {


	const ALLOWED_BLOCKS = [
        'core/social-links',
        'core/social-link'
    ];

    // get core option 'default_link_category'
    // 
    // V1 
    // wp.data.select( 'core' ).getEntityRecord('root','site','default_link_category')
    // 404
    // https://welttag.puppen.test/wp-json/wp/v2/settings/default_link_category?_locale=user
    // 
    // V2
    // wp.data.select('core').getEntityRecord( 'root', '__unstableBase' )
    // returns some, but not ours
    // 
    // 
    // RESULT
    // Keep this unfiltered and return all existing links 
    // (for now)

    const { links, hasResolved }  = useSelect(
        select => {

            const query = {
            	per_page: 25,
            	// link_category: 122 // for DEBUGGING on welttag.puppen.test ONLY
            };
            const selectorArgs = [ 'postType', 'ft_link', query ];
            return {
                links: select( coreDataStore ).getEntityRecords(
                    ...selectorArgs
                ),
                hasResolved: select( coreDataStore ).hasFinishedResolution(
                    'getEntityRecords',
                    selectorArgs
                ),
            };
        },
        []
    );

    // @todo
    // check for more than one InnerBlocks
    // and only then
    // look for the links
    // needed as defaults

    // finished ?
    if ( !hasResolved ) {
		return (
    		<div { ...useBlockProps() }>
    			<Spinner />
    		</div>    
		)
	}

    // finished the query
    // but found none
	if ( hasResolved && 0 === links.length ){
            const MINIMAL_TEMPLATE = [
                [ 'core/social-links', {} ]
            ];
    
            return(
                <div { ...useBlockProps() }>
                    <InnerBlocks
                        allowedBlocks={ ALLOWED_BLOCKS }
                        template={ MINIMAL_TEMPLATE }
                    />
                </div>    
            )
    }

    // finished the query 
    // and found some
	let ft_link_blocks = links.map( buildBlock )

    const TEMPLATE = [
        [ 'core/social-links', {},
	        ft_link_blocks
        ]
    ];

    //console.log( links )
    //console.log( ft_link_blocks )
	
    return (
		<div { ...useBlockProps() }>
            <InnerBlocks
                allowedBlocks={ ALLOWED_BLOCKS }
                template={ TEMPLATE }
            />
		</div>
	);
}
