# Changelog
All notable changes to this project will be documented in this file.

## 0.7.0

* Fix: improved plugin loading priorities

## 0.6.9

* Fix: removed short open tags

## 0.6.8

* Fix: backward compatibility with PHP 5.4 and WP 4.4

## 0.6.7

* Fix: recover aggregate ratings in the organizations schema

## 0.6.6

* Feat: select the post types to include the social media tags

## 0.6.5

* Fix: updated static function self::recoverValue

## 0.6.4

* Fix: Prevented PHP Warning messages

## 0.6.3

* Added backward compatibility for employee links

## 0.6.2

* Added backward compatibility for employee images


## 0.6.1

* Added backward compatibility

## 0.6

* Renamed ACF field names and keys to avoid collisions

## 0.5

* Switched Zipcode to Postal Code and allowed for text instead of just numbers
* Added open graph functionality
* Removed unnecessary needed variable in ACF bypass class

## 0.4.1

* Added @id for locations
* Added fallback for priceRange
* Updated for "image" and "priceRange" requirements in not just LocalBusiness schema

## 0.4

* Added JPEGs as possible options for images (not just JPG)
* Gave phone number text fields the business phone number as placeholder if business phone number was defined
* Turn "About" field to textarea instead of text
* Added link to acceptable options in instructions for Country schema
* Added schema and set up for LocalBusiness
* Correcting typo in instructions for Related URLs
* Use correct size (full) for business logo, instead of thumbnail
* Trim white space from inline output of ratings CSS
* Proper implementation of name fallback for individual locations
* Fixed error for "return value in write context" error in certain versions of PHP

## 0.3

* Added Sitelinks Searchbox
* Extending state maxlength to 3 for England and Australia
* Adding name fallback for individual locations
* Kill Yoast schema as long as our plugin is on, instead of just if data was saved
* Adding support for Yoast home description
* Give locations schema it's own meta box

## 0.2

* Added functionality to update plugin inside the WP admin plugins page

## 0.1 - 2018.04.23

* Initial release
