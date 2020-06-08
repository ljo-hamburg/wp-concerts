module.exports = {
  extends: [
    "stylelint-config-sass-guidelines",
    "stylelint-config-rational-order",
  ],
  rules: {
    "string-quotes": "double",
    "max-nesting-depth": 3,
    "selector-max-id": 1,
    "selector-max-compound-selectors": 5,
    "selector-no-qualifying-type": null,
    "order/properties-alphabetical-order": null,
    "scss/at-extend-no-missing-placeholder": null,
  },
};
