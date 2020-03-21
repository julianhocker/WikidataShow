<?php
use Wikidata\Wikidata; #https://github.com/freearhey/wikidata
class ExampleExtensionHooks {
   // Register any render callbacks with the parser
   public static function onParserFirstCallInit( Parser $parser ) {

      // Create a function hook associating the "example" magic word with renderExample()
      $parser->setFunctionHook( 'wikidatashow', [ self::class, 'renderExample' ] );
   }
   
   // Render the output of {{#example:}}.
   public static function renderExample( Parser $parser, $param1 = '') {

		if (empty($param1)){#check, if input is empty. If it is not, get wikidata-id from api
			$title = $parser->getTitle()->getText();
			$titleunderscores = $parser->getTitle()->getDBKey();
			##get wikidatalink from actual page	
			$endpoint = "https://schularchive.bbf.dipf.de/api.php";
			$url = "$endpoint?action=ask&query=[[$titleunderscores]]|?Wikidatalink|limit=5&format=json";
			$json_data = file_get_contents($url);		
			$apiresponse = json_decode($json_data, true);
			$wikidataentry = $apiresponse['query']['results'][$title]['printouts']['Wikidatalink'][0];#get wikidatalink from api
			$wikidataentry = substr($wikidataentry, 30, 100);
			$control= "1";
		}else{
			$wikidataentry = $param1;
			$control = "2";
		}
		$wikidata = new Wikidata();#init object to get info from wikidata
		$entity = $wikidata->get($wikidataentry,"de"); # get data for entitiy (with Q-number)
		$properties = $entity->properties->toArray(); #convert data to array to make handling easier		
		#$result = $entity ->  label;
		
		#get information
		$image = $properties['P18'] -> values[0] -> label;
		$image = substr($image, 51, 100);#hack, trim the link to wikimedia commons
		$adress = $properties['P6375']-> values[0]-> label;
		$website = $properties['P856']-> values[0] -> label;	
		$coordinates = 	$properties['P625']-> values[0] -> label;
		$names = $properties['P1448']-> values;
		$nameresult = "";
		foreach($names as $item) {
			$oldname = $item -> label;
			$nametime = $item -> qualifiers[0] -> value;
			$nameresult .= "\n# $oldname; $nametime";
		}
		$founded = $properties['P1249']-> values[0] -> label;
		$instances = $properties['P31']-> values;
		$instanceresult = "";
		foreach($instances as $item) {
			$instance = $item -> label;
			#$nametime = $item -> qualifiers[0] -> value;
			$instanceresult .= "\n# $instance";
		}
		
		#get links
			$url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=$wikidataentry&format=json";
			$json_data = file_get_contents($url);		
			$apiresponse = json_decode($json_data, true);
			$wikipedialink = $apiresponse['entities'][$wikidataentry]['sitelinks']['dewiki']['title'];
			$wikipedialink = str_replace(" ","_",$wikipedialink); #hack to make link pretty
		
		
		##make a pretty output of our results
        $output = "
{| class='wikitable'
!Webseite
|$website
|-
!Adresse
|$adress
|-
!Karte
|$coordinates
|-
!Schulnamen
|$nameresult
|-
!Schulgründung
|$founded
|-
!Bild
|[[File:$image|400px]]
|-
!Ist ein
|$instanceresult
|-
!Wikipedialink
|https://de.wikipedia.org/wiki/$wikipedialink
|}";

#{{#display_map:Brandenburg Gate, Berlin, Germany}}";  
        #$output = "param1 is $param1 and param2 is $param2 and param3 is $param3";
		return $output;
   }
}
