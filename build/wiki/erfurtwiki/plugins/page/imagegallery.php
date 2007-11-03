<?php

/*
   This plugins brings together all uploaded/cached images onto one
   page (this usually includes [refrenced] graphics), see internal://

   CSS:
      td.lighter { ... }

   ----

   jeremy mikola <jmikola@arsjerm.net>
       - contributor, added image mime-type filtering
   jeffrey engleman 
       - removed database call in loop significantly increasing performance
*/

 $ewiki_config["action_links"]["gallery"]["info"] = 'More Info';
 
define("EWIKI_GALLERY_WIDTH", 3);
define("EWIKI_PAGE_IMAGEGALLERY", "ImageGallery");
$ewiki_plugins["page"][EWIKI_PAGE_IMAGEGALLERY] = "ewiki_page_image_gallery";

//The main gallery includes only uploaded images and cached images *not* images
// that have been uploaded as attachments to a page or into a section.
// Only pages with no section attribute are included.   
$ewiki_plugins["page"]['MainGallery'] = "ewiki_page_image_gallery";
$ewiki_config['image-galleries']['MainGallery']['section']='';

function ewiki_page_image_gallery($id, $data=0, $action) {
    global $ewiki_config;

   $o = ewiki_make_title($id, $id, 2);

   $mwidth = 120;
   $mscale = 0.7;
   
   #-- fetch and asort images
   $sorted = array();
   $pages = array();
   $result = ewiki_db::GETALL(array("flags", "created", "meta", "refs"));
    while ($row = $result->get()) {
        if ((($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_BINARY)
            && (strpos($row['meta']['Content-Type'], 'image/') === 0)) {
            if(isset($ewiki_config['image-galleries'][$id])){
                foreach($ewiki_config['image-galleries'][$id]as $field=>$value){
                    if($row['meta'][$field]!=$value){
                        continue (2);
            
                    }
                }
            }            
            if (!EWIKI_PROTECTED_MODE || (EWIKI_PROTECTED_MODE_HIDING <= .5)|| ewiki_auth($row["id"], $uu, "binary-get")) {
                $sorted[$row["id"]] = $row["created"];
                $pages[$row["id"]] = $row;
            }           
        } elseif (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT){
            $page_refs[$row["id"]]=$row["refs"];
        } 
    }
    
   arsort($sorted);
   
   #-- start table
   $o .= '<table border="0" cellpadding="10" cellspacing="4">' . "\n";
   $n = 0;
   $num_per_row = EWIKI_GALLERY_WIDTH;
   foreach ($sorted as $image => $uu) {

      $row = $pages[$image];
      $meta = $row["meta"];

      #-- height, width
      $x = $x0 = $meta["width"];
      $y = $y0 = $meta["height"];
      if (! ($x && $y)) {
         $x = $mwidth;
         $y = (int) ($mwidth * $mscale);
      }
      $r = 1;
      if ($y > $mwidth * $mscale) {
         $r = $mwidth * $mscale / $y;
      }
      if ($r > $mwidth / $x) {
         $r = $mwidth / $x;
      }
      $x = (int) ($x * $r);
      $y = (int) ($y * $r);

      #-- get image references
      $ref=array();
      foreach($page_refs as $pageid => $pageref) {
        if(strstr($pageref, $image)){
          if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($pageid, $str_null, "view")) {
            continue;
          }   
          $ref[] = '<a href=?page='.$pageid . '>' . $pageid . '</a>';
        }
        if (count($ref) >= 5) {
          break;
        }
      }

      $ref = implode(", ", $ref);
      
      #-- table lines
      (($n % $num_per_row) == 0) && ($o .= "<tr>\n");

      #-- print a/img tag
      $o .= '<td class="lighter" align="center">'
          . '<a href="' . ewiki_script_binary("", $image) . '">'
          . '<img src="' . ewiki_script_binary("", $image)
          . '" alt="' . $image . '" border="0"'
          . ($x && $y ? ' width="'.$x.'" height="'.$y.'"' : '')
          . ' />'
          . str_replace('/','/ ', urldecode($meta["Content-Location"]))
          . '</a><br />'
          . ($x0 && $y0 ? "{$x0}x{$y0}<br />" : "")
          . $ref
          #-- gallery-actions
          . '<div class="action-links">'
          . ewiki_control_links_list($image, $uuu, $ewiki_config["action_links"]["gallery"])
          . "</div>\n"
          . "</td>\n";

      #-- table lines
      $n++;
      (($n % $num_per_row) == 0) && ($o .= "</tr>\n");

   }

   #-- empty table cells
   if ($n % $num_per_row) {
      while (($n % $num_per_row) && ($n++)) {
         $o .= "<td class=\"lighter\">&nbsp;</td>\n";
      }
      $o .= "</tr>\n";
   }
   $o .= "</table>\n";
    
   return($o);
}

 
?>