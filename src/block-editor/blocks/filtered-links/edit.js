
/**
 * WordPress dependencies
 */

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
import {
    BlockControls,
    InspectorControls,
    useBlockProps,
} from '@wordpress/block-editor';
/**
 * https://developer.wordpress.org/block-editor/how-to-guides/data-basics/2-building-a-list-of-pages/
 * https://stackoverflow.com/questions/57878714/how-to-use-getentityrecords-for-specific-taxonomy-terms
 * https://wordpress.stackexchange.com/questions/298495/query-the-rest-api-for-a-tag-by-slug
 */
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import { 
    Notice,
    PanelBody,
    RadioControl,
    Spinner,
    ToggleControl
} from '@wordpress/components';




function MyFirstApp( { link_category_slug, humanReadable } ) {

    let { link_cat_term, hasResolvedLinkCat }  = useSelect(
        select => {

            let query = {
            	per_page: 1,
                slug: link_category_slug
            };
            let selectorArgs = [ 'taxonomy', 'link_category', query ];
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
        [ link_category_slug ]
    );


    const { links, hasResolved }  = useSelect(
        select => {

            const query = {
            	per_page: 25,
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
        [ humanReadable ]
    );
    return <LinksList 
    	hasResolved={ hasResolved }
    	links={ links }
    	hasResolvedLinkCat={ hasResolvedLinkCat }
    	link_cat_term={ link_cat_term }
        humanReadable={ humanReadable }
    />;
}

 
function LinksList( { 
    hasResolved,
    links,
    hasResolvedLinkCat,
    link_cat_term,
    humanReadable
} ) {
    if ( !hasResolvedLinkCat || !hasResolved ) {
		return (
    		<div { ...useBlockProps() }>
    			<Spinner />
    		</div>    
		)
	}

    // fallback
    let filtered_links = links

	// reduce the set of 'ft_link' posts
    // to the ones with our currently needed
    // 'link_category' taxonomy term
    if ( link_cat_term?.length )
        filtered_links = links.filter(v => v.link_category.includes( link_cat_term[0].id ) );

    if ( !filtered_links?.length ) {
        return (
            <div { ...useBlockProps() }>
                <Notice
                    isDismissible={ false }
                    status="warning"
                >
                    { __( 'No Links available in the chosen category.','ft-network-sourcelinks' ) }
                </Notice>
            </div>    
        )
    }

    const untrailingSlashIt = ( str ) => {
      return str.replace(/\/$/, '');
    }

    const url_title = ( link ) => {
        let url =  new URL( link )
        // let path = ( 2 > url.pathname.length ) ? '' : url.pathname 
        // return url.host + path;
        return untrailingSlashIt( url.host + url.pathname );
    }

    return (

        <ul { ...useBlockProps() }>
            { filtered_links?.map( ft_link => (
                <li key={ ft_link.id }>
                    { humanReadable ? (
                    	<a href={ ft_link.link } title={ decodeEntities( ft_link.title.rendered ) }>
                            { url_title( ft_link.link ) }
                    	</a>
                    ) : (
                        <a href={ ft_link.link }>
                    	   { decodeEntities( ft_link.title.rendered ) }
                        </a>
                    ) }
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
export default function Edit( {
    attributes: { link_category_slug, humanReadable },
    setAttributes
} ) {

    const { link_cat_terms, hasResolvedLinkCats }  = useSelect(
        select => {

            const query = {
                per_page: 100,
            };
            const selectorArgs = [ 'taxonomy', 'link_category', query ];
            return {
                link_cat_terms: select( coreDataStore ).getEntityRecords(
                    ...selectorArgs
                ),
                hasResolvedLinkCats: select( coreDataStore ).hasFinishedResolution(
                    'getEntityRecords',
                    selectorArgs
                ),
            };
        },
        []
    );

    function onChangeRadioField( newValue ) {
        setAttributes( { link_category_slug: newValue } );
    }
    
	return (
        <>
            <InspectorControls>
                <PanelBody title={ __( 'Settings' ) }>
                    <ToggleControl
                        label={ __( 'Show URLs or Title.', 'ft-network-sourcelinks' ) }
                        onChange={ ( newHumanReadable ) => setAttributes( { humanReadable: newHumanReadable } ) }
                        checked={ humanReadable }
                    />
                    <RadioControl
                        label={ __( 'Show Links by category.','ft-network-sourcelinks' ) }
                        selected={ link_category_slug }
                        options={ link_cat_terms?.map( link_cat_term => (
                            { label: link_cat_term.name, value: link_cat_term.slug   }
                        ) ) }
                        onChange={ onChangeRadioField }
                    />

                </PanelBody>
            </InspectorControls>
            <MyFirstApp 
                link_category_slug={ link_category_slug }
                humanReadable={ humanReadable }
            />
        </>
	);
}


