import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, InnerBlocks } from "@wordpress/block-editor";
import { useSelect } from "@wordpress/data";
import { PanelBody, ToggleControl } from "@wordpress/components";

const BLOCK_NAME = "wp-concerts/program";

/**
 * Custom React hook that returns the end time of the current concert.
 *
 * @returns {string|null} The end time or `null` if no end time is specified.
 */
function useConcertEnd() {
  const meta = useSelect((select) => {
    return select("core/editor").getEditedPostAttribute("meta");
  }, []);
  const concertDate = meta["concert-date"];
  const duration = meta["concert-duration"];

  if (!concertDate || duration <= 0) {
    return null;
  }
  const date = new Date(concertDate);
  const dateTimeFormat = new Intl.DateTimeFormat(undefined, {
    hour: "2-digit",
    minute: "2-digit",
  });
  return dateTimeFormat.format(new Date(date.getTime() + duration * 60 * 1000));
}

/**
 * Registers the program block. The block exists mainly as a wrapper around program
 * items but it also features a display of the end time of a concert.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Program", "wp-concerts"),
  description: __("Specify the concert schedule.", "wp-concerts"),
  keywords: [__("Schedule", "wp-concerts")],
  category: "common",
  icon: "schedule",
  attributes: {
    /**
     * A boolean value indicating whether the concert end time should be displayed.
     */
    showEndTime: {
      type: "boolean",
      default: true,
    },
    /**
     * The end time of the concert. This attribute is not editable. Instead it is set
     * automatically in the editor and persisted when the block is saved. This works
     * around the limitation that the save function cannot make use of hooks.
     */
    endTime: {
      type: "string",
    },
  },
  edit({ className, setAttributes, attributes: { showEndTime } }) {
    const endTime = useConcertEnd();
    setAttributes({ endTime });
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <ToggleControl
              label={__("Show End Time", "wp-concerts")}
              checked={showEndTime}
              onChange={(showEndTime) => setAttributes({ showEndTime })}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          <h2>{__("Program", "wp-concerts")}</h2>
          <InnerBlocks
            allowedBlocks={["wp-concerts/program-item"]}
            renderAppender={InnerBlocks.ButtonBlockAppender}
          />
          {showEndTime && endTime && (
            <>
              <h3>{__("Concert End", "wp-concerts")}</h3>
              <div>{__("Ca. %s", "wp-concerts").replace("%s", endTime)}</div>
            </>
          )}
        </div>
      </>
    );
  },
  save({ attributes: { showEndTime, endTime } }) {
    return (
      <div>
        <h2>{__("Program", "wp-concerts")}</h2>
        <InnerBlocks.Content />
        {showEndTime && endTime && (
          <>
            <h3>{__("Concert End", "wp-concerts")}</h3>
            <div>{__("Ca. %s", "wp-concerts").replace("%s", endTime)}</div>
          </>
        )}
      </div>
    );
  },
});
