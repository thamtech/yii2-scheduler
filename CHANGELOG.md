Change Log
==========

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/)
and this project adheres to [Semantic Versioning](https://semver.org).


[v0.5.0]
--------

### Added
- Action column with text-based "View Details" link on log list

### Changed
- Updated Travis CI and Scrutinizer settings
- Include "Success"/"Failure" text in log result column
- Replaced `mtdowling/cron-expression` library with `dragonmantank/cron-expression`


[v0.4.0]
--------

### Fixed
- #2: An unused parameter would cause a warning when migrations are executed (honsa)


[v0.3.0]
--------

### Changed
- Log improvements


[v0.2.0]
--------

### Added
- Support for optional Mutex configuration instead of a raw database lock


### Changed
- Documentation updates
- Internal implementation improvements


[v0.1.0]
--------

### Added
- Initial implementation of configuration-driven fork of webtoolsnz/yii2-scheduler
