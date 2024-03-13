# Islandora Rights Statements Badge

## Introduction

Islandora Rightsstatements provides a field formatter containing a Rightsstatements.org button with a link to the statement URI and its details.

It reads the Rightsstatements.org URI from a Drupal field, and builds the HTML from the appropriate assets at Rightsstatements.org.

Example badge image:

![Example badge](https://raw.githubusercontent.com/rightsstatements/rightsstatements.github.io/master/files/buttons/InC.dark.png)

The badge will only display on objects with a Rightsstatements.org URI in some defined element.

## Installation

Install as usual, see [https://www.drupal.org/docs/extending-drupal/installing-modules](https://www.drupal.org/docs/extending-drupal/installing-modules) for further information.


```
composer require lehigh-university-libraries/islandora_rights_statements
```

## Configuration

Use the Rights Statement badge field formatter on your `field_rights`

## Maintainers/Sponsors

Current maintainers:

* [Joe Corall](https://github.com/joecorall)

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)

## Attribution

This module began as a fork/port of the Drupal 7 module [bondjimbond/islandora_rightsstatements](https://github.com/bondjimbond/islandora_rightsstatements)
