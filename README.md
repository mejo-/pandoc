# Nextcloud Pandoc

Small integration app to convert text files using [Pandoc](https://pandoc.org/).

Used by [Nextcloud Collectives](https://github.com/nextcloud/collectives/) to
allow exporting a collective using Pandoc.

## Usage

The app is not meant to be used directly. Its main purpose is to provide a
Pandoc exporter to other apps.

Requests to the route `/apps/pandoc/convertFile?fileIds=<id1>,<id2>&to=<format>&from=<format>`
will return the converted content of the files for listed fileIds.

The query params `to` (default 'plain' or app setting `default_output_format`)
and `from` (default 'gfm' - github flavor markdown) are optional.

Opening `/apps/pandoc?fileIds=123,456` in the browser will convert the content
of files with id 123 and 456 to the format configured as default by app setting
`default_output_format` (of 'plain' by default).

## Installation

In your Nextcloud instance, simply navigate to **»Apps«**, find the
**»Pandoc«** app and enable it.

## Requirements

[Pandoc](https://pandoc.org/) needs to be installed on the Nextcloud host.

## Configuration

It's possible to define an alternative default output format by setting the
app config variable`default_output_format`. This setting will be passed to
pandoc with the `--to` commandline option.

In order to use a custom pandoc lua writer as default output format, do
something like the following:

```
php occ config:app:set --value="/usr/local/share/pandoc/data/bbcode_phpbb.lua"
-- pandoc default_output_format
```

## Maintainer

* Jonas <jonas@freesources.org>

## Licence

AGPL v3 or later. See [COPYING](COPYING) for the full licence text.
