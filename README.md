# WP Concerts

![Build](https://github.com/ljo-hamburg/wp-concerts/workflows/Build/badge.svg)
![Lint](https://github.com/ljo-hamburg/wp-concerts/workflows/Lint/badge.svg)

A WordPress plugin to support concerts. Used by the [LJO Hamburg](https://ljo-hamburg.de/).

## Installation

Installing the plugin is as easy as with every other plugin. Download the latest release, upload it to your site and activate it. The plugin automatically updates itself from GitHub releases  via [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker).

For more information such as requirements see the `plugin/readme.txt` file.

## Development

### Installing the Dependencies

In order to develop the plugin, you need [Node.js](https://nodejs.org/en/) and [Composer](https://getcomposer.org/). You also need [GNU `gettext`](https://www.gnu.org/software/gettext/) to compile translations. To get started run the following commands:

```shell
npm install
composer install
```

This will install all dependencies required to build the plugin.

### Building the Plugin

The plugin provides several npm scripts that can be used to accomplish common tasks. Building the plugin is as easy as

```shell
npm run build
```

This will simply execute `gulp` which is used as a build system. By default Gulp will compile the plugin into a `build` folder. You can change the build director by providing a `--dest` argument like so:

```
npm run build -- --dest=dist
```

The additional `--` is required to tell npm to pass the `--dest` argument to the build script.

The command above will compile a production version of the plugin. In development you likely want additional features (such as the development autoloader). To compile the plugin for development run

```
npm run build-dev
```

The plugin will be built into the `build` folder (which can be overridded using `--dest` as above). The `build` folder can then be mounted inside a docker container or VM or be symlinked to a WordPress installation in the `plugins` folder.

In development you can automatically recompile if you change a file. To do so run

```
npm run watch
```

This will watch most of the development files and recompile them if any changes are detected. Note that the dependencies in `composer.json` and `package.json` are not watched. If you change any of those you need to manually trigger a rebuild using `npm run build-dev`. The `--dest` arguments works with this task as well.

The `build` directory is not automatically cleaned. Existing files are overridden but orphaned files are not deleted. If you want to clean the `build` directory just run

```
npm run clean
```

### Creating Translations

The plugin is completely translation-ready. Translations are compiled from the `languages` folder automatically. If you want to add a new translatable strings be sure to run

```
npm run gettext
```

afterwards to update the translation files to reflect the updated code. Note that the gettext utility needs to be installed separately.

### Linting the Code

To check the code for style violations run

```
npm run lint
```

Alternatively you can lint only a part of the code using one of the following:

```
npm run lint:php
npm run lint:js
npm run lint:scss
```

