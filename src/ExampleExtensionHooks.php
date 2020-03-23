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
			#handling pages where wikidaalink is not defined:
			try {
                  if (empty($apiresponse['query']['results'][$title]['printouts']['Wikidatalink'][0])){
                        throw new Exception("not defined");
                }else {
			        $wikidataentry = $apiresponse['query']['results'][$title]['printouts']['Wikidatalink'][0];#get wikidatalink from api
			        $wikidataentry = substr($wikidataentry, 30, 100);
                }
            }
                    //catch exception
            catch(Exception $e) {
                $wikidataentry = "Q1533809";
            }

		}else{
			$wikidataentry = $param1;
		}
		$wikidata = new Wikidata();#init object to get info from wikidata
		$entity = $wikidata->get($wikidataentry,"de"); # get data for entitiy (with Q-number)
		$properties = $entity->properties->toArray(); #convert data to array to make handling easier		
		#$result = $entity ->  label;
		
		#get information
        #picture (p18)
		try {
            if (empty($properties['P18'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                $image = $properties['P18'] -> values[0] -> label;
          	    $image = substr($image, 51, 100);#hack, trim the link to wikimedia commons
          	    $imagewiki = "[[File:$image|400px]]";
            }
        }
        //catch exception
        catch(Exception $e) {
          $imagewiki = $e->getMessage();
        }
        #adress
		try {
            if (empty($properties['P6375'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                $adress = $properties['P6375']-> values[0]-> label;
            }
        }
        //catch exception
        catch(Exception $e) {
          $adress = $e->getMessage();
        }
        #website
		try {
            if (empty($properties['P856']-> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                $website = $properties['P856']-> values[0] -> label;
            }
        }
        //catch exception
        catch(Exception $e) {
          $website = $e->getMessage();
        }
        #coordinates
		try {
            if (empty($properties['P625'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                $coordinates = 	$properties['P625']-> values[0] -> label;
            }
        }
        //catch exception
        catch(Exception $e) {
          $coordinates = $e->getMessage();
        }
        #names
		try {
            if (empty($properties['P1448'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                	$names = $properties['P1448']-> values;
                	$nameresult = "";
                	foreach($names as $item) {
                		$oldname = $item -> label;
                		$nametime = $item -> qualifiers[0] -> value;
                		$nameresult .= "\n# $oldname; $nametime";
                	}
            }
        }
        //catch exception
        catch(Exception $e) {
          $nameresult = $e->getMessage();
        }
        #founded
        try {
             if (empty($properties['P1249'] -> values[0] -> label)){
                 throw new Exception("not defined");
                    }else {
                        $founded = $properties['P1249']-> values[0] -> label;
                    }
                }
                //catch exception
                catch(Exception $e) {
                  $founded = $e->getMessage();
                }
         #instances
		try {
            if (empty($properties['P31'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                	$instances = $properties['P31']-> values;
                	$instanceresult = "";
                	foreach($instances as $item) {
                		$instance = $item -> label;
                		$instanceresult .= "\n# $instance";
                	}
            }
        }
        //catch exception
        catch(Exception $e) {
          $instanceresult = $e->getMessage();
        }






		
		#get links
		$url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=$wikidataentry&format=json";
        $json_data = file_get_contents($url);
        $apiresponse = json_decode($json_data, true);
		try {
            if (empty($apiresponse['entities'][$wikidataentry]['sitelinks']['dewiki']['title'])){
                throw new Exception("not defined");
            }else {
                $wikipedialink = $apiresponse['entities'][$wikidataentry]['sitelinks']['dewiki']['title'];
                $wikipedialink = str_replace(" ","_",$wikipedialink); #hack to make link pretty
            }
        }
        //catch exception
        catch(Exception $e) {
          $wikipedialink = $e->getMessage();
        }

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
|$imagewiki
|-
!Ist ein
|$instanceresult
|-
!Wikipedialink
|https://de.wikipedia.org/wiki/$wikipedialink
|}";
		return $output;
   }
}