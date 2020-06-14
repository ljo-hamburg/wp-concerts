import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { PanelBody, RangeControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import ServerSideRender from "@wordpress/server-side-render";

const BLOCK_NAME = "wp-concerts/next-concerts";

/**
 * Displays a list of upcoming concerts. For simplicity this block is rendered on the
 * server side.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Next Concerts", "wp-concerts"),
  description: __("A list of upcoming concerts.", "wp-concerts"),
  category: "common",
  icon: "calendar-alt",
  attributes: {
    count: {
      type: "integer",
      default: 3,
    },
  },
  edit({ setAttributes, attributes: { count } }) {
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <RangeControl
              label={__("Number of Concerts", "wp-concerts")}
              help={__(
                "The number of upcoming concerts to be displayed",
                "wp-concerts"
              )}
              min={0}
              value={count}
              onChange={(count) => setAttributes({ count })}
            />
          </PanelBody>
        </InspectorControls>
        <ServerSideRender block={BLOCK_NAME} attributes={{ count }} />
      </>
    );
  },
  save() {
    return null;
  },
});
