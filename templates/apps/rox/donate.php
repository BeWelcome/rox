<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
?>

<?php   if ($sub == 'done') { 
            if ($error) {?> 

    	<h3><?php echo $words->get('Donate_NotDone');?></h3>
    	<p class="warning"><?php echo $words->getFormatted('Donate_NotDoneText')?>: <?=$error?></p>
<?php   } else { ?>
    	<h3><?php echo $words->get('Donate_Done');?></h3>
    	<p class="note"><?php echo $words->getFormatted('Donate_DoneText','<a href="/bw/feedback.php">','</a>')?></p>
<?php } 
} elseif ($sub == 'cancel') { ?>
        <h3><?php echo $words->get('Donate_Cancel'); ?></h3>
        <p class="warning"><?php echo $words->getFormatted('Donate_CancelText'); ?></p>
<?php   } ?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
        <a name="why"></a>
    	<h3><?php echo $words->get('Donate_Why');?></h3>
    	<p><?php echo $words->getFormatted('Donate_WhyText','<a href="/bw/feedback.php">','</a>')?></p>

        <a name="tax"></a>
        <h3><?php echo $words->get('Donate_Tax'); ?></h3>
        <p><?php echo $words->get('Donate_TaxText'); ?></p>
        
        <a name="transparency"></a>
        <h3><?php echo $words->get('Donate_Transparency'); ?></h3>
        <p><?php echo $words->getFormatted('Donate_TransparencyText','<a href="http://www.bevolunteer.org/joomla/index.php/Donate!?Itemid=54&option=com_civicrm">','</a>'); ?></p>
        
    </div>
   </div>

  <div class="c50r">
    <div class="subcr">
        <a name="how"></a>
        <h3><?php echo $words->get('Donate_How'); ?></h3>
        <p><?php echo $words->get('Donate_HowText'); ?></p>
        <div class="row">                    
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <fieldset id="donate-paypal" style="border: 1px solid #999; display:block; padding: 10px 20px; margin-top: 20px; background-color: #f5f5f5;">
                    <legend><?=$words->get('Donate_Paypal_Legend')?></legend>
                    <img src="images/misc/paymethods.gif">
                    <p><?=$words->get('Donate_Process')?></p>
                    
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="business" value="treasurer@bevolunteer.org">
                    <select  name="amount">
                    <option value="10.00">10 &#8364;</option>
                    <option value="25.00" selected>25 &#8364;</option>
                    <option value="50.00">50 &#8364;</option>
                    <option value="100.00">100 &#8364;</option>
                    <option value="200.00">200 &#8364;</option>
                    </select>
                    <input type="hidden" name="item_name" value="BeVolunteer donation">
                    <input type="hidden" name="page_style" value="Primary">
                    <input type="hidden" name="no_shipping" value="1">
                    <input type="hidden" name="lc" value="<?php 
                     if (isset($_SESSION["lang"]) ) {
                     		switch ($_SESSION["lang"]){
                    					 case 'fr' :
                    					 			echo "FR" ;
                    								break ;
                    					 case 'de' :
                    					 			echo "DE" ;
                    								break ;
                    					 case 'it' :
                    					 			echo "IT" ;
                    								break ;
                    					 case 'esp' :
                    					 			echo "ES" ;
                    								break ;
                    					 default :
                    					 			echo "US" ;
                    								break ;
                    		} 
                     }
                     else {
                       echo "US" ;
                     }
                     ?>">
                      
                    <input type="hidden" name="return" value="<?=PVars::getObj('env')->baseuri?>donate/done">
                    <input type="hidden" name="cancel_return" value="<?=PVars::getObj('env')->baseuri?>donate/cancel">
                    <input type="hidden" name="cn" value="comment">
                    <input type="hidden" name="currency_code" value="EUR">
                    <input type="hidden" name="tax" value="0">
                    <input type="hidden" name="bn" value="PP-DonationsBF">
                    <input type="submit" class="button" border="0" name="submit" alt="<?php echo $words->getBuffered('PayPalDonate_tooltip'); ?>" onmouseover="return('<?php echo $words->getBuffered('PayPalDonate_tooltip'); ?>')" value="Donate Now!">
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                    </form>
                    <?php $words->flushBuffer() ?>
                </div>
        <div class="row"> 
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="def-form" id="donate-options-form">               
                <fieldset id="donate-account" style="border: 1px solid #999; display:block; padding: 20px; margin-top: 20px; background-color: #f5f5f5;"><legend><?=$words->get('Donate_Account_Legend')?></legend>
                    <p><?=$words->get('Donate_Account')?></p>
                    <p><?=$words->get('Donate_Account2')?></p>
                </fieldset>
            </form>
        </div>
        
    </div>
  </div>
</div>

<h3><?php echo $words->get('Donate_FurtherInfo'); ?></h3>
<p><?php echo $words->get('Donate_FurtherInfoText','<a href="http://bevolunteer.org/wiki"','</a>');?></p>
	 
<?php if ($TDonationArray) {
 require TEMPLATE_DIR.'apps/rox/donate_list.php';
 } ?>