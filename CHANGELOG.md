# Changelog

All Notable changes to `AssetLibrary` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## unreleased

## 0.8.7 - 2023-03-14
- Fix: Detaching single asset when entity id is uuid

## 0.8.6 - 2023-03-06
- Fix: detaching asset with entity id being uuid fails

## 0.7.6 - 2020-07-23
- added: extra params to AddAsset and AssetUploader to allow to set custom collection and disk on the media record.

## 0.7.4 - 2020-07-23
- changed image dimensions calculations and added getWidth and getHeight functions to asset.

## 0.7.3 - 2020-07-23
- fixed asset deletion

## 0.7.2 - 2020-06-17

- add support for laravel 7
- add isused and is unused functions on asset as a shortcut to know if this asset is linked to a model.

## 0.7.1 - 2020-02-17

### Changed

- Assetuploader base64 now requires a filename. if uploaded through addAsset a random default is chosen as filename.

## 0.7.0 - 2020-01-21

### Changed

- Replace asset now also takes type and locale to make sure we only replace the asset we want.
- SortAsset now also take locale and the parameters have been swapped in order.
- Added checks for invalid parameters to assetuploader

## 0.6.6 - 2020-01-21

### Changed

- Replace asset now also takes type and locale to make sure we only replace the asset we want. These will be required in the next major version 0.7.0

## 0.6.5 - 2019-12-17

### Changed

- the same asset can now be uploaded to a model multiple times.

## 0.6.4 - 2019-11-29

### Changed

- detach asset now requires type and locale so we dont remove the asset for other types/locales

## 0.6.3 - 2019-11-29

### Changed

- the same asset can now be uploaded to a model for different types or locales

## 0.6.1 - 2019-11-06

### Fixed
- provided fallback for getDimensions if there is no dimensions data

## 0.6.0 - 2019-11-06

### Added
- Manipulation of assets now happens on the newly added commands: AddAsset, DeleteAsset, DetachAsset, SortAsset, ReplaceAsset
- Added HasAsset interface to replace the HasMedia interface on the models.
- added imagemigrate command

### Changed
- Moved asset and assettrait to root folder
- Moved Assetuploader to Application folder
- Refactored the public api
- Assettrait is now only used for accessing assets and no longer for manipulation of assets

## NEXT - 2018-07-26

### Added
- Docs updated

## NEXT - 2018-07-24

### Added
- The addFile and addFiles method on the assetTrait now return the added assets.
- Filenames will now be slugified to avoid weird behaviour when accessing the files throught urls.

## NEXT - YYYY-MM-DD

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
