<?php
/*
   Data-oriented XML parsing code.
*/


#-- simplification wrapper around XML parser -------------------------------
#  namespace-aware, folds URIs down to expected xmlnamespace prefixes;
#  this is mostly handled internally, not by php-xml
#
class easy_xml {

   var $xp;
   var $xml;

   #-- general xmlns= mappings -> we don't want to deal with URIs
   #   but get shortened abbreviations instead (and not unpredictable ones)
   var $xmlns = array(
      "http://www.w3.org/1999/02/22-rdf-syntax-ns#" => "rdf",
      "http://purl.org/dc/elements/1.1/" => "dc",
      "http://purl.org/rss/1.0/modules/wiki/" => "rss-wiki",
      "http://purl.org/rss/1.0/" => "rss",   // (at least its similar enough)
      "http://purl.org/atom/ns#" => "atom",
      "DAV:" => "dav",
#<eee># "urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/" => "ms-dav-time",
      "http://xmlns.com/foaf/0.1/" => "foaf",
   );
   #-- final xmlns= qualifier down-mapping,
   #   think of this as "local xmlns:tag renaming"
   var $xmlns2;

   #-- tagname mappings
   var $map = array(
      //"xmlns-prefix:weird-tag-name" => "desired",
   );

   #-- tag name stack
   var $current, $parent, $stack=array();


   #-- prepare parser
   function easy_xml($xml="", $charset=NULL, $targetcoding="ISO-8859-1") {

      #-- init (no "_ns" parser, because we can handle that better)
      $this->xp = xml_parser_create($charset);
      xml_parser_set_option($this->xp, XML_OPTION_CASE_FOLDING, false);
      xml_parser_set_option($this->xp, XML_OPTION_SKIP_WHITE, true);
      xml_parser_set_option($this->xp, XML_OPTION_TARGET_ENCODING, $targetcoding);
      $this->xml = &$xml;

      #-- handlers
      xml_set_character_data_handler($this->xp, array(&$this,"cdata"));
      xml_set_element_handler($this->xp, array(&$this,"start"), array(&$this,"end"));
      
      #-- we take care of namespaces ourselves
      $this->xmlns2 = array();
   }


   #-- action 
   function parse($more_xml="") {
      $r = xml_parse($this->xp, trim($this->xml . $more_xml), $_is_final=TRUE);
      if ($e = xml_get_error_code($this->xp)) {
         trigger_error("XML error #$e: " . xml_error_string($e), E_WARNING);
      }
      unset($this->xml);
      unset($this->stack);
      unset($this->current);
      unset($this->parent);
      unset($this->l);
      unset($this->map);
      unset($this->as_content);
      unset($this->as_list);
      $r = xml_parser_free($this->xp) && ($r);
      unset($this->xp);
      return($r);
   }


   #-- map ugly XMLNS urls to known identifiers/internal representation
   function tag($tag) {
      while (($l = strrpos($tag, ":"))
         and ($prefix = substr($tag, 0, $l))
         and (isset($this->xmlns2[$prefix])) )
      {
         $ns = $this->xmlns2[$prefix];
         if ($ns == $prefix) {
            break;
         }
         
         #-- strip known xmlns qualifier/moniker
         $tag = substr($tag, $l+1);
         if ($ns) {
            $tag = $ns . ":" . $tag;
         }
         else break;
      }

      #-- and also rewrite to preferred tag names
      if (isset($this->map[$tag])) {
         $tag = $this->map[$tag];
      }
      
      return($tag);
   }


   #-- log <opening> tags
   function start($xp, &$tag, &$attr) {
      #-- normalize attributes and discover namespaces
      if ($attr) {
         $a = array();
         foreach ($attr as $i=>$v) {
            if (strncmp($i, "xmlns:", 6) == 0) {
               $this->xmlns($xp, substr($i, 6), $v);
            }
            else {
               $i = $this->tag($i);
               $a[$i] = $v;
            }
         }
         $attr = $a;
      }

      #-- normalize tag names
      $tag = $this->tag($tag);
      
      #-- track where we are
      if ($this->current) {
         $this->stack[] = $this->current;
      }
      $this->parent = $this->current;
      $this->current = $tag;
   }


   #-- track </end> tags
   function end($xp, &$tag) {
      $tag = $this->tag($tag);
      $this->current = $this->parent;
      $this->parent = array_pop($this->stack);
   }


   #-- data extr
   function cdata($xp, $data) {
   }


   #-- we handle namespaces ourselves to decipher silly URIs and
   #   get rid of uncommon prefixes
   function xmlns($xp, $short, $uri) {
      #-- setup back-mapping to OUR preferred xmlnamespace abbr
      if ($desired = $this->xmlns[$uri]) {
         if (($short != $desired)
         and (isset($this->xmlns2["rw:"][$short])
          or !isset($this->xmlns2[$short]))  )
         {
            $this->xmlns2[$short] = $desired;
            $this->xmlns2["rw:"][$short] = 1;  // mark as overwritable entry
         }
         // prevents 1:1-conversions
      }
      #-- keep it
      else {
         $this->xmlns[$uri] = $short;  // log
       //  $this->xmlns2[$short] = $short;
      }
   }


}//class



#-- simple data/array XML file --------------------------------------------
#  can decode only two-level data containers
class easy_xml_data extends easy_xml {

   #-- which elements always to expect multiple times (= becomes list)
   var $as_list = array();
   
   #-- which attributes to transmove into cdata
   var $as_content = array();


   #-- append string data to collection
   function cdata($xp, $data) {
      if (trim($data)) {
         $this->l[$this->parent][$this->current] = $data;
      }
   }


   #-- extract single blocks from array collection list
   function end($xp, $tag) {

      parent::end($xp, $tag);

      if (isset($this->l[$tag])) {
         #-- make list
         if (isset($this->{$tag}) || in_array($tag, $this->as_list)) {
            #-- convert into list
            if (isset($this->{$tag}) && !isset($this->{$tag}[0])) {
               $this->{$tag} = array(
                  0 => $this->{$tag}
               );
            }
            $this->{$tag}[] = $this->l[$tag];
         }
         #-- no list (asis)
         else {
            $this->{$tag} = $this->l[$tag];
         }
         unset($this->l[$tag]);
      }
   }


   #-- converts certain expected tag attributes into cdata
   function start($xp, &$tag, &$attr) {
      parent::start($xp, $tag, $attr);
      foreach ($attr as $i=>$content) {
         if ($this->as_content[$tag]==$i) {
            $this->cdata($xp, $content);
         }
      }
   }

}//class



#-- special simplifications for RSS- and Atom- XML files --------------------
class easy_xml_rss extends easy_xml_data {

   #-- add a few mappings to make RSS and Atom look similar
   function easy_xml_rss($xml, $cs=NULL, $tc="ISO-8859-1") {
      parent::easy_xml($xml, $cs, $tc);
      // kill some of _our_ namespace prefixes
      $this->xmlns2 += array(
//         "rss-wiki" => "",
         "rss" => "",
         "atom" => "",
         "dc" => "",
      );
      // rename tags (mainly for Atom)
      $this->map += array(
         // atom:
         "feed" => "channel",
         "entry" => "item",
         "content" => "description",
         "id" => "guid",
         // dc:
         "date" => "pubDate",
      );
      // tag attributes to auto-convert into content
      $this->as_content += array(
         "link"=>"href",
      );
      // always make list-array of items
      $this->as_list = array(
         "item",
      );
   }
}//class




#-- decode into structs (fast, but annoying to work with) ------------------
class ewiki_xml_fast {

   var $data;
   var $tags;

   #-- do
   function parse() {
      xml_parse_into_struct($this->xp, $this->xml, $this->data, $this->tags);
      
      #-- fix xmlns, tag names
      $data = &$this->data;
      $tags = &$this->tags;
      foreach ($data as $i=>$d) {
         if ($new = $this->tag($data[$i]["tag"])) {
            $data[$i]["tag"] = $new;
         }
         if (isset($data[$i]["attributes"])) {
            foreach ($data[$i]["attributes"] as $key=>$val) {
               if ($new = ewiki_short_xmlns($key, $xmlns)) {
                  unset($data[$i]["attributes"][$key]);
                  $data[$i]["attributes"][$new] = $val;
               }
            }
         }
      }
      foreach ($tags as $key=>$val) {
         if ($new = $this->tag($key)) {
            unset($tags[$key]);
            $tags[$new] = $val;
         }
      }

      unset($this->xml);
   }
}//class


?>