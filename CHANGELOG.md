# Changelog

All Notable changes to `AssetLibrary` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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
