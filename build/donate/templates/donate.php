<?php
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


<div class="row">
	<div class="col-xs-12">
		<div class="card">
			<p class="h5 card-text text-xs-center m-b-0">Goal for 2016-2017: <strong>€1000,-</strong></p>
			<progress class="progress progress-primary m-a-0" value="60" max="100"></progress>
			<p class="h5 card-text text-xs-center"><strong>€600.00</strong> received</p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-lg-6">
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" data-toggle="tab" href="#bank" role="tab">Bank</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#bitcoin" role="tab">Bitcoin</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#paypal" role="tab">PayPal</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" data-toggle="tab" href="#time" role="tab">Time</a>
			</li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div class="tab-pane card active" id="bank" role="tabpanel">
				<h3><?=$words->get('Donate_Account_Legend')?></h3>
				<p><?=$words->get('Donate_Account2')?></p>
				<p><?=$words->get('Donate_Account')?></p>
			</div>
			<div class="tab-pane card" id="bitcoin" role="tabpanel">
				<h3><?php echo $words->get('Donate_Bitcoins');?> <img src="images/misc/bitcoin.gif" alt="bitcoin" /></h3>
				<p><?php echo $words->get('Donate_Bitcoins_Text');?></p>
			</div>
			<div class="tab-pane card" id="paypal" role="tabpanel">
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
			</div>
			<div class="tab-pane card" id="time" role="tabpanel">
				Donate time
			</div>
		</div>

		<div class="m-t-2">
			<h3><?php echo $words->get('Donate_FurtherInfo'); ?></h3>
			<p><?php echo $words->get('Donate_FurtherInfoText');?></p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-lg-6">
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

<script type="text/javascript">
/* update the amount-field in the donation form when an option is selected/ an amount is entered */
function changeAmount (Amount) {
document.getElementById('amount').value = Amount+'.00';
}
function clearForm (Element) {
document.getElementById(Element).value = '';
}
</script>