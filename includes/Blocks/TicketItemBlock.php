<?php
/**
 * The `TicketItemBlock` class.
 *
 * @package LJO\WPConcerts
 */

declare(strict_types=1);

namespace LJO\WPConcerts\Blocks;

/**
 * The `TicketItemBlock` class represents the ticket item block.
 *
 * @package LJO\WPConcerts\Blocks
 */
class TicketItemBlock extends Block {

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	public const NAME = 'ticket-item';

	/**
	 * The attribute key for the amount attribute. The amount includes the currency
	 * symbol.
	 *
	 * @var string
	 */
	public const AMOUNT_KEY = 'amount';

	/**
	 * The attribute key for the extra attribute. The extra attribute contains
	 * additional information about the amount, such as excluded taxes.
	 *
	 * @var string
	 */
	public const EXTRA_KEY = 'extra';

	/**
	 * The attribute key for the description attribute. The description usually is the
	 * name of the price tier.
	 *
	 * @var string
	 */
	public const DESCRIPTION_KEY = 'description';

	/**
	 * The attribute key for the sold out attribute. This attribute indicates whether
	 * this ticket tier is sold out or still available.
	 *
	 * @var string
	 */
	public const SOLD_OUT_KEY = 'soldOut';

	/**
	 * TicketItemBlock constructor. Creates and registers the block.
	 */
	public function __construct() {
		parent::__construct( self::NAME );
	}
}
