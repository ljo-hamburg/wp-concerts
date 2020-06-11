import React from "react";
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";
import { InnerBlocks } from "@wordpress/block-editor";

const BLOCK_NAME = "wp-concerts/cast";

/**
 * Registers the cast block. The block primarily exists as a wrapper of cast members.
 */
registerBlockType(BLOCK_NAME, {
  title: __("Cast", "wp-concerts"),
  description: __("Specify the cast of a concert.", "wp-concerts"),
  keywords: [__("Players", "wp-concerts")],
  category: "common",
  icon: "admin-users",
  edit({ className }) {
    return (
      <div className={className}>
        <h2>{__("Cast", "wp-concerts")}</h2>
        <InnerBlocks
          allowedBlocks={["wp-concerts/cast-member"]}
          renderAppender={InnerBlocks.ButtonBlockAppender}
        />
      </div>
    );
  },
  save() {
    return (
      <div>
        <h2>{__("Cast", "wp-concerts")}</h2>
        <InnerBlocks.Content />
      </div>
    );
  },
});
