const { src, dest, series, parallel, watch } = require("gulp");
const sourcemaps = require("gulp-sourcemaps");
const sass = require("gulp-sass");
const postcss = require("gulp-postcss");
const babel = require("gulp-babel");
const gulpWebpack = require("webpack-stream");
const webpack = require("webpack");
const named = require("vinyl-named");
const gulpif = require("gulp-if");
const ejs = require("gulp-ejs");
const rename = require("gulp-rename");
const exec = require("gulp-exec");
const msgfmt = require("gulp-potomo");
const clean = require("gulp-clean");
const argv = require("yargs").option("dest", {
  type: "string",
  description: "The output directory of the build.",
}).argv;

const production = process.env.NODE_ENV === "production";
const data = require("./package.json");
const composer = require("./composer.json");

sass.compiler = require("dart-sass");

let BUILD_DIR = "build";
if (argv.dest) {
  BUILD_DIR = argv.dest;
}

/**
 * Installs PHP dependencies in the build directory using composer. This step only
 * installs runtime dependencies and no build dependencies.
 */
function installDependencies() {
  return src(["composer.json", "composer.lock"])
    .pipe(dest(BUILD_DIR))
    .pipe(exec(`composer install --working-dir=${BUILD_DIR} --no-dev`));
}

/**
 * Copies the includes directory into the build folder. There is no compilation required
 * for the PHP files.
 */
function copyIncludes() {
  return src("includes/**/*").pipe(dest(`${BUILD_DIR}/includes`));
}

/**
 * Copies the WordPress templates from the `templates` folder into the build folder. The
 * templates are copied into the root of the `build` directory so that the WordPress
 * system can find them. No additional compilation is needed.
 */
function copyTemplates() {
  return src("templates/**/*").pipe(dest(BUILD_DIR));
}

/**
 * Compiles the JavaScript files in the `scripts` directory and combines them into a
 * single `wp-concerts.js` file that is then output into the build directory.
 *
 * The files are transpiled with Babel and are accompanied by sourcemaps.
 */
function compileScripts() {
  return src("scripts/*.js")
    .pipe(sourcemaps.init())
    .pipe(babel())
    .pipe(sourcemaps.write("."))
    .pipe(dest(`${BUILD_DIR}/scripts`));
}

/**
 * Compiles the Gutenberg Blocks scripts. This task invoked webpack and outputs its
 * result to the `blocks` directory in the build folder.
 *
 * See the `webpack.config.js` file for details on this step.
 */
function buildBlocks() {
  return src("blocks/*.js")
    .pipe(named())
    .pipe(gulpWebpack(require("./webpack.config"), webpack))
    .pipe(dest(`${BUILD_DIR}/blocks`));
}

/**
 * Compiles the styles of the script into the build directory. All styles are compiled
 * using Sass/SCSS and are post-processed with PostCSS and Autoprefixer.
 *
 * Sourcemaps are generated for all files.
 */
function buildStyles() {
  return src(["styles/*.scss", "!styles/_*.scss"])
    .pipe(sourcemaps.init())
    .pipe(
      sass({ outputStyle: production ? "compressed" : "expanded" }).on(
        "error",
        sass.logError
      )
    )
    .pipe(postcss([require("autoprefixer")]))
    .pipe(sourcemaps.write("."))
    .pipe(dest(BUILD_DIR));
}

/**
 * Copies the files from the `theme` folder into the root of the build folder. Any files
 * with an `.ejs` extension are compiled using EJS. The templates receive the complete
 * `package.json` object as `data` and `composer.json` object as `composer`.
 *
 * If we are building a release in GitHub CI the `release` object contains the
 * respective webhook payload object.
 */
function copyPluginFiles() {
  return src(["plugin/*"])
    .pipe(
      gulpif(
        /.ejs$/,
        ejs({
          data,
          composer,
          release:
            process.env.GITHUB_EVENT_NAME === "release"
              ? require(process.env.GITHUB_EVENT_PATH).release
              : {},
        })
      )
    )
    .pipe(gulpif(/.ejs$/, rename({ extname: "" })))
    .pipe(dest(BUILD_DIR));
}

/**
 * Compiles the translations from the `languages` folder into `.mo` files and outputs
 * them into the `languages` folder of the build directory.
 */
function compileMoTranslations() {
  return src("./languages/*.po")
    .pipe(msgfmt())
    .pipe(dest(`${BUILD_DIR}/languages`));
}

/**
 * Runs `wp i18n make-json` on all translation files in the `languages` directory and
 * outputs the resulting files into the `languages` folder of the build directory.
 */
function compileJedTranslations() {
  return src("./languages/*.po").pipe(
    exec(
      `./vendor/bin/wp i18n make-json --no-purge "<%= JSON.stringify(file.path) %>" ${BUILD_DIR}/languages/`
    )
  );
}

/**
 * Dumps the composer autoload files for production builds. This will generate an
 * authoritative classmap for all PHP files.
 */
function dumpAutoload() {
  const command = ["composer", "dump-autoload", `--working-dir=${BUILD_DIR}`];
  if (production) {
    command.push("--classmap-authoritative");
  }
  return src(["composer.json", "composer.lock"])
    .pipe(dest(BUILD_DIR))
    .pipe(exec(command.join(" ")));
}

/**
 * Removes temporary files from the build directory. This should always be the last step
 * in a build pipeline.
 */
function cleanup() {
  return src(`${BUILD_DIR}/composer.{json,lock}`, { read: false }).pipe(
    clean()
  );
}

/**
 * Watches for changes in the source code and triggers the appropriate build steps to
 * rebuild that part of the theme.
 */
function serve() {
  watch("includes/**/*.php", copyIncludes);
  watch("templates/**/*.php", copyTemplates);
  watch("scripts/**/*.js", compileScripts);
  watch("blocks/**/*.js", buildBlocks);
  watch("styles/**/*.scss", buildStyles);
  watch("plugin/**/*", copyPluginFiles);
  watch(
    "languages/*.po",
    parallel(compileMoTranslations, compileJedTranslations)
  );
}

/**
 * Builds the whole theme. This is triggered by `npm run build` and `npm run build-dev`.
 */
exports.default = series(
  parallel(
    series(
      parallel(installDependencies, copyIncludes, copyTemplates),
      dumpAutoload
    ),
    compileScripts,
    buildBlocks,
    buildStyles,
    copyPluginFiles,
    compileMoTranslations,
    compileJedTranslations
  ),
  cleanup
);

/**
 * Builds the theme and them watches changes. This is triggered by `npm run watch`.
 */
exports.watch = series(exports.default, serve);
