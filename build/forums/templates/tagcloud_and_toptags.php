<?php
/*
	This file display the list of categories, the list of contienent and the tagcloud
*/
?>
	<h3>
	<a href="javascript:void();" id="HideUnhide_TagCloud">+/-</a>  
	<script language="Javascript" type="text/javascript">
	<!--
		$('HideUnhide_TagCloud').observe('click', function(){
			show_hide_tag_list('tagcloud') ;
			show_hide_tag_list('category') ;
			show_hide_tag_list('continent') ;
		});
	//!-->
	</script>
	<?php echo $this->words->getFormatted('ForumTagCollectionTitle'); ?></h3>
    <div class="subcolumns">

	
<!-- Now displays the by category -->
	<div class="c33l" id="category">
        <div class="subcl category">
          <h4 class="clearfix"><?php echo '<img src="styles/css/minimal/images/iconsfam/folder_page.png" alt="'. $this->words->getBuffered('tags') .'" title="'. $this->words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $this->words->flushBuffer(); ?><?php echo $this->words->getFormatted('ForumByCategory'); ?></h4>
          <ul>
          <?php
            foreach ($top_tags as $tagid => $tag) {
			   $TagCategory=$this->words->fTrad($tag->IdName) ;
			   $TagDescription=$this->words->fTrad($tag->IdDescription) ;
//              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($TagCategory).'" title="'.$TagDescription.'">'.$TagCategory.'</a></li>' ;
				echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($TagCategory).'">'.$TagCategory.'</a><br />' ;
				echo ' <span class="forums_tag_description">'.$TagDescription.'</span></li>'; 
            }
            ?>
          </ul>
        </div> <!-- subcl -->
      </div> <!-- c33l -->


<!-- Now displays the by continent -->
      <div class="c33l" id="continent">
        <div class="subc region">
          <h4 class="clearfix"><?php echo '<img src="styles/css/minimal/images/iconsfam/world.png" alt="'. $this->words->getBuffered('geo') .'" title="'. $this->words->getBuffered('geo') .'" class="forum_icon" />';?>&nbsp;<?php echo $this->words->flushBuffer(); ?><?php echo $this->words->getFormatted('ForumByContinent'); ?></h4>
          <ul class=" clearfix">
            <li><a href="forums/kAF-Africa"><?php echo $this->words->getBuffered('Africa'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kAN-Antarctica"><?php echo $this->words->getBuffered('Antarctica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kAS-Asia"><?php echo $this->words->getBuffered('Asia'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kEU-Europe"><?php echo $this->words->getBuffered('Europe'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kNA-North America"><?php echo $this->words->getBuffered('NorthAmerica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kSA-South Amercia"><?php echo $this->words->getBuffered('SouthAmerica'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
            <li><a href="forums/kOC-Oceania"><?php echo $this->words->getBuffered('Oceania'); ?></a><?php echo $this->words->flushBuffer(); ?></li>
          </ul>
        </div> <!-- subc -->
      </div> <!-- c33l -->

    </div> <!-- subcolumns -->
	<script language="Javascript" type="text/javascript">
	<!--
	function show_hide_tag_list(tblid, show) {
		if (tbl = document.getElementById(tblid)) {
			if (null == show) show = tbl.style.display == 'none';
			tbl.style.display = (show ? '' : 'none');
		}
	}
	//!-->
	</script>
