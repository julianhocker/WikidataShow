<?php
use Wikidata\Wikidata; #https://github.com/freearhey/wikidata
class WikidataShowHooks {
   // Register any render callbacks with the parser
   public static function onParserFirstCallInit( Parser $parser ) {

      // Create a function hook associating the magic word with renderExample()
      $parser->setFunctionHook( 'wikidatashow', [ self::class, 'renderExample' ] );
      $parser->setFunctionHook( 'wikidatashowlite', [ self::class, 'wikidatashowlite' ] );
      $parser->setFunctionHook( 'wikidatashoweasy', [ self::class, 'wikidatashoweasy' ] );
   }

   // Render the output of {{#example:}}.
   public static function renderExample( Parser $parser, $param1 = '') {
        global $wgScriptPath;
        global $wgServer;

        $language = wfMessage( 'language')->plain();
        $wikilanguage = $language ."wiki";
		if (empty($param1)){#check, if input is empty. If it is not, get wikidata-id from api
			$title = $parser->getTitle()->getText();
			$titleunderscores = $parser->getTitle()->getDBKey();
			##get wikidatalink from actual page	
			$endpoint = "$wgServer$wgScriptPath/api.php";
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
		    if (empty ($wikidata->get($wikidataentry,$language))){
		        throw new Exception('not defined');
		    }else{
		        $entity = $wikidata->get($wikidataentry,$language); # get data for entitiy (with Q-number)
            	$properties = $entity->properties->toArray(); #convert data to array to make handling easier
		    }
		}
		catch(Exception $e){
		    return "wrong Wikidata ID";
		}

		#$result = $entity ->  label;
		
		#get information
        #picture (p18)
        $image = self::getData($properties, "P18");

        if($image == "not defined"){
            $imagewiki = "not defined";
        }else{
            $image = substr($image, 51);#hack, trim the link to wikimedia commons
            $imagewiki = "[[File:$image|400px]]";
        }
        $adress = self::getAdress($wikidataentry, $wikilanguage);

        $website = self::getData($properties, "P856");
        $coordinates = self::getData($properties, "P625");
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

        $earliestRecord = date_parse(self::getData($properties, "P1249"));
        $inception =  date_parse(self::getData($properties, "P571"))['year'];

        #operator
        $operatorResult = self::getMultipleData($properties, "P137");

        #instances
        $instanceResult = self::getMultipleData($properties, "P31");

        $gnd = self::getData($properties, "P227");
		if ($gnd == "not defined"){
		    $gndlink = "not defined";
		 } else {
		    $gndlink = "https://d-nb.info/gnd/$gnd";
		}
		#get wikipedialink
		$wikipedialink = self::getWikipediaLink($wikidataentry, $wikilanguage);
		if ($wikipedialink == "not defined"){
            $wikipedialink =  "not defined";
        } else {
            $wikipedialink = "https://$language.wikipedia.org/wiki/$wikipedialink";
        }

		$websiteString = wfMessage( 'website')->plain();
		$adressString = wfMessage( 'adress')->plain();
		$mapString = wfMessage( 'map')->plain();
		$namesString = wfMessage( 'names')->plain();
		$foundedString = wfMessage( 'founded')->plain();
		$imageString = wfMessage( 'image')->plain();
		$instanceString = wfMessage( 'instance')->plain();
		$wikipediaString = wfMessage( 'wikipedia')->plain();
		$gndString = wfMessage( 'gndlink')->plain();
		$operatorSring = wfMessage('operator')->plain();
        $output = "
{| class='wikitable'
!$websiteString
|$website
|-
!$adressString
|$adress
|-
!$mapString
|$coordinates
|-
!$namesString
|$nameresult
|-
!$foundedString
|$earliestRecord[year], $inception
|-
!$imageString
|$imagewiki
|-
!$instanceString
|$instanceResult
|-
!$operatorSring
|$operatorResult
|-
!$wikipediaString
|$wikipedialink
|-
!$gndString
|$gndlink
|}";
		return $output;
   }

   public static function wikidatashowlite( Parser $parser, $param1 = '', $param2 = '') {
        global $wgScriptPath;
        global $wgServer;
        $language = wfMessage( 'language')->plain();
        $wikilanguage = $language ."wiki";
        $title = $parser->getTitle()->getText();
        $titleunderscores = $parser->getTitle()->getDBKey();
        ##get wikidatalink from actual page
        if(empty($param2)){#if param2 is not set, take the wikidatalink from the actual page
            $endpoint = "$wgServer$wgScriptPath/api.php";
            $url = "$endpoint?action=ask&query=[[$titleunderscores]]|?Wikidata_ID|limit=5&format=json";
            $json_data = file_get_contents($url);
            $apiresponse = json_decode($json_data, true);
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
        } else {
            $wikidataentry = $param2;
        }

        $wikidata = new Wikidata();#init object to get info from wikidata
        #check if we get valid information from wikidata
        try{
            if (empty ($wikidata->get($wikidataentry,$language))){
                throw new Exception('not defined');
            }else{
                $entity = $wikidata->get($wikidataentry,$language); # get data for entitiy (with Q-number)
               	$properties = $entity->properties->toArray(); #convert data to array to make handling easier
        	}
        }
        catch(Exception $e){
            return "wrong Wikidata ID";
        }

        #get results depending on the input
        switch ($param1){
            case "P18":#picture
                $image = self::getData($properties, "P18");
                if($image == "not defined"){
                    return wfMessage('unknown')->plain();
                }else{
                    $image = substr($image, 51);#hack, trim the link to wikimedia commons
                    return "[[File:$image|400px]]";
                }
            case "P227":#gnd entry
                $gnd = self::getData($properties, "P227");
                if ($gnd == "not defined"){
                    return wfMessage( 'unknown')->plain();
                } else {
                    return "https://d-nb.info/gnd/$gnd";
                }
            case "wikipedia":#wikipedia
                $wikipedialink = self::getWikipediaLink($wikidataentry, $wikilanguage);
                if ($wikipedialink == "not defined"){
                    return wfMessage( 'unknown')->plain();
                } else {
                    return "https://$language.wikipedia.org/wiki/$wikipedialink";
                }
            case "P856"://website
                 return self::getData($properties, "P856");
            case "P6375"://street adress
                return self::getData($properties, "P6375");
            case "P31": //instances
                return self::getMultipleData($properties, "P31");
            case "P137"://operator
                return self::getMultipleData($properties, "P137");
            case "P1249": //earliestRecord
                return date_parse(self::getData($properties, "P1249"))['year'];
            case "P571": //inception
                return date_parse(self::getData($properties, "P571"))['year'];
            case "P569": //year of birth
                return date_parse(self::getData($properties, "P569"))['year'];
            case "P570": //year of death
                return date_parse(self::getData($properties, "P570"))['year'];
            case "P937"://work location
                return self::getMultipleData($properties, "P937");
            case "P106"://occupation
                return self::getMultipleData($properties, "P106");
            case "P108"://employer
                return self::getMultipleData($properties, "P108");
            case "P1066"://student of
                return self::getMultipleData($properties, "P1066");
            case "P625"://coordinates
                $coordinates = self::getData($properties, "P625");
                preg_match_all('/[\d]+.[\d]+/', $coordinates, $split);
                $latitude = array_values($split)[0][0];
                $longitude = array_values($split)[0][1];
                return "$longitude, $latitude";
            default:
                return "not defined";
        }
   }

   public static function wikidatashoweasy(Parser $parser, $param1 = ''){
               global $wgScriptPath;
               global $wgServer;
               $language = wfMessage( 'language')->plain();
               $wikilanguage = $language ."wiki";
               $title = $parser->getTitle()->getText();
               $titleunderscores = $parser->getTitle()->getDBKey();
               ##get wikidatalink from actual page
               $endpoint = "$wgServer$wgScriptPath/api.php";
               $url = "$endpoint?action=ask&query=[[$titleunderscores]]|?Wikidata_ID|limit=5&format=json";
               $json_data = file_get_contents($url);
               $apiresponse = json_decode($json_data, true);
               #handling pages where wikidaalink is not defined:
               try {
                    if (empty($apiresponse['query']['results'][$title]['printouts']['Wikidata ID'][0])){
                        throw new Exception("not defined");
                    }else {
                       $wikidataentry = $apiresponse['query']['results'][$title]['printouts']['Wikidata ID'][0];//get wikidatalink from api
                    }
               }
               //catch exception
               catch(Exception $e) {
                   return "No wikidata entry found";
               }
               $wikidata = new Wikidata();#init object to get info from wikidata
               //check if we get valid information from wikidata
               try{
                   if (empty ($wikidata->get($wikidataentry,$language))){
                       throw new Exception('not defined');
               		    }else{
               		        $entity = $wikidata->get($wikidataentry,$language); //get data for entitiy (with Q-number)
                           	$properties = $entity->properties->toArray(); //convert data to array to make handling easier
               		    }
               		}
               		catch(Exception $e){
               		    return "wrong Wikidata ID";
               		}
       return self::getData($properties, $param1);
   }

      public static function getData($properties = '', $pvalue = ''){#get data if p-value only has one value
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

      public static function getMultipleData($properties = '', $pvalue = ''){#get data if p-value has multiple values
          try {
              if (empty($properties[$pvalue] -> values[0] -> label)){
                  throw new Exception("not defined");
              }else {
                  $instances = $properties[$pvalue]-> values;
                  $instanceResult = "";
                  foreach($instances as $item) {
                      $instance = $item -> label;
                      $instanceResult .= "\n# $instance";
                      }
                  return $instanceResult;
                  }
              }
          //catch exception
          catch(Exception $e) {
              return $e->getMessage();
          }
      }

      public static function getWikipediaLink($wikidataentry = "", $wikilanguage = ""){
            $url = "https://www.wikidata.org/w/api.php?action=wbgetentities&ids=$wikidataentry&format=json";
            $json_data = file_get_contents($url);
            $apiresponse = json_decode($json_data, true);
      		try {
                  if (empty($apiresponse['entities'][$wikidataentry]['sitelinks'][$wikilanguage]['title'])){
                      throw new Exception("not defined");
                  }else {
                      $wikipedialink = $apiresponse['entities'][$wikidataentry]['sitelinks'][$wikilanguage]['title'];
                      $wikipedialink = str_replace(" ","_",$wikipedialink); #hack to make link pretty

                      return $wikipedialink;
                  }
            }
            //catch exception
            catch(Exception $e) {
                return $e->getMessage();
            }
      }
}
