<?php
$words = new MOD_words();
?>

<?php
if (isset($trip_data[$trip->trip_id])) {
    echo '<h3>'.$words->get('Trip_SubtripsTitle').'</h3>';
	if ($isOwnTrip) {
		echo '<p class="small">'.$words->get('Trip_draganddrop').'</p>';
	}
	
	echo '<ul id="triplist">';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		
		echo '<li id="tripitem_'.$blogid.'"'.($isOwnTrip ? ' style="cursor:move;"' : '').'>';
		echo '<div class="floatbox">';
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="subcolumns">
  <div class="c25l" style="width: 15%">
    <div class="subcl">
<?php
        if ($blog->blog_start) {
            ?>
            <h2 class="trip_date"><?php echo date("M d", strtotime($blog->blog_start)) ?><br />
            <span style="font-size: 14px;"><?php echo date("Y", strtotime($blog->blog_start)) ?></span>
            </h2>
            <!--<div class="calendar calendar-icon-<?php echo date("m", strtotime($blog->blog_start)) ?>">
              <div class="calendar-day"><?php echo date("j", strtotime($blog->blog_start)) ?></div>
            </div> -->
<?php
		}
?>
<!-- End of contents for left subtemplate -->
    </div>
  </div>
  
  <div class="c75r" style="width: 85%">
    <div class="subcr">
      <!-- Contents for right subtemplate -->
<?php
        echo '<h3 class="borderless">';
		if ($blog->name) {
			echo '<span id="b-geo'.$blogid.'" style="font-size: 14px;">'.$blog->name.'</span>';
            if ($isOwnTrip) {
            ?> <a href="#" id="b-geo-edit<?=$blogid?>" class="button">Set location</a> 
            <?php } ?>
            <a href="#" onclick="javascript: map_<?=$trip->trip_id?>.setCenter(new GLatLng(<?=$blog->latitude?>, <?=$blog->longitude?>), 8); return false;"><img src="styles/YAML/images/iconsfam/map.png" alt="Trip Map & Details"> </a><br /><?php
        }
        echo '<a href="blog/'.$trip->handle.'/'.$blogid.'" id="b-title'.$blogid.'">'.$blog->blog_title.'</a><br />';
        echo '</h3>';
		if ($blog->blog_text) {
			if (!$isOwnTrip && strlen($blog->blog_text) > 400) {
				$blogtext = substr($blog->blog_text, 0, 400);
				$blogtext_add = '...<br /><a href="blog/'.$trip->handle.'/'.$blogid.'">'.$words->get('ReadMore').'...</a>';
			} else {
				$blogtext = $blog->blog_text;
			}
			echo '<div id="b-text'.$blogid.'">'.$blogtext.'</div>';
			echo isset($blogtext_add) ? '<p id="id="b-text'.$blogid.'">'.$blogtext.'</p>' : '';
		} elseif ($isOwnTrip) {
            echo '<div id="b-text'.$blogid.'">click here to add text!</div>';
        }
        if ($isOwnTrip) {
            echo '<p><a href="trip/'.$trip->trip_id.'/subedit/'.$blogid.'" class="button">Edit</a></p>';
        }
?>
<?php
	if ($isOwnTrip) {
?>

<script type='text/javascript' src='script/lightview.js'></script>
<script type="text/javascript">
// TESTING LIGHTVIEW
// document.observe('dom:loaded', function() {
  // Lightview custom event for the demos, see the documentation for more examples.
  // $('b-geo-edit<?=$blogid?>').observe('lightview:opened', function(event) {
    // new Effect.Pulsate($('lightview').down('.lv_Caption'), { pulses: 3 });
  // });

  // Ajax Form Demo
  // $('b-geo-edit<?=$blogid?>').observe('click', function(event) {
    // event.stop();
    // $('geoajaxform').observe('submit', submitGeoForm);
	// Lightview.show({
	  // href: '#geo-entry',
	  // rel: 'inline',
	  // title: '<?=$words->get('label_search_location')?>',
	  // options: {
	    // autosize: true,
	  // }
	// });
  // });
  
  // });


    function setGeo(item,geonameid) {
        var add = '?item='+ item +'&geoid=' + geonameid;
        new Ajax.Request('blog/ajax/post/'+add,
          {
            method:'get',
            onSuccess: function(transport){
              var response = transport.responseText || "no response text";
              alert("Success! \n\n" + response);
            },
            onFailure: function(){ alert('Something went wrong...') }
          })
        $('b-geo'+item).hide();
        }
    function setMap(geonameid,a,b,c,d,e,f,g,h) {
        }
    new Ajax.InPlaceEditor('b-geo<?=$blogid?>', 'blog/ajax/post/', {
            onComplete: function(form, value) {
                    Lightview.show({
                        href: 'geo/suggestLocation/'+ value,
                        rel: 'ajax',
                        options: {
                          title: 'results',
                          menubar: false,
                          topclose: true,
                          autosize: false,
                          width: 600,
                          height: 300,
                          ajax: {
                            parameters: form.serialize // the parameters from the form
                          }
                        }
                      })
                // return '?item=<?=$blogid?>&geoid=' + decodeURIComponent(value)
                // return '?s=' + decodeURIComponent(value)
            },
            externalControl: 'b-title-edit',
            formClassName: 'inplaceeditor-form-big',
            cols: '25',
            ajaxOptions: {method: 'get'},
            savingText: 'Searching...',
            externalControl: 'b-geo-edit<?=$blogid?>',
            externalControlOnly: true
        })

    new Ajax.InPlaceEditor('b-title<?=$blogid?>', 'blog/ajax/post/', {
            callback: function(form, value) {
                return '?item=<?=$blogid?>&title=' + decodeURIComponent(value)
            },
            externalControl: 'b-title-edit',
            formClassName: 'inplaceeditor-form-big',
            cols: '25',
            ajaxOptions: {method: 'get'}
        })

    new Ajax.InPlaceEditor('b-text<?=$blogid?>', 'blog/ajax/post/', {
            callback: function(form, value) {
                return '?item=<?=$blogid?>&text=' + decodeURIComponent(value)
            },
            externalControl: 'b-text-edit',
            rows: '5',
            cols: '55',
            ajaxOptions: {method: 'get'}
        })
</script>
<?php } ?>
<!-- End of contents for right subtemplate -->
    </div>
  </div>
</div> 
<?php
		echo '</li>';
			
	}
	echo '</ul>';
?>

<?php
	if ($isOwnTrip) {
?>
<script type="text/javascript">

  
function submitGeoForm(event) {
  // block default form submit
  event.stop();
  
  params = Form.serialize('geoajaxform');
  search = params['search'].value;
  
  Lightview.show({
    href: 'geo/suggestLocation/'+ search,
    rel: 'ajax',
    options: {
      title: 'results',
	  menubar: false,
	  topclose: true,
	  autosize: false,
	  width: 600,
	  height: 300,
      ajax: {
        parameters: params // the parameters from the form
      }
    }
  });
}

Sortable.create('triplist', {
	onUpdate:function(){
		new Ajax.Updater('list-info', 'trip/reorder/', {
			onComplete:function(request){
				new Effect.Highlight('triplist',{});
				params = Sortable.serialize('triplist').toQueryParams();
				points = Object.values(params).toString().split(',');
				setPolyline();
				
			}, 
			parameters:Sortable.serialize('triplist'), 
			evalScripts:true, 
			asynchronous:true,
			method: 'get'
		})
	}
})</script>

<?php
} // end if is own trip

} // end if tripdata
else {
    echo $words->get('Trip_SubtripsNone');
}
?>

<?php
	if ($isOwnTrip) {
?>
    <div style="padding: 20px 0">
    <h3>
    <a href="blog/create" onclick="$('blog-create-form').toggle(); return false"><img src="images/icons/note_add.png"></a> <a href="blog/create" onclick="$('blog-create-form').toggle(); return false"><?=$words->get('Trip_SubtripsCreate')?></a><br />
    </h3>
    <p class="small"><?=$words->get('Trip_SubtripsCreateDesc')?></p>
    </div>
    <?php require 'subtrip_createform.php' ?>
<?php
    }
?>
<div id="geo-entry">
    <form id="geoajaxform">
        <input type="text" id="geo-search" name="search"  />
        <input type="submit" id="geosubmit" value="<?=$words->get('label_search_location')?>" class="button" />
    </form>
</div>
<link rel="stylesheet" href="styles/lightview.css" type="text/css"/>   