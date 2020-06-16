import React from "react";
import { __, _x } from "@wordpress/i18n";
import { registerPlugin } from "@wordpress/plugins";
import { PluginSidebar, PluginSidebarMoreMenuItem } from "@wordpress/edit-post";
import {
  PanelBody,
  DateTimePicker,
  TextControl,
  ToggleControl,
  TextareaControl,
} from "@wordpress/components";
import { useSelect, useDispatch } from "@wordpress/data";

const SIDEBAR = {
  name: "concert-meta-sidebar",
  icon: "calendar-alt",
  title: _x("Concert Data", "Editor sidebar title", "wp-concerts"),
};

/**
 * This function registers a custom sidebar for the concert post type. The sidebar
 * allows users to edit the metadata of a concert. See includes/ConcertPostType.php for
 * a description of the various meta attributes.
 */
registerPlugin("concert-sidebar", {
  render() {
    const meta = useSelect((select) => {
      return select("core/editor").getEditedPostAttribute("meta");
    }, []);
    const { editPost } = useDispatch("core/editor");

    /**
     * Sets a meta value for the current post.
     *
     * @param {string} key The key of the meta value that should be set.
     * @param {any} value The value that the meta value should be set to. If this is
     *                    undefined the function is curried.
     * @returns {function} A function that when invoked with a value will set the meta
     *                     `key` to that value.
     */
    function setMeta(key, value = undefined) {
      const setter = (value) => {
        editPost({ meta: { [key]: value } });
      };
      if (typeof value !== "undefined") {
        setter(value);
      }
      return setter;
    }

    /**
     * Sets the concert date meta value. This function exists because the Gutenberg
     * `DateTimePicker` removes the time zone from its dates. We add the time zone back
     * in and set the concert date to the resulting value.
     * @param dateString
     */
    function setDate(dateString) {
      function pad(num) {
        const norm = Math.floor(Math.abs(num));
        return (norm < 10 ? "0" : "") + norm;
      }
      const tz = -new Date(dateString).getTimezoneOffset();
      const diff = tz >= 0 ? "+" : "-";
      dateString += `${diff}${pad(tz / 60)}:${pad(tz % 60)}`;
      setMeta("concert-date", dateString);
    }

    const date = meta["concert-date"]
      ? new Date(meta["concert-date"])
      : new Date();

    return (
      <>
        <PluginSidebarMoreMenuItem target={SIDEBAR.name} icon={SIDEBAR.icon}>
          {SIDEBAR.title}
        </PluginSidebarMoreMenuItem>
        <PluginSidebar {...SIDEBAR}>
          <PanelBody title={__("Date and Time")} initialOpen={true}>
            <DateTimePicker
              currentDate={date}
              onChange={setDate}
              is12Hour={false}
            />
            <TextControl
              label={__("Duration", "wp-concerts")}
              help={__("The duration of the concert in minutes", "wp-concerts")}
              value={meta["concert-duration"]}
              onChange={(duration) =>
                setMeta("concert-duration", parseInt(duration))
              }
            />
          </PanelBody>
          <PanelBody title={__("Location", "wp-concerts")}>
            <TextControl
              label={__("Location", "wp-concerts")}
              value={meta["concert-location"]}
              onChange={setMeta("concert-location")}
            />
            <TextControl
              label={__("Location Extra", "wp-concerts")}
              value={meta["concert-location-extra"]}
              onChange={setMeta("concert-location-extra")}
            />
            <TextControl
              label={__("Address", "wp-concerts")}
              value={meta["concert-location-address"]}
              onChange={setMeta("concert-location-address")}
            />
            <TextControl
              label={__("Website", "wp-concerts")}
              value={meta["concert-location-url"]}
              onChange={setMeta("concert-location-url")}
            />
          </PanelBody>
          <PanelBody title={__("Organizer", "wp-concerts")}>
            <TextControl
              label={__("Name", "wp-concerts")}
              value={meta["concert-organizer"]}
              onChange={setMeta("concert-organizer")}
            />
            <TextControl
              label={__("Homepage", "wp-concerts")}
              value={meta["concert-organizer-url"]}
              onChange={setMeta("concert-organizer-url")}
            />
          </PanelBody>
          <PanelBody title={__("Cancel", "wp-concerts")} initialOpen={false}>
            <ToggleControl
              label={__("The concert is cancelled", "wp-concerts")}
              help={__(
                "Cancel the concert if it cannot take place as scheduled.",
                "wp-concerts"
              )}
              checked={meta["concert-cancelled"]}
              onChange={setMeta("concert-cancelled")}
            />
            {meta["concert-cancelled"] && (
              <TextareaControl
                label={__("Message", "wp-concerts")}
                help={__("Why was the concert cancelled?", "wp-concerts")}
                value={meta["concert-cancelled-message"]}
                onChange={setMeta("concert-cancelled-message")}
              />
            )}
          </PanelBody>
        </PluginSidebar>
      </>
    );
  },
});
