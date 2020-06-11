import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { RichText, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";

const BLOCK_NAME = "wp-concerts/ticket-item";

/**
 * Registers the ticket item block. A ticket item allows the user to edit a single price
 * point for tickets for a concert.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Ticket Item", "wp-concerts"),
  description: __("A single ticket price.", "wp-concerts"),
  category: "common",
  icon: "tickets-alt",
  parent: ["wp-concerts/tickets"],
  attributes: {
    /**
     * The price of this ticket item, including the currency. To improve search engine
     * compatibility it is currently required to enter the amount in the exact format
     * expected by the locale of the WordPress site.
     */
    amount: {
      type: "string",
    },
    /**
     * Extra information about the amount (such as excluded tax).
     */
    extra: {
      type: "string",
    },
    /**
     * The description of the ticket item. This is usually a description of what you are
     * getting for this price.
     */
    description: {
      type: "string",
    },
    /**
     * A boolean value indicating whether this price point is sold out or still
     * available.
     */
    soldOut: {
      type: "boolean",
    },
  },
  edit({
    className,
    setAttributes,
    attributes: { amount, extra, description, soldOut },
  }) {
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <TextControl
              label={__("Price Description", "wp-concerts")}
              help={__(
                "Additional information about the price such as excluded costs.",
                "wp-concerts"
              )}
              value={extra}
              onChange={(extra) => setAttributes({ extra })}
            />
            <ToggleControl
              label={__("Sold out", "wp-concerts")}
              help={__(
                "Check this box if this ticket tier is sold out.",
                "wp-concerts"
              )}
              checked={soldOut}
              onChange={(soldOut) => setAttributes({ soldOut })}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className + (soldOut ? " sold-out" : "")}>
          <div className="price">
            <RichText
              tagName="span"
              className="amount"
              value={amount}
              allowedFormats={[]}
              onChange={(amount) => setAttributes({ amount })}
              placeholder={__("$ Amount", "wp-concerts")}
            />
            {extra && (
              <RichText
                tagName="span"
                className="extra"
                value={extra}
                allowedFormats={[]}
                onChange={(extra) => setAttributes({ extra })}
                placeholder={__("Info", "wp-concerts")}
              />
            )}
          </div>
          <RichText
            tagName="div"
            className="description"
            value={description}
            allowedFormats={[]}
            onChange={(description) => setAttributes({ description })}
            placeholder={__("Description", "wp-concerts")}
          />
        </div>
      </>
    );
  },
  save({ attributes: { amount, extra, description, soldOut } }) {
    return (
      <div className={soldOut ? "sold-out" : ""}>
        <div className="price">
          <span className="amount">{amount}</span>
          {extra && <span className="extra">{extra}</span>}
        </div>
        {description && <div className="description">{description}</div>}
      </div>
    );
  },
});
