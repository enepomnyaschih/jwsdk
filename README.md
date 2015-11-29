## Manual

https://github.com/enepomnyaschih/jwsdk/wiki

## Changelog

### 0.7.1 (November 29, 2015)

Improvements:

- Scripts return correct error code now

### 0.7 (October 11, 2015)

New features:

- Added ([`<jwdebug></jwdebug>` meta tag support](https://github.com/enepomnyaschih/jwsdk/wiki/jWidget-SDK-documentation.-JS-preprocessing)) in JS code ([#15](https://github.com/enepomnyaschih/jwsdk/issues/15))

Improvements:

- jWidget SDK doesn't pring everything into annoying log files anymore. All output goes to stdout and stderr ([#86](https://github.com/enepomnyaschih/jwsdk/issues/86))
- Prevented recompression on build error ([#91](https://github.com/enepomnyaschih/jwsdk/issues/91))
- Made both command line arguments optional ([#94](https://github.com/enepomnyaschih/jwsdk/issues/94))

Bug fixes:

- Fixed known issue in JS obfuscator: regular expressions are processed properly now ([#97](https://github.com/enepomnyaschih/jwsdk/issues/97))
- Removed IE 9 compatibility meta tags from default HTML templates ([#89](https://github.com/enepomnyaschih/jwsdk/issues/89))
- Fixed LESS and Sass build errors ([#90](https://github.com/enepomnyaschih/jwsdk/issues/90))
- Fixed release build errors on Unix ([#95](https://github.com/enepomnyaschih/jwsdk/issues/95))
- Fixed a minor issue in JS file merging algorithm ([#96](https://github.com/enepomnyaschih/jwsdk/issues/96))
