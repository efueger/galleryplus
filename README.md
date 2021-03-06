# Gallery+
[![Build Status](https://travis-ci.org/interfasys/galleryplus.svg?branch=master)](https://travis-ci.org/interfasys/galleryplus)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/interfasys/galleryplus/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/interfasys/galleryplus/?branch=master)
[![Codacy Badge](https://www.codacy.com/project/badge/02f02de5292e4f7393cd7e5697227a5a)](https://www.codacy.com/app/interfaSys/galleryplus)
[![Code Climate](https://codeclimate.com/github/interfasys/galleryplus/badges/gpa.svg)](https://codeclimate.com/github/interfasys/galleryplus)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/76c41e1a-ed83-46e0-bbad-af925f72e8c9/mini.png)](https://insight.sensiolabs.com/projects/76c41e1a-ed83-46e0-bbad-af925f72e8c9)

Fork of the official Gallery app for ownCloud which brings current features and more to older versions of ownCloud.
 
This branch currently targets ownCloud 8.1.
 
### What is Gallery?

A media gallery for ownCloud which includes previews for all media types supported by your ownCloud installation.

Provides a dedicated view of all images in a grid, adds image viewing capabilities to the files app and adds a gallery view to public links.

![Screenshot](https://oc8demo.interfacloud.com/index.php/s/pvetv4RaIbFhDRb/download)
## Featuring
* Support for large selection of media types (depending on ownCloud setup)
* Large, zoomable previews which can be shown in fullscreen mode
* Sort images by name or date added
* Per album description and copyright statement
* A la carte features (external shares, native svg, etc.)
* Image download straight from the slideshow or the gallery
* Seamlessly jump between the gallery and the files view
* Ignore folders containing a ".nomedia" file
* Native SVG support (disabled by default)
* Mobile support

Checkout the [full changelog](CHANGELOG.md) for more.

## Maintainers

### Current
* [Olivier Paroz](https://github.com/oparoz)
* [Jan-Christoph Borchardt](https://github.com/jancborchardt) (Design)

### Alumni
* [Robin Appelman](https://github.com/icewind1991)

## Contributors

* All the people who have provided patches to [Gallery+](https://github.com/interfasys/galleryplus/pulls?q=is%3Apr+is%3Aclosed), [Gallery](https://github.com/owncloud/gallery/pulls?q=is%3Apr+is%3Aclosed) and [Pictures](https://github.com/owncloud/gallery-old/pulls?q=is%3Apr+is%3Aclosed) over the years


## Requirements

### Browser compatibility
This list is based on the current knowledge of the maintainers and the help they can get.
It will evolve if and when people provide patches to fix all known current issues

#### Fully supported
* Desktop: Firefox, Chrome
* Mobile: Safari, Chrome on Android 5+ and iOS 8.x, BlackBerry 10, Firefox

#### Partially supported
May not look as nice, but should work

* Desktop: Internet Explorer 9-11, Edge
* Mobile: Opera, Chrome on Android 4

#### Not supported
* Desktop: Internet Explorer prior to 9, Safari, Opera
* Mobile: Windows Phone

### Server requirements

#### Required
* ownCloud >= 8.1
* [See ownCloud's requirements](https://doc.owncloud.org/server/8.1/admin_manual/installation/source_installation.html#prerequisites)

#### Recommended
* FreeBSD or Linux server
* PHP 5.5 with caching enabled
* EXIF PHP module
* A recent version ImageMagick with SVG and Raw support
* MySQL or MariaDB instead of Sqlite
* A powerful server with lots of RAM

## Supporting the development

There are many ways in which you can help make Gallery a better product

* Report bugs (see below)
* Provide patches for both [`owncloud/core`](https://github.com/owncloud/core) and the app
* Help test new features by checking out new branches on Github
* Design interface components for new features
* Develop new features. Please consult with the maintainers before starting your journey
* Fund a feature, either via [BountySource](https://www.bountysource.com/teams/interfasys/issues?tracker_ids=9328526) or by directly hiring a maintainer or anybody else who is capable of developing and maintaining it

## Bugs

### Before reporting bugs

* Read the section about server and browser requirements
* Make sure you've disabled the original Pictures app
* Read the "Known issues" section below
* Get the latest version of the app from [the releases page](https://github.com/interfasys/galleryplus/releases)
* Check if they have already been reported in [Gallery](https://github.com/owncloud/gallery/issues) and [Gallery+](https://github.com/interfasys/galleryplus/issues)

### Known issues

#### Within deep folders

* It may take longer to initialise the view as we're parsing every parent folder to look for configuration files

#### Configurations

* If you have write access on a share belonging to another ownCloud instance, editing the configuration file in your folder will also modify the original folder

### When reporting bugs

* Enable debug mode by putting this at the bottom of **config/config.php**

```
DEFINE('DEBUG', true);
```

* Turn on debug level debug by adding **`loglevel" => 0,`** to your **config/config.php** and reproduce the problem
* Check **data/owncloud.log**

Please provide the following details so that your problem can be fixed:

* **Owncloud log** (data/owncloud.log)
* **Browser log** (Hit F12 to gain access)
* ownCloud version
* App version
* Browser version
* PHP version

## Preparation
Here is a list of steps you might want to take before using the app

### Supporting more media types
First, make sure you have installed ImageMagick and its PECL extension.
Next add a few new entries to your **config/config.php** configuration file.

```
  'preview_max_scale_factor' => 1,
  'enabledPreviewProviders' =>
  array (
    0 => 'OC\\Preview\\PNG',
    1 => 'OC\\Preview\\JPEG',
    2 => 'OC\\Preview\\GIF',
    11 => 'OC\\Preview\\Illustrator',
    12 => 'OC\\Preview\\Postscript',
    13 => 'OC\\Preview\\Photoshop',
    14 => 'OC\\Preview\\TIFF'
  ),
```

Look at the sample configuration (config.sample.php) in your config folder if you need more information about how the config file works.
That's it. You should be able to see more media types in your slideshows and galleries as soon as you've installed the app.

### Improving performance

#### Assets pipelining
Make sure to enable "asset pipelining", so that all the Javascript and CSS resources can be mixed together.
This can greatly reduce the loading time of the app.

Read about it in the [Administration Manual](https://doc.owncloud.org/server/8.0/admin_manual/configuration_server/js_css_asset_management_configuration.html)

## Installation

**IMPORTANT**: Make sure you've disabled the original Pictures app

### Installing from archive

* Go to the [the releases page](https://github.com/interfasys/galleryplus/releases)
* Download the latest release/archive to your server's **owncloud/apps/** directory
* Unpack the app
* **IMPORTANT**: Rename it to galleryplus

### Installing from Git

In your terminal go into the **owncloud/apps/** directory and then run the following command:
```
$ git clone https://github.com/interfasys/galleryplus.git
```

Now you can activate it in the apps menu. It's called Gallery

To update the app go inside you **owncloud/apps/galleryplus/** directory and type:
```
$ git pull --rebase
```

## List of patches
None so far
