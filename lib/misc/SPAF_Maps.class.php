<?php
/* ----------------------------------------------------------------------------
  SPAF_Maps.class.php
 ------------------------------------------------------------------------------
  version  : 1.00 BETA 2
  author   : martynas@solmetra.com
 ------------------------------------------------------------------------------
  Map class
 --------------------------------------------------------------------------- */
 
class SPAF_Maps {
  // {{{
  // !!! EDITABLE CONFIGURATION ===============================================
  var $google_api_key = '';
                                // Your Google Maps API key. Google Maps will 
                                // not display if this is blank or incorrect!
                                // Please note that API Key is bound to a host
                                // (domain name) that you are trying to display 
                                // maps on. A key generated for one domain will
                                // not work on another - even on "localhost"
                                // Get your key free at:
                                // http://www.google.com/apis/maps/signup.html
                                // You will need a Google Account to signup 
                                
  var $max_results = 10;        
                                // A maximum number of results to find
                                
  var $width = '500px';         
                                // Width of map area in pixels (px) 
                                // or percents (%)
  
  var $height = '300px';        
                                // Height of map area in pixels (px) 
                                // or percents (%)
  
  var $show_control = true;     
                                // Show map manipulation controls (move, zoom)
  
  var $show_type = true;        
                                // Show map type selection (map, satellite, 
                                // etc.)
  
  var $show_overlay = true;     
                                // Show overlay markers of the query results 
                                // along with basic info on them (i.e. 
                                // population)
                                
  var $secondary_search = true; 
                                // If search by specified query produces no 
                                // results, repeat the search without query - 
                                // just the specified country.
  
  var $use_sockets = false;     
                                // true - geocode data will be fetched by 
                                // opening direct socket connection to HTTP port
                                // of the geocode webservice.
                                //
                                // false - function file_get_contents() will be
                                // used. PHP ini value of allow_url_fopen must 
                                // be set to on.
  
  
  // !!! DO NOT CHANGE ANYTHING BELOW THIS LINE ===============================
  // }}}
  // {{{
  var $google_api_url = 'http://maps.google.com/maps?file=api&amp;v=2&amp;key={key}';
  var $geonames_url = 'http://ws.geonames.org/search?q={query}&maxRows={rows}&style=LONG';
  var $query = '';
  var $country = '';
  var $results = '';
  var $default = '';
  var $service = 'search?q=';
  var $offset = '';
  var $style = 'medium';
  var $fcode = '';
  var $lang = '';
 
  // }}}
  // {{{
  function SPAF_Maps ($query = '', $country = '') {
    // set query and country
    $this->query = $query;
    $this->country = $country;
    
    // set default (if query produces zero results) - default New York
    $this->default = array(
      'name'        => 'New York', 
      'lat'         => '43.00028',
      'lng'         => '-75.50028',
      'geonameId'   => '5128638', 
      'countryCode' => 'US', 
      'countryName' => 'United States', 
      'fcl'         => 'A', 
      'fcode'       => 'ADM1', 
      'fclName'     => 'country, state, region,...', 
      'fcodeName'   => 'first-order administrative division', 
      'population'  => '19274244' 
    );
  }
  // }}}
  // {{{
  function setConfig ($key, $val) {
    $this->$key = $val;
    return true;
  }
  // }}}
  // {{{
  function setCountry ($country) {
    $this->country .= ' '.$country;
    return true;
  }
  // }}}
  // {{{
  function setQuery ($query) {
    $this->query = $query;
    return true;
  }
  // }}}
  // {{{
  function setMaxResults ($max) {
    $this->max_results = $max;
    return true;
  }
  // }}}
  // {{{
  function getResults () {
    // check if results were already fetched
    if (!is_array($this->results)) {
      $this->fetchResults();
    }
    
    return $this->results;
  }
  // }}}
  // {{{
  function showMap () {
    // check if results were already fetched
    if (!is_array($this->results)) {
      $this->fetchResults();
    }
    
    // get coordinates of the first location
    if (isset($this->results[0])) {
      $current = &$this->results[0];
    }
    else {
      $current = $this->default;
      $this->results[] = $current;
    }
    
    // determine correct zoom level
    //$zoom = $this->calcZoom(&$current);
    $zoom = $this->calcZoom($current);
    
    // prepare url
    $url = str_replace('{key}', $this->google_api_key, $this->google_api_url);
    
    // start map code
    echo '<script src="'.$url.'" type="text/javascript"></script>'."\r\n".
         '<script type="text/javascript">'."\r\n".
         '//<![CDATA['."\r\n".
         ' var map = null;'."\r\n";
    
    // create a function for adding markers
    echo 'function createMarker(point, descr) {'."\r\n".
         '  var marker = new GMarker(point);'."\r\n".
         '  GEvent.addListener(marker, "click", function() {'."\r\n".
         '    marker.openInfoWindowHtml(descr);'."\r\n".
         '  });'."\r\n".
         '  return marker;'."\r\n".
         '}'."\r\n";

    // begin main function
    echo 'function SPAF_Maps_load() {'."\r\n".
         '  if (GBrowserIsCompatible()) {'."\r\n";
       
    // create object and center it  
    echo  '    map = new GMap2(document.getElementById("spaf_map"));'."\r\n".
          '    map.setCenter(new GLatLng('.$current['lat'].', '.$current['lng'].'), '.$zoom.');'."\r\n";

    // add controls
    if ($this->show_control) {
      echo '    map.addControl(new GSmallMapControl());'."\r\n";
    }
    
    if ($this->show_type) {
      echo '    map.addControl(new GMapTypeControl());'."\r\n";
    }
    
    // add result overlay markers
    if ($this->show_overlay) {
      $cnt = sizeof($this->results);
      for ($i = 0; $i < $cnt; $i++) {
        $location = &$this->results[$i];
        $description = '<strong>'.$this->javaScriptEncode($location['name']).'</strong><br />';
        if ($location['fcode'] == 'PPLC') {
          $description .= 'A capital of '.$location['countryName'].'<br />';
        }
        if (isset($location['population']) && ($location['population'] > 0)) {
          $description .= 'Population '.number_format($location['population'], 0, '.', ',').'<br />';
        }
        // enclose caption with style
        $description = '<span style="color: #000000;">'.$description.'</span>';
        echo 'map.addOverlay(createMarker(new GLatLng('.$location['lat'].', '.$location['lng'].'), \''.$description.'\'));'."\r\n";
      }
    }
    
    // end map code
    echo '  }'."\r\n".
         '}'."\r\n".
         '//]]>'."\r\n".
         '</script>'."\r\n";
    
    // put div
    echo '<div id="spaf_map" style="width: '.$this->width.'; height: '.$this->height.'"></div>';
    
    // execute event
    echo '<script type="text/javascript">'."\r\n".
         'window.onload = SPAF_Maps_load;'."\r\n".
         'window.onunload = GUnload;'."\r\n".
         '</script>';
    
    return true;
  }
  // }}}
  // {{{
  function showLocationControl ($properties = '') {
    // check if results were already fetched
    if (!is_array($this->results)) {
      $this->fetchResults();
    }
    
    // prepent properties with whitespace
    if ($properties != '') {
      $properties = ' '.$properties;
    }
    
    // create function
    echo '<script type="text/javascript">'."\r\n".
         'function changeMarker (pos) {'."\r\n".
         '  dta = pos.split(\' \');'."\r\n".
         '  map.panTo(new GLatLng(dta[0], dta[1]));'."\r\n".
         '  map.setZoom(dta[2]);'."\r\n".
         '}'."\r\n".
         '</script>'."\r\n";
    
    // begin
    echo '<select'.$properties.' onchange="changeMarker(this.options[this.selectedIndex].value);">'."\r\n";
    
    // show options
    $cnt = sizeof($this->results);
    for ($i = 0; $i < $cnt; $i++) {
      //$location = &$this->results[$i];
    $location = $this->results[$i];
      echo '<option value="'.$location['lat'].' '.$location['lng'].' '.$this->calcZoom($location).'">'.$location['name'].' ('.$location['countryName'].')</option>'."\r\n"; 
    }
    
    // show a no-results sign
    if ($cnt == 0) {
      echo '<option value="">-- no results --</option>'."\r\n";
    }
    
    // end
    echo '</select>'."\r\n";
    
    return true;
  }
  // }}}
  // {{{ 
  function calcZoom (&$location) {
    // get primary zoom level based on location type
    switch ((isset($location['fcl']) && $location['fcl'] ? $location['fcl'] : 'not set - go to default case')) {
      case 'A':
        $zoom = 5;
        break;
      case 'P':
        $zoom = 10;
        break;
      default:
        $zoom = 8;
        break;
    }
    
    // modify zoom type based on population
    $mod = 0;
    if (isset($location['population'])) {
      $mod = floor($location['population'] / 5000000);
      if ($mod > 2) {
        $mod = 2;
      }
    }
    
    return $zoom - $mod;
  }
  // }}}
  // {{{
  function fetchResults ($repeat = false) {
    // prepare fetch url
    if ($repeat) {
      $url = str_replace(
        array('{service}','{query}', '{rows}','{style}'),
        array($this->service,$this->country, $this->max_results,$this->style),
        $this->geonames_url);
    }
    else {
      $url = str_replace(
        array('{service}','{query}', '{rows}','{style}'),
        array($this->service,urlencode($this->query), $this->max_results,$this->style),
        $this->geonames_url);
    }
    
    // add country filtering
    if ($this->country != '') {
      $url .= '&country='.$this->country;
    }
	
	// offset
	if ($this->offset != '') {
		$url .= '&startRow='.$this->offset;
	}

	// choose verbosity
	if ($this->fcode != '') 
		$url .= $this->fcode;
		
	//choose language
	if ($this->lang != '')
		$url .= '&lang='.$this->lang;

//	var_dump($url);	
    
    // fetch url
    if ($this->use_sockets) {
      $xml = $this->fetchUrl($url);
    }
    else {
      $xml = file_get_contents($url);
    }
    // chech if file was actually fetched
    if ($xml === false) {
      $this->results = array();
      return false;
    }
    
    // parse fetched XML
    // get all items
    $this->results = array(); 
    preg_match_all('/<geoname>(.*)<\/geoname>/isU', $xml, $arr, PREG_SET_ORDER);

    // parse each individual item
    while (list(, $item) = each($arr)) {
      preg_match_all('/<([a-z"= .12]+)>(.*)<\/[a-z12]+>/isU', $item[1], $params, PREG_SET_ORDER);
      $location = array();
      while (list(, $param) = each($params)) {
        $location[$param[1]] = $param[2];
      }
      $this->results[] = $location;
    }
    // check if search shoud be repeated with less restrictive query
    if (sizeof($this->results) == 0 && $this->secondary_search && !$repeat) {
      $this->fetchResults(true);
    }
    
    return true;
  }
  // }}}
  // {{{
  function javaScriptEncode ($str) {
    $str = str_replace("\\", "\\\\", $str);
    $str = str_replace("'", "\\'", $str);
    $str = str_replace("\r\n", '\r\n', $str);
    $str = str_replace("\n", '\r\n', $str);
    return $str;
  }
  // }}}
  // {{{
  function fetchUrl ($url) {
    // parse URL
    if (!$elements = @parse_url($url)) {
      return '';
    }
    
    // add default port
    if (!isset($elements['port'])) {
      $elements['port'] = 80;
    }
    
    // open socket
    $fp = fsockopen($elements['host'], $elements['port'], $errno, $errstr, 20);
    if (!$fp) {
      return '';
    }
    
    // assemble path
    $path = $elements['path'];
    if (isset($elements['query'])) {
      $path .= '?'.$elements['query'];
    }
    
    // assemble HTTP request header
    $request  = "GET $path HTTP/1.1\r\n";
    $request .= "Host: ".$elements['host']."\r\n";
    $request .= "Connection: Close\r\n\r\n";
    
    // send HTTP request header and read output
    $result = '';
    fwrite($fp, $request);
    while (!feof($fp)) {
      $result .= fgets($fp, 128);
    }
    
    // close socket connection
    fclose($fp);
    
    // strip extra text from result
    return preg_replace('/^[^<>]*(<.*>)[^<>]*$/s', '$1', $result);
  }
  // }}}
}   
?>