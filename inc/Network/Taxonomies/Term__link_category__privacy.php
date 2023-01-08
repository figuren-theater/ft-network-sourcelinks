<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Taxonomies;


/**
 * Term of the 'link_category' taxonomy
 */
// class Term__link_category__privacy extends Term__Abstract
class Term__link_category__privacy
{
	/**
	 * Term Slug
	 */
	const SLUG = 'privacy';

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
		$this->name = _x( 'Privacy relevant', 'internal Privacy-related Link-Category Name', 'ft-network-sourcelinks' );
		$this->description = _x( 'These Links belong to portals where I\'m as same as responsible for my users privacy as the portal owner.', 'internal Privacy-related Link-Category Description', 'ft-network-sourcelinks' );
	}
}
