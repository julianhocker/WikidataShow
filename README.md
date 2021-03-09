# Wikidata Show
This extension for mediawiki adds data to a wiki page based on a link set in the Semantic MediaWiki and grabs data from wikidata. It is bascially developed for the project [school archives](https://schularchive.bbf.dipf.de) and handles the following information from wikidata. You can also use it as a blueprint to write your own extension to get data from wikidata. Supported items:
* P18: picture 
* P6375: adress
* P856: website
* P625: coordinates
* P1448: names
* P1249: earliest written record
* P571: inception
* P31: instance of
* P137: operator
* Link to wikipedia
* P227: Link to GND (German national library)
* P569: year of birth
* P570: year of death
* P937: work location
* P106: occupation
* P108: employer
* P1066: student of

The extension adds three magic words to your mediawiki:
1. wikidatashow: creates a box like to one in the example
1. wikidatashowlite: takes in the p-value you want to show and only shows this information (fits great if you want to display local data from semantic mediawiki together with data from wikidata)
1. wikidatashoweasy: does the same as wikidatashowlite, but gives back the raw result without any formatting. If you type {{#wikidatashowlite:P18, Q1533809}}, you get: https://d-nb.info/gnd/2012843-5if you type {{#wikidatashowwasy:P18, Q1533809}}, you get: 2012843-5. wikidatashoweasy can be used with all p-values, wikidatashowlite only with p-values pre-defined above.
Example:

![alt text](https://raw.githubusercontent.com/julianhocker/wikidatashow/master/example.png "Example of extension")

## Installation
1. Add "freearhey/wikidata": "3.2" to your composer.json of the wiki in the section "require"
2. Run composer update --no-dev
3. Clone this repo via git clone https://github.com/julianhocker/WikidataShow.git into extensions 
4. Add wfLoadExtension('WikidataShow'); to your LocalSettings.php

## Usage
### Wikidatashow
This way you get a box with all the data defined above directly from wikidata
* type the magic work {{#wikidatashow:}} into a page and it will get the corresponding information based on the smw-attribute 'Wikidata ID'
* you can also provide the wikidata-id directly, e.g. {{#wikidatashow:Q1533809}}

### Wikidatashowlite
This way you only get single items from wikidata. This function is provided for links to wikipedia, image, adress, website, link to GND
* type the magic word {{#wikidatashowlite:}} to a page, giving the p-value of the information you need or 'wikipedia', e.g. {{#wikidatashowlite:P18}} to get the corresponding image. 
* if you do not have Semantic Mediawiki running, just provide the wikidata ID as second paramenter to get the data: 
{{#wikidatashowlite:P227|Q1533809}}

## Dependencies
The extension was tested on Semantic MediaWiki 3.1.5. and MediaWiki 1.34.0. You do not need Semantic MediaWiki to make it running, but then you have to provide the wikidata-ID directly.  Translation is right now done in English and German, please feel free to add more translation ;). 

## Known issues 
* Please open issues if you encounter problems 
* connection to wikidata should be done more nicely in the code
* I also added the magic word wikidatashoweasy, that just returns the value from wikidata. It seemed handy at first, but I did not see great usage since many values are not proper formatted/usefull in wikidata