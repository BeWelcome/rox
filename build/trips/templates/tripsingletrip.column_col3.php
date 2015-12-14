<?php
/*
 * template content: 
 * shows the trip author with a picture, trip title, trip text ($this->trip->trip_descr),
 * number of destinations of the trip and action links (create, edit, delete, add destination)
 */
$words = $this->getWords();
$layoutbits = new MOD_layoutbits();
?>
<div>
<div><div class="pull-left"><?=$layoutbits->PIC_50_50($this->trip->handle)?><?php echo $words->flushBuffer(); ?></div><h2 class="tripname"><?=$this->trip->trip_name; ?></h2></div>
        <?=$words->get('by')?> <a href="members/<?php echo $this->trip->handle; ?>"><?php echo $this->trip->handle; ?></a>
            <a href="blog/<?php echo $this->trip->handle; ?>" title="Read blog by <?php echo $this->trip->handle; ?>"><img src="images/icons/blog.gif" style="vertical-align:bottom;" alt="" /></a>
            <a href="trip/show/<?php echo $this->trip->handle; ?>" title="Show trips by <?php echo $this->trip->handle; ?>"><img src="images/icons/world.gif" style="vertical-align:bottom;" alt="" /></a>
<?php
if ($this->member)
{
?><div class="btn-group pull-right" role="group">
    <button type="button" class="btn btn-default"><a href="trip/create" title="<?=$words->getSilent('TripTitle_create')?>"><img src="images/icons/world_add.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('TripTitle_create')?>" /></a> <a href="trip/create" title="<?=$words->getSilent('TripTitle_create')?>"><?=$words->getSilent('TripTitle_create')?></a><?php echo $words->flushBuffer(); ?></button>
    <?php if ($this->member && !$this->isOwnTrip) { ?>
    <button type="button" class="btn btn-default"><a href="trip/show/<?=$this->member->Username?>" title="<?=$words->getSilent('TripsShowMy')?>"><img src="images/icons/world.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('TripsShowMy')?>" /></a> <a href="trip/show/<?=$this->member->Username?>" title="<?=$words->getSilent('TripsShowMy')?>"><?=$words->getSilent('TripsShowMy')?></a><?php echo $words->flushBuffer(); ?></button>
    <?php    }?>
    <?php if ($this->isOwnTrip) { ?>
    <button type="button" class="btn btn-default"><a href="trip/edit/<?=$this->trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('Trip_EditMyTrip')?>" /></a> <a href="trip/edit/<?=$this->trip->trip_id; ?>" title="<?=$words->getSilent('Trip_EditMyTrip')?>"><?=$words->getSilent('Trip_EditMyTrip')?></a><?php echo $words->flushBuffer(); ?></button>
    <button type="button" class="btn btn-default"><a href="trip/delete/<?=$this->trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/delete.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('Trip_DeleteMyTrip')?>" /></a> <a href="trip/delete/<?=$this->trip->trip_id; ?>" title="<?=$words->getSilent('Trip_DeleteMyTrip')?>"><?=$words->getSilent('Trip_DeleteMyTrip')?></a><?php echo $words->flushBuffer(); ?></button>
    <button type="button" class="btn btn-default"><a href="trip/<?=$this->trip->trip_id; ?>/#destination-form" title="<?=$words->getSilent('Trip_SubtripsCreate')?>"><img src="images/icons/note_add.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('Trip_SubtripsCreate')?>" /></a> <a href="trip/<?=$this->trip->trip_id; ?>/#destination-form" title="<?=$words->getSilent('Trip_SubtripsCreate')?>"><?=$words->getSilent('Trip_SubtripsCreate')?></a><?php echo $words->flushBuffer(); ?></button>
    <?php    }?>
    </div><?php
} ?>
</div>

<?php
$CntSubtrips = 0;
if ($this->trip_data) 
    $CntSubtrips = count($this->trip_data[$this->trip->trip_id]);

if (isset($this->trip->trip_descr) && $this->trip->trip_descr) {
echo '<p class="tripdesc">'.$this->trip->trip_descr.'</p>';
}
if (isset($this->trip->trip_text) && $this->trip->trip_text) {
	echo '<p>'.$this->trip->trip_text.'</p>';
}
?>
<div class="clearfix">

    <?php
    if (isset($this->trip_data[$this->trip->trip_id])) {
        echo '<h3>'. $CntSubtrips.' ' .$words->get('Trip_SubtripsTitle').'</h3>';
        echo '<ul id="triplist">';
        foreach ($this->trip_data[$this->trip->trip_id] as $blogid => $blog) {

            echo '<li id="tripitem_'.$blogid.'">';
            ?>

            <!-- Subtemplate: 2 columns 50/50 size -->
            <div class="bw_row">
                <div class="col-md-1">
                        <?php
                        if ($blog->blog_start) {
                            ?>
                            <div class="calendar calendar-icon-<?php echo date("m", strtotime($blog->blog_start)) ?>">
                                <div class="calendar-day"><?php echo date("j", strtotime($blog->blog_start)) ?></div>
                                <div class="calendar-year"><?php echo date("Y", strtotime($blog->blog_start)) ?></div>
                            </div>
                        <?php
                        }
                        ?>
                        <!-- End of contents for left subtemplate -->
                </div>

                <div class="col-md-11">
                        <!-- Contents for right subtemplate -->
                        <h3 class="borderless">
                            <a href="blog/<?=$this->trip->handle?>/<?=$blogid?>"><?=$blog->blog_title?></a> </h3>
                        <?php	if ($blog->name)
                        {
                            if ($bloggeo = $this->model->getBlogGeo($blog->blog_geonameid))
                            {
                                $country = $bloggeo->getCountry();
                                $countryname = $country->name;
                            }
                            else
                            {
                                $countryname = '';
                            }
                            echo "<span class='trip_author'>{$blog->name}, {$countryname}</span><br />";
                        }
                        ?>
                        <?php
                        if ($blog->blog_text) {
                            $blogtext = $blog->blog_text;
                            $moreLink = '<br /><a href="blog/' . $this->trip->handle. '/' . $blogid . '">' . $words->get('ReadMore') . ' ...</a>';
                            echo '<div>' . MOD_layoutbits::truncate_words($blogtext, 60, $moreLink) . '</div>';
                        }
                        ?>
                        <div>
                            <?php
                            if ($this->member && $this->isOwnTrip) {?>
                                <a href="blog/edit/<?=$blogid; ?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_EditMyOwnSubTrip')?>" /></a> <a href="blog/edit/<?=$blogid; ?>" title="<?=$words->get('Trip_EditMyOwnSubTrip')?>"><?=$words->get('Trip_EditMyOwnSubTrip')?></a>
                            <?php   }?>
                        </div>
                        <!-- End of contents for right subtemplate -->
                </div><!-- End of c75r -->
            </div>
            <?php
            echo '</li>';

        }
        echo '</ul>';

    } // end if tripdata
    ?>
</div>

<?php require 'subtrip_createform.php';

$shoutsCtrl = new ShoutsController;
$shoutsCtrl->shoutsList('trip', $this->trip->trip_id);
?>