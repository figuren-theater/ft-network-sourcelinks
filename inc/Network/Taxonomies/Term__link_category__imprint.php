<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Taxonomies;


/**
 * Term of the 'link_category' taxonomy
 */
// class Term__link_category__imprint extends Term__Abstract
class Term__link_category__imprint
{
	/**
	 * Term Slug
	 */
	const SLUG = 'imprint';

	/**
	 * Term Name
	 */
	public $name = '';

	/**
	 * Term Description
	 */
	public $description = '';

	function __construct()
	{
		$this->name = _x( 'Imprint relevant', 'Default Link-Category Name', 'ft-network-sourcelinks' );
		$this->description = _x( 'These Links belong to sites I\'m responsible for, so show them in my imprint.', 'Default Link-Category Description', 'ft-network-sourcelinks' );
	}
}
