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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330,
Boston, MA 02111-1307, USA.

*/
$words = new MOD_words();
?>

<?php if ($sub == 'done') {
if ($error) {?>

		<p class="warning"><?php echo $words->getFormatted('Donate_NotDoneText')?>: <?=$error?></p>
<?php } else { ?>
		<p class="note"><?php echo $words->getFormatted('Donate_DoneText','<a href="feedback">','</a>')?></p>
<?php }
} elseif ($sub == 'cancel') { ?>
		<p class="warning"><?php echo $words->getFormatted('Donate_CancelText'); ?></p>
<?php } ?>

<div class="subcolumns">
	<div class="c50l">
		<div class="subcl">
			<h3><?=$words->get('Donate_Account_Legend')?></h3>
			<p><?=$words->get('Donate_Account2')?></p>			
			<p><?=$words->get('Donate_Account')?></p>

			<h3><?php echo $words->get('Donate_Bitcoins');?> <img src="images/misc/bitcoin.gif" alt="bitcoin" /></h3>
			<p><?php echo $words->get('Donate_Bitcoins_Text');?></p>
		
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<h3><?=$words->get('Donate_Paypal_Legend')?></h3>
			<p><img src="images/misc/paymethods.gif" alt="methods of payment" /></p>
			<p>
			<input type="hidden" name="cmd" value="_xclick" />
			<input type="hidden" name="business" value="treasurer@bevolunteer.org" />
			<select name="amountSelect" id="amountSelect" onchange="changeAmount(this.value); clearForm('amountSelectText');">
			<option value=""></option>
			<option value="10">10 &#8364;</option>
			<option value="25" selected="selected">25 &#8364;</option>
			<option value="50">50 &#8364;</option>
			<option value="100">100 &#8364;</option>
			<option value="200">200 &#8364;</option>
			</select>
			<label for="amountSelectText"><?=$words->get('Donate_Paypal_OrChooseYourself')?></label>
			</p>
			<p>
			<input type="text" size="4" name="amountSelectText" id="amountSelectText" onchange="changeAmount(this.value);" onclick="clearForm('amountSelect');" /> &#8364;
			</p>
			<p>
			<input type="hidden" id="amount" name="amount" value="25.00" />
			<input type="hidden" name="item_name" value="BeVolunteer donation" />
			<input type="hidden" name="page_style" value="Primary" />
			<input type="hidden" name="no_shipping" value="1" />
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
			?>" />
			<input type="hidden" name="return" value="<?=PVars::getObj('env')->baseuri?>donate/done" />
			<input type="hidden" name="cancel_return" value="<?=PVars::getObj('env')->baseuri?>donate/cancel" />
			<input type="hidden" name="cn" value="comment" />
			<input type="hidden" name="currency_code" value="EUR" />
			<input type="hidden" name="tax" value="0" />
			<input type="hidden" name="bn" value="PP-DonationsBF" />
			<input type="submit" class="button" name="submit" alt="<?php echo $words->getBuffered('Donate_DonateNow'); ?>" onmouseover="return('<?php echo $words->getBuffered('Donate_DonateNow'); ?>')" value="<?php echo $words->getBuffered('Donate_DonateNow'); ?>" />
			<img alt="Donate now" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
			</p>
			</form>
			<p><?=$words->get('Donate_Process')?></p>	
			
			<h3><?php echo $words->get('Donate_FurtherInfo'); ?></h3>
			<p><?php echo $words->get('Donate_FurtherInfoText');?></p>		
		</div>
		
	</div>
	<div class="c50r">
		<div class="subcr">
			<a name="why"></a>
			<h3><?php echo $words->get('Donate_Why');?></h3>
			<p><?php echo $words->getFormatted('Donate_WhyText','<a href="feedback">','</a>')?></p>
		
			<a name="transparency"></a>
			<h3><?php echo $words->get('Donate_Transparency'); ?></h3>
			<p><?php echo $words->getFormatted('Donate_TransparencyText'); ?></p>			
			
			<a name="tax"></a>
			<h3><?php echo $words->get('Donate_Tax'); ?></h3>
			<p><?php echo $words->get('Donate_TaxText'); ?></p>
		
		<?php echo $words->flushBuffer() ?>
		</div>

	</div>
</div>
<script type="text/javascript">
/* update the amount-field in the donation form when an option is selected/ an amount is entered */
function changeAmount (Amount) {
document.getElementById('amount').value = Amount+'.00';
}
function clearForm (Element) {
document.getElementById(Element).value = '';
}
</script>