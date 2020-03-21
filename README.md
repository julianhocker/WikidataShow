# Wikidata Show

##Installation
add 		"freearhey/wikidata": "3.2"   to your composer.json

composer ...
git clone "mein repo" 
wfLoadExtension('WikidataShow');

##Usage
-type the magic work {{wikidatashow:}} into a page and it will get the corresponding information based on the smw-item 'Wikidatalink'
-you can also provide the wikidata-id directly, e.g. {{wikidatashow:}}
