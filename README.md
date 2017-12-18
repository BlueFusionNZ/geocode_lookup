THIS MODULE IS PROBABLY NO LONGER NEEDED
========================================
You should check out https://www.drupal.org/project/geolocation_address_link instead and see if it does what you need.



Address & Geolocation
=====================

This is a very short module that takes address information entered into a 
Drupal Address field and populates a Geolocation field on the same Node


Please report bugs, comments, pull requests & improvements at the [github repo](https://github.com/BlueFusionNZ/geocode_lookup)

## Requirements

* Drupal 8
* Address
* Geolocation

## Installation

* Usual D8 method
* You will then need to make some manual code changes at:
  * geocode_lookup.module - change the presave hook to reference the node/entity and field names for your site.
* You probably want to hide the Geolocation field in the form UI and display it as a map in the view mode.
 
