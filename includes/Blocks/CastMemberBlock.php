<?php
/**
 * The `CastMemberBlock` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

/**
 * Registers the cast member block.
 *
 * @package LJO\WPConcerts\Blocks
 */
class CastMemberBlock extends Block {

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	public const NAME = 'cast-member';

	/**
	 * The attribute key for the cast member's name.
	 *
	 * @var string
	 */
	public const NAME_KEY = 'name';

	/**
	 * The attribute key for the cast member's role.
	 *
	 * @var string
	 */
	public const ROLE_KEY = 'role';

	/**
	 * The attribute key for the cast member's url.
	 *
	 * @var string
	 */
	public const URL_KEY = 'url';

	/**
	 * The attribute key for the cast member's extra.
	 *
	 * @var string
	 */
	public const EXTRA_KEY = 'extra';

	/**
	 * The attribute key for the cast member's ensemble attribute. The ensemble
	 * attribute indicates whether the cast member is an ensemble or an individual
	 * person.
	 *
	 * @var string
	 */
	public const ENSEMBLE_KEY = 'ensemble';

	/**
	 * CastMemberBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( self::NAME );
	}

}
