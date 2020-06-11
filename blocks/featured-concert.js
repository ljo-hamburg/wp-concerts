import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { PanelBody, Placeholder, TextControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import { useSelect } from "@wordpress/data";
import ImageSelector from "@ljo-hamburg/gutenberg-image-selector";

const BLOCK_NAME = "wp-concerts/featured-concert";

/**
 * Registers the featured concert block. The block highlights a single concert.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Featured Concert", "wp-concerts"),
  description: __("Highlight a single concert.", "wp-concerts"),
  category: "common",
  icon: "format-audio",
  attributes: {
    /**
     * The ID of the concert that should be featured.
     */
    concertID: {
      type: "integer",
    },
    /**
     * The ID of an image that should be used as a background image behind the date.
     */
    backgroundImageID: {
      type: "integer",
    },
  },
  edit({
    className,
    setAttributes,
    attributes: { concertID, backgroundImageID },
  }) {
    const backgroundImage = useSelect(
      (select) => {
        return select("core").getMedia(backgroundImageID);
      },
      [backgroundImageID]
    );
    const concert = useSelect(
      (select) =>
        select("core").getEntityRecord("postType", "concert", concertID),
      [concertID]
    );
    const date = concert ? new Date(concert.meta["concert-date"]) : null;
    const title = concert ? concert.title.rendered : "";
    let day;
    let month;
    let year;
    if (date) {
      let dateTimeFormat = new Intl.DateTimeFormat(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
      [
        { value: day },
        ,
        { value: month },
        ,
        { value: year },
      ] = dateTimeFormat.formatToParts(date);
    }
    const backgroundImageStyle = {};
    if (backgroundImage) {
      backgroundImageStyle.backgroundImage =
        'url("' + backgroundImage.source_url + '")';
    }
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <TextControl
              label={__("Concert ID", "wp-concerts")}
              help={__(
                "Enter the ID of the concert that should be displayed",
                "wp-concerts"
              )}
              value={concertID}
              onChange={(concertID) =>
                setAttributes({ concertID: parseInt(concertID) })
              }
            />
            <ImageSelector
              imageID={backgroundImageID}
              authMessage={__(
                "You are not permitted to upload images.",
                "wp-concerts"
              )}
              label={__("Select Background Image", "wp-concerts")}
              removeLabel={__("Remove Background Image", "wp-concerts")}
              onChange={(backgroundImageID) =>
                setAttributes({ backgroundImageID })
              }
            />
          </PanelBody>
        </InspectorControls>
        {!concertID && (
          <Placeholder
            icon="format-audio"
            label={__("Select a concert", "wp-concerts")}
            instructions={__(
              "Choose a featured concert by entering its ID in the inspector on the right.",
              "wp-concerts"
            )}
          />
        )}
        {concertID && !concert && (
          <Placeholder
            icon="format-audio"
            label={__("Concert not found", "wp-concerts")}
            instructions={__(
              "The concert you selected does not exist.",
              "wp-concerts"
            )}
          />
        )}
        {concertID && concert && !date && (
          <Placeholder
            icon="format-audio"
            label={__("Missing date", "wp-concerts")}
            instructions={__(
              "The selected concert does not have a date associated with it. Only concerts with dates can be displayed.",
              "wp-concerts"
            )}
          />
        )}
        {concertID && concert && date && (
          <div className={className}>
            <div className="date" style={backgroundImageStyle}>
              <div className="overlay" />
              <span className="day">{day}</span>
              <span className="month-year">
                {month}, {year}
              </span>
            </div>
            <div className="content">
              <h3 className="title">{title}</h3>
            </div>
          </div>
        )}
      </>
    );
  },
  save() {
    return null;
  },
});
