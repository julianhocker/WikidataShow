# Wikidata Show
This extension for mediawiki adds data to a wiki page based on a link set in the Semantic MediaWiki and grabs data from wikidata. It is bascially developed for the project 'school archives' and handles the following information from wikidata. You can also use it as a blueprint to write your own extension to get data from wikidata. Supported items:
* P18 (picture) 
* P6375 (adress)
* P856 (website)
* P625 (coordinates)
* P1448 (names)
* P1249 (founding data)
* P31 (instance of)
* Link to wikipedia
* Link to GND (German national library)

Example:

![alt text](https://raw.githubusercontent.com/julianhocker/wikidatashow/master/example.png "Example of extension")

## Installation
1. Add "freearhey/wikidata": "3.2" to your composer.json of the wiki in the section "require"
2. Run composer update --no-dev
3. Clone this repo via git clone https://github.com/julianhocker/wikidatashow.git into extensions 
4. Add wfLoadExtension('WikidataShow'); to your LocalSettings.php

## Usage
### Wikidatashow
This way you get a box with all the data defined above directly from wikidata
* type the magic work {{#wikidatashow:}} into a page and it will get the corresponding information based on the smw-attribute 'Wikidata ID'
* you can also provide the wikidata-id directly, e.g. {{#wikidatashow:Q1533809}}

###Wikidatashotlite
This way you only get single items from wikidata. This function is provided for links to wikipedia, image, adress, website, link to GND
* type the magic word {{#wikidatashowlite:}} to a page, giving the p-value of the information you need or 'wikipedia', e.g. {{#wikidatashowlite:P18}} to get the corresponding image. 

This function only works if you have semantic Mediawiki installed and provide the wikidata ID in the page
## Dependencies

The extension was tested on Semantic MediaWiki 3.1.5. and MediaWiki 1.34.0. You do not need Semantic MediaWiki to make it running, but then you have to provide the wikidata-ID directly. 

## Known issues 
*Please open issues if you encounter problems 
