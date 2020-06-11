import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import {
  InnerBlocks,
  InspectorControls,
  RichText,
} from "@wordpress/block-editor";
import { PanelBody, SelectControl, TextControl } from "@wordpress/components";

const BLOCK_NAME = "wp-concerts/tickets";

const AVAILABILITY = {
  none: "none",
  free: "free",
  boxOffice: "box-office",
  online: "online",
};

/**
 * Returns whether buying tickets is possible given an availability value.
 *
 * @param {string} availability One of the `AVAILABILITY` values.
 */
function canBuyTickets(availability) {
  return [AVAILABILITY.boxOffice, AVAILABILITY.online].includes(availability);
}

/**
 * Registers the tickets block. The tickets block mainly exists as a wrapper around
 * ticket items. However it is also possible to indicate the availability of tickets.
 * Depending on the availability it may or may not be possible to add ticket items.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Tickets", "wp-concerts"),
  description: __("Tickets for this concert.", "wp-concerts"),
  category: "common",
  icon: "tickets-alt",
  attributes: {
    /**
     * The availability of tickets. Must be one of the values in `AVAILABILITY`.
     */
    availability: {
      type: "string",
      default: "none",
    },
    /**
     * Additional information that applies independently of a single ticket item. Here
     * users may indicate additional tiers that are only available on request.
     */
    notes: {
      type: "string",
    },
    /**
     * An URL where tickets can be bought.
     */
    link: {
      type: "string",
    },
  },
  edit({
    className,
    setAttributes,
    attributes: { availability, notes, link },
  }) {
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <SelectControl
              label={__("Availability", "wp-concerts")}
              value={availability}
              options={[
                {
                  label: __("Not available yet", "wp-concerts"),
                  value: AVAILABILITY.none,
                },
                {
                  label: __("Free Admission", "wp-concerts"),
                  value: AVAILABILITY.free,
                },
                {
                  label: __("Box office only", "wp-concerts"),
                  value: AVAILABILITY.boxOffice,
                },
                {
                  label: __("Tickets available online", "wp-concerts"),
                  value: AVAILABILITY.online,
                },
              ]}
              onChange={(availability) => setAttributes({ availability })}
            />
            {availability === AVAILABILITY.online && (
              <TextControl
                label={__("Ticket Link", "wp-concerts")}
                help={__("Where can tickets be bought?", "wp-concerts")}
                value={link}
                onChange={(link) => setAttributes({ link })}
              />
            )}
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          <h2>{__("Tickets", "wp-concerts")}</h2>
          {availability === AVAILABILITY.none && (
            <div className="availability">
              {__("Not available yet", "wp-concerts")}
            </div>
          )}
          {availability === AVAILABILITY.free && (
            <div className="availability">
              {__("Free Admission", "wp-concerts")}
            </div>
          )}
          {availability === AVAILABILITY.boxOffice && (
            <div className="availability">
              {__("Box office only", "wp-concerts")}
            </div>
          )}
          {canBuyTickets(availability) && (
            <InnerBlocks
              allowedBlocks={["wp-concerts/ticket-item"]}
              renderAppender={InnerBlocks.ButtonBlockAppender}
            />
          )}
          <RichText
            tagName={"div"}
            className={"notes"}
            value={notes}
            allowedFormats={[]}
            onChange={(notes) => setAttributes({ notes })}
            placeholder={__("Extra Information", "wp-concerts")}
          />
          {availability === AVAILABILITY.online && link && (
            <a
              className="button"
              href={link}
              target="_blank"
              rel="noopener noreferrer"
            >
              {__("Buy Tickets", "wp-concerts")}
            </a>
          )}
        </div>
      </>
    );
  },
  save({ attributes: { availability, notes, link } }) {
    return (
      <div>
        <h2>{__("Tickets", "wp-concerts")}</h2>
        {availability === AVAILABILITY.none && (
          <div className="availability">
            {__("Not available yet", "wp-concerts")}
          </div>
        )}
        {availability === AVAILABILITY.free && (
          <div className="availability">
            {__("Free Admission", "wp-concerts")}
          </div>
        )}
        {availability === AVAILABILITY.boxOffice && (
          <div className="availability">
            {__("Box office only", "wp-concerts")}
          </div>
        )}
        {canBuyTickets(availability) && <InnerBlocks.Content />}
        {notes && <div className="notes">{notes}</div>}
        {availability === AVAILABILITY.online && link && (
          <a
            className="button link"
            href={link}
            target="_blank"
            rel="noopener noreferrer"
          >
            {__("Buy Tickets", "wp-concerts")}
          </a>
        )}
      </div>
    );
  },
});
