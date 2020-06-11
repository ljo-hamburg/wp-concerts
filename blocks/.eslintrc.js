module.exports = {
  parserOptions: {
    sourceType: "module",
  },
  extends: ["plugin:react/recommended"],
  rules: {
    "react/prop-types": "off",
    "react/display-name": "off",
  },
  settings: {
    react: {
      version: "detect",
    },
  },
};
