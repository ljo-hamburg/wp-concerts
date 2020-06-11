import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, RichText } from "@wordpress/block-editor";
import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";

const BLOCK_NAME = "wp-concerts/cast-member";

/**
 * A cast member is a single person or entity participating in a concert. This block
 * allows to create and edit a single cast member.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Cast Member", "wp-concerts"),
  description: __("Entities participating in a concert.", "wp-concerts"),
  category: "common",
  icon: "admin-users",
  parent: ["wp-concerts/cast"],
  attributes: {
    /**
     * The full name of the person or entity.
     */
    name: {
      type: "string",
    },
    /**
     * The role of the cast member in the concert (e.g. conductor).
     */
    role: {
      type: "string",
    },
    /**
     * An URL associated with the cast member (e.g. a homepage or biography). This value
     * may be used by search engines to identify people and music groups.
     */
    url: {
      type: "string",
    },
    /**
     * Additional information about the cast member in the context of a concert. This
     * field may be useful to indicate additional information about the member. This
     * field is not visible to search engines.
     */
    extra: {
      type: "string",
    },
    /**
     * A boolean value indicating whether this cast member is an ensemble or a single
     * person. This value is used exclusively by search engines to better classify
     * entities.
     */
    ensemble: {
      type: "boolean",
      default: false,
    },
  },
  edit({
    className,
    setAttributes,
    attributes: { name, role, url, extra, ensemble },
  }) {
    return (
      <>
        <InspectorControls>
          <PanelBody>
            <TextControl
              label={__("Homepage", "wp-concerts")}
              help={__(
                "Enter a homepage of the cast member. This value helps search engines better find concerts.",
                "wp-concerts"
              )}
              value={url}
              onChange={(url) => setAttributes({ url })}
            />
            <TextControl
              label={__("Additional Information", "wp-concerts")}
              help={__("Informative text for the cast member.")}
              value={extra}
              onChange={(extra) => setAttributes({ extra })}
            />
            <ToggleControl
              label={__("This is an ensemble", "wp-concerts")}
              help={__(
                "Check this if this cast member is an ensemble. This helps Google understand this concert."
              )}
              checked={ensemble}
              onChange={(ensemble) => setAttributes({ ensemble })}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          <div className="name-role">
            <RichText
              tagName="div"
              className="name"
              value={name}
              allowedFormats={[]}
              onChange={(name) => setAttributes({ name })}
              placeholder={__("Name", "wp-concerts")}
            />
            <RichText
              tagName="div"
              className="role"
              value={role}
              allowedFormats={[]}
              onChange={(role) => setAttributes({ role })}
              placeholder={__("Role", "wp-concerts")}
            />
          </div>
          {extra && (
            <RichText
              tagName="small"
              className="extra"
              value={extra}
              allowedFormats={[]}
              onChange={(extra) => setAttributes({ extra })}
            />
          )}
        </div>
      </>
    );
  },
  save({ attributes: { name, role, url, extra } }) {
    return (
      <div>
        <div className="name-role">
          {url ? (
            <a
              className="name"
              href={url}
              target="_blank"
              rel="noopener noreferrer"
            >
              {name}
            </a>
          ) : (
            <span className="name">{name}</span>
          )}
          <div className="role">{role}</div>
        </div>
        {extra && <small className="extra">{extra}</small>}
      </div>
    );
  },
});
