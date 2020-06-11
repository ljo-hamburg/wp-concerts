<?php
/**
 * The `ProgramItemBlock` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

/**
 * Creates and registers the program item block.
 *
 * @package LJO\WPConcerts\Blocks
 */
class ProgramItemBlock extends Block {

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	public const NAME = 'program-item';

	/**
	 * The attribute key for the composer attribute.
	 *
	 * @var string
	 */
	public const COMPOSER_KEY = 'composer';

	/**
	 * The attribute key for the piece attribute.
	 *
	 * @var string
	 */
	public const PIECE_KEY = 'piece';

	/**
	 * The attribute key for the url attribute.
	 *
	 * @var string
	 */
	public const URL_KEY = 'url';

	/**
	 * ProgramItemBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( self::NAME );
	}

}
