# Kill Field Collection Items Module

Kill field collection entities created by the 
[Field Collection](https://www.drupal.org/project/field_collection) module.
Useful when broken code leaves orphan field collection items.

*Warning!* Use this module at your peril. It could break your site. Seriously.

## Requirements

* Drupal 8
* [Field Collection](https://www.drupal.org/project/field_collection) module

## Installation

The module can be installed via the
[standard Drupal installation process](http://drupal.org/node/895232).

## Usage

* Go to Configuration | Content authoring | Kill field collection items
  (/admin/config/content/field_collection_kill_items). You'll get a list
  of field collection item entities.
* Type in the ids of the items you want to kill, separated by commas.
* Click the button.
* (Optional) Weep because you have killed your site.

