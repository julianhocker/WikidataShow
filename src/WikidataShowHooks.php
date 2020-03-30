<?php
use Wikidata\Wikidata; #https://github.com/freearhey/wikidata
class WikidataShowHooks {
   // Register any render callbacks with the parser
   public static function onParserFirstCallInit( Parser $parser ) {

      // Create a function hook associating the magic word with renderExample()
      $parser->setFunctionHook( 'wikidatashow', [ self::class, 'renderExample' ] );
   }

   // Render the output of {{#example:}}.
   public static function renderExample( Parser $parser, $param1 = '') {

		if (empty($param1)){#check, if input is empty. If it is not, get wikidata-id from api
			$title = $parser->getTitle()->getText();
			$titleunderscores = $parser->getTitle()->getDBKey();
			##get wikidatalink from actual page	
			$endpoint = "https://schularchive.bbf.dipf.de/api.php";
			$url = "$endpoint?action=ask&query=[[$titleunderscores]]|?Wikidata_ID|limit=5&format=json";
            $json_data = file_get_contents($url);
            $apiresponse = json_decode($json_data, true);
			#handling pages where wikidaalink is not defined:
			try {
                  if (empty($apiresponse['query']['results'][$title]['printouts']['Wikidata ID'][0])){
                        throw new Exception("not defined");
                }else {
			        $wikidataentry = $apiresponse['query']['results'][$title]['printouts']['Wikidata ID'][0];#get wikidatalink from api
                }
            }
             //catch exception
            catch(Exception $e) {
                return "No wikidata entry found";
            }

		}else{
			$wikidataentry = $param1;
		}
		$wikidata = new Wikidata();#init object to get info from wikidata
		#check if we get valid information from wikidata
		try{
		    if (empty ($wikidata->get($wikidataentry,"de"))){
		        throw new Exception('not defined');
		    }else{
		        $entity = $wikidata->get($wikidataentry,"de"); # get data for entitiy (with Q-number)
            	$properties = $entity->properties->toArray(); #convert data to array to make handling easier
		    }
		}
		catch(Exception $e){
		    return "wrong Wikidata ID";
		}

		#$result = $entity ->  label;
		
		#get information
        #picture (p18)
        $image = self::getData($properties, $wikidataentry, "P18");

        if($image == "not defined"){
            $imagewiki = "not defined";
        }else{
            $image = substr($image, 51, 100);#hack, trim the link to wikimedia commons
            $imagewiki = "[[File:$image|400px]]";
        }
        $adress = self::getData($properties, $wikidataentry, "P6375");
        $website = self::getData($properties, $wikidataentry, "P856");
        $coordinates = self::getData($properties, $wikidataentry, "P625");
        #names
		try {
            if (empty($properties['P1448'] -> values[0] -> label)){
                throw new Exception("not defined");
            }else {
                	$names = $properties['P1448']-> values;
                	$nameresult = "";
                	foreach($names as $item) {
                		$oldname = $item -> label;
                		$nametime = date_parse($item -> qualifiers[0] -> value);
                		$nameresult .= "\n# $oldname; $nametime[year]";
                	}
            }
        }
        //catch exception
        catch(Exception $e) {
          $nameresult = $e->getMessage();
        }

        $founded = date_parse(self::getData($properties, $wikidataentry, "P1249"));

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

        $gnd = self::getData($properties, $wikidataentry, "P227");
		if ($gnd == "not defined"){
		    $gndlink = "not defined";
		 } else {
		    $gndlink = "https://d-nb.info/gnd/$gnd";
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
        #$test = self::getData();
		##make a pretty output of our results
		$websiteString = wfMessage( 'website')->plain();
		$adressString = wfMessage( 'adress')->plain();
        $output = "
{| class='wikitable'
!$websiteString
|$website
|-
!$adressString
|$adress
|-
!Karte
|$coordinates
|-
!Schulnamen
|$nameresult
|-
!Schulgründung
|$founded[year]
|-
!Bild
|$imagewiki
|-
!Ist ein
|$instanceresult
|-
!Wikipedialink
|https://de.wikipedia.org/wiki/$wikipedialink
|-
!DNB-Link
|$gndlink
|}";
		return $output;
   }

      public static function getData($properties = '', $wikidataentry = '', $pvalue = ''){#get data if you need only one information
          try {
              if (empty($properties[$pvalue] -> values[0] -> label)){
                throw new Exception("not defined");
              }else {
                return $properties[$pvalue]-> values[0] -> label;
              }
          }
          //catch exception
          catch(Exception $e) {
              return $e->getMessage();
          }
      }
}
