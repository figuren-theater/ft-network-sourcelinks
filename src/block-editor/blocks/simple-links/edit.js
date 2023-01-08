
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * https://developer.wordpress.org/block-editor/how-to-guides/data-basics/2-building-a-list-of-pages/
 * https://stackoverflow.com/questions/57878714/how-to-use-getentityrecords-for-specific-taxonomy-terms
 * https://wordpress.stackexchange.com/questions/298495/query-the-rest-api-for-a-tag-by-slug
 */
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import { Spinner } from '@wordpress/components';

function MyFirstApp() {

    const { link_cat_term, hasResolvedLinkCat }  = useSelect(
        select => {

            const query = {
            	per_page: 1,
            	slug: 'sync'
            };
            const selectorArgs = [ 'taxonomy', 'link_category', query ];
            return {
                link_cat_term: select( coreDataStore ).getEntityRecords(
                    ...selectorArgs
                ),
                hasResolvedLinkCat: select( coreDataStore ).hasFinishedResolution(
                    'getEntityRecords',
                    selectorArgs
                ),
            };
        },
        []
    );

    const { links, hasResolved }  = useSelect(
        select => {

            const query = {
            	per_page: 25,
            	// link_category: link_cat_term.id
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

    return <LinksList 
    	hasResolved={ hasResolved }
    	links={ links }
    	hasResolvedLinkCat={ hasResolvedLinkCat }
    	link_cat_term={ link_cat_term }
    />;
}
 
function LinksList( { hasResolved, links, hasResolvedLinkCat, link_cat_term } ) {
    if ( !hasResolvedLinkCat ) {
		return (
		<div { ...useBlockProps() }>
			<Spinner />
		</div>    
		)
	}
    if ( !hasResolved ) {
		return (
		<div { ...useBlockProps() }>
			<Spinner />
		</div>    
		)    }

	const filtered_links = links.filter(v => v.link_category.includes( link_cat_term[0].id ) );
/*
    console.log(link_cat_term)
    console.log(links)
    console.log(filtered_links)
*/
    if ( !filtered_links?.length ) {
        return <div>No results</div>
    }
    
    return (
        <ul>
            { filtered_links?.map( ft_link => (
                <li key={ ft_link.id }>
                	<a href={ ft_link.link }>
                    	{ decodeEntities( ft_link.title.rendered ) }
                	</a>
                </li>
            ) ) }
        </ul>
    )
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

	return (
		<MyFirstApp />
	);
}





