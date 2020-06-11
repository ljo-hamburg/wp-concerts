<?php
/**
 * The `TicketsBlock` class.
 *
 * @package LJO\WPConcerts.
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

/**
 * The `TicketsBlock` represents the tickets block.
 *
 * @package LJO\WPConcerts\Blocks
 */
class TicketsBlock extends Block {

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	public const NAME = 'tickets';

	/**
	 * The attribute key for the availability attribute. Possible values are included
	 * as constants of the `TicketsBlock` class starting with `AVAILABILITY_`.
	 *
	 * @var string
	 */
	public const AVAILABILITY_KEY = 'availability';

	/**
	 * The attribute key for the notes attribute.
	 *
	 * @var string
	 */
	public const NOTES_KEY = 'notes';

	/**
	 * The attribute key for the link attribute.
	 *
	 * @var string
	 */
	public const LINK_KEY = 'link';

	/**
	 * Availability value indicating that tickets are not available yet. This value is
	 * assumed when no ticket availability is specified.
	 *
	 * @var string
	 */
	public const AVAILABILITY_NONE = 'none';

	/**
	 * Availability value indicating that tickets are available for free or that no
	 * tickets are required.
	 *
	 * @var string
	 */
	public const AVAILABILITY_FREE = 'free';

	/**
	 * Availability value indicating that tickets are available at the box office. This
	 * value should be accompanied by ticket items representing the available tiers.
	 *
	 * @var string
	 */
	public const AVAILABILITY_BOX_OFFICE = 'box-office';

	/**
	 * Availability value indicating that tickets are available online. This value
	 * should be accompanied by ticket items representing the available tiers. Also the
	 * `LINK_KEY` attribute should be set to an appropriate URL where tickets can be
	 * bought.
	 *
	 * @var string
	 */
	public const AVAILABILITY_ONLINE = 'online';

	/**
	 * TicketsBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( self::NAME );
	}

}
