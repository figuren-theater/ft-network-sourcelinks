<?php
declare(strict_types=1);

namespace Figuren_Theater\Network\Taxonomies;


/**
 * Term of the 'link_category' taxonomy
 */
// class Term__link_category__own extends Term__Abstract
class Term__link_category__own
{
	/**
	 * Term Slug
	 */
	const SLUG = 'own';

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
		$this->name = _x( 'Own content', 'Default Link-Category Name', 'ft-network-sourcelinks' );
		$this->description = _x( 'These Links belong to my personel websites or profiles at public social networks, where you are the main publisher.', 'Default Link-Category Description', 'ft-network-sourcelinks' );
	}
}
