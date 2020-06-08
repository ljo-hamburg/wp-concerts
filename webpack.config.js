const path = require("path");
const DependencyExtractionWebpackPlugin = require("@wordpress/dependency-extraction-webpack-plugin");

const isProduction = process.env.NODE_ENV === "production";
const mode = isProduction ? "production" : "development";

module.exports = {
  mode,
  output: {
    filename: "[name].js",
    path: path.resolve(process.cwd(), "build"),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: ["babel-loader"],
      },
    ],
  },
  plugins: [new DependencyExtractionWebpackPlugin({ injectPolyfill: true })],
  devtool: "source-map",
};
