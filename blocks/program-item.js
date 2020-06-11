import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, RichText } from "@wordpress/block-editor";
import { PanelBody, TextControl } from "@wordpress/components";

const BLOCK_NAME = "wp-concerts/program-item";

/**
 * Registers the program item block. The block allows the user to edit a single item on
 * a concert's schedule.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Program Item", "wp-concerts"),
  description: __("A piece of a concert program.", "wp-concerts"),
  keywords: [__("Piece", "wp-concerts")],
  category: "common",
  icon: "schedule",
  parent: ["wp-concerts/program"],
  attributes: {
    /**
     * The composer of the piece.
     */
    composer: {
      type: "string",
    },
    /**
     * The name of the piece including opus number and any additional information.
     */
    piece: {
      type: "string",
    },
    /**
     * An URL for the piece providing additional information. This URL is also used by
     * search engines to identify the piece.
     */
    url: {
      type: "string",
    },
  },
  edit({ className, setAttributes, attributes: { composer, piece, url } }) {
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <TextControl
              label={__("URL", "wp-concerts")}
              help={__(
                "A website with additional information (e.g. a Wikipedia page).",
                "wp-concerts"
              )}
              value={url}
              onChange={(url) => setAttributes({ url })}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          <RichText
            tagName="div"
            className="composer"
            value={composer}
            allowedFormats={[]}
            onChange={(composer) => setAttributes({ composer })}
            placeholder={__("Composer", "wp-concerts")}
          />
          <div className="detail">
            <RichText
              tagName="div"
              className="piece"
              value={piece}
              allowedFormats={[]}
              onChange={(piece) => setAttributes({ piece })}
              placeholder={__("Piece", "wp-concerts")}
            />
            {url && (
              <a
                target="_blank"
                rel="noopener noreferrer"
                className="link"
                href={url}
              >
                {__("About this piece", "wp-concerts")}
              </a>
            )}
          </div>
        </div>
      </>
    );
  },
  save({ attributes: { composer, piece, url } }) {
    return (
      <div>
        <div className="composer">{composer}</div>
        <div className="detail">
          <div className="piece">{piece}</div>
          {url && (
            <a
              target="_blank"
              className="link"
              href={url}
              rel="noopener noreferrer"
            >
              {__("About this piece", "wp-concerts")}
            </a>
          )}
        </div>
      </div>
    );
  },
});
