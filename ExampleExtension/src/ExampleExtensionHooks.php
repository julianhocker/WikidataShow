<?php
use Wikidata\Wikidata;
#use MediaWiki\Title;
class ExampleExtensionHooks {
   // Register any render callbacks with the parser
   public static function onParserFirstCallInit( Parser $parser ) {

      // Create a function hook associating the "example" magic word with renderExample()
      $parser->setFunctionHook( 'example', [ self::class, 'renderExample' ] );
   }

   // Render the output of {{#example:}}.
   public static function renderExample( Parser $parser, $param = '') {

      // The input parameters are wikitext with templates expanded.
      // The output should be wikitext too.
      ##todo: get actual page name
      
		##get wikidatalink from actual page	
		$endpoint = "https://schularchive.bbf.dipf.de/api.php";
		$url = "$endpoint?action=ask&query=[[$param]]|?Wikidatalink|limit=5&format=json";
		$json_data = file_get_contents($url);		
		$apiresponse = json_decode($json_data, true);
 
		#https://schularchive.bbf.dipf.de/api.php?action=ask&query=[[Gymnasium_Leopoldinum_Passau]]|?Wikidatalink|limit=5&format=json
	
		$wikidataentry = $apiresponse['query']['results']['Gymnasium Leopoldinum Passau']['printouts']['Wikidatalink'][0];#get wikidatalink from api
		$wikidataentry = substr($wikidataentry, 30, 100);
		$wikidata = new Wikidata();#init object to get info from wikidata
		$entity = $wikidata->get($wikidataentry); # get data for entitiy (with Q-number)
		#example: https://www.wikidata.org/w/api.php?action=wbgetentities&ids=Q1533809&format=json
		$properties = $entity->properties->toArray(); #convert data to array to make handling easier
		#print_r($properties);#debug
		$result = $entity ->  label;
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
		
		#$title = Title::newFromText($name);
		#$title = $this->getSkin()->getTitle();
		#$title = Title::getText( );
		#$title = $mTextform; 
		/*echo  $entity ->  label; #auto-fill the name of the page
echo "</h1>";
echo "<p></p>";
echo "<img src='$image' height='400px'>";#create image
echo "<p>Coordinates: ";
print_r($coordinates = $properties['P625']-> values[0] -> label);
echo "</p><p>Street Adress: ";
#print_r($properties['P637']);
print_r($properties['P6375']-> values[0]-> label);
echo "</p><p>Website: ";
print_r($properties['P856']-> values[0] -> label);

echo "</p><p>Names:</p><ol>";

echo "</ol></body></html>";*/
		#:http://commons.wikimedia.org/wiki/Special:FilePath/
        $output = "==$result==        
[[File:$image|thumb|$result]] $website, $adress, $coordinates
$nameresult \n$wikidataentry"; #$title";  
        #$output = "param1 is $param1 and param2 is $param2 and param3 is $param3";

      return $output;
   }
}
