# Wikidata Show
This extension for mediawiki adds data to a wiki page based on a link set in the Semantic MediaWiki and grabs data from wikidata. It is bascially developed for the project 'school archives' and handles the following information from wikidata. You can also use it as a blueprint to write your own extension to get data from wikidata. Supported items:
* P18 (picture) 
* P6375 (adress)
* P856 (website)
* P625 (coordinates)
* P1448 (names)
* P1249 (founding data)
* P31 (instance of)
* Link to German wikipedia
* Link to GND (German national library)

Example:

![alt text](https://raw.githubusercontent.com/julianhocker/wikidatashow/master/example.png "Example of extension")

## Installation
1. add "freearhey/wikidata": "3.2" to your composer.json
2. composer update --no-dev
3. clone this repo via git clone https://github.com/julianhocker/wikidatashow.git into extensions 
4. Add wfLoadExtension('WikidataShow'); to your LocalSettings.php

## Usage
* type the magic work {{wikidatashow:}} into a page and it will get the corresponding information based on the smw-item 'Wikidatalink'
* you can also provide the wikidata-id directly, e.g. {{wikidatashow:Q1533809}}

## Dependencies

The extension was tested on Semantic MediaWiki 3.1.5. and MediaWiki 1.34.0. You do not need Semantic MediaWiki to make it running, but then you have to provide the wikidata-ID directly. 

## Known issues 
* The extension should use a relative path to the wiki to make it more universal
* there should be the standard attribute Wikidata ID used, instead of Wikidatalink