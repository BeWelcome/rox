<link href="build/scrollingtabs.19b6cca0.css" rel="stylesheet">
<div class="row"><?php
$words = new MOD_words();

$lang = 'fr' === $this->session->get('lang') ? 'fr' : 'en';
$helloAssoLink = 'https://www.helloasso.com/associations/bevolunteer/formulaires/1/' . $lang;
?>

<?php if ($sub == 'done') {
    echo '<div class="col-12">';
if ($error) {?>
		<p class="alert alert-danger"><?php echo $words->getFormatted('Donate_NotDoneText')?>: <?=$error?></p>
<?php } else { ?>
		<p class="alert alert-success"><?php echo $words->getFormatted('Donate_DoneText','<a href="feedback">','</a>')?></p>
<?php }
    echo '</div>';
} elseif ($sub == 'cancel') { ?>
    <div class="col-12">
		<p class="alert alert-notice"><?php echo $words->getFormatted('Donate_CancelText'); ?></p>
    </div>
<?php } ?>

	<div class="col-12 col-lg-6">
        <div class="scroller scroller-left u-float-left mt-2"><i class="fa fa-caret-left"></i></div>
        <div class="scroller scroller-right u-float-right mt-2"><i class="fa fa-caret-right"></i></div>
        <div class="wrapper-nav">
            <ul class="nav nav-tabs list" role="tablist">
                <li class="nav-item">
                    <a class="nav-item nav-link active" data-toggle="tab" href="#bank" role="tab"><?= $words->get('donate.bank') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link" data-toggle="tab" href="#hello_asso" role="tab">HelloAsso</a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link" data-toggle="tab" href="#paypal" role="tab"><?= $words->get('donate.money') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-item nav-link" data-toggle="tab" href="#time" role="tab"><?= $words->get('donate.time') ?></a>
                </li>
            </ul>
        </div>

		<!-- Tab panes -->
		<div class="tab-content">
			<div class="tab-pane card active p-2" id="bank" role="tabpanel">
				<h3><?=$words->get('Donate_Account_Legend')?></h3>
				<p><?=$words->get('Donate_Account2')?></p>
				<p class="alert-primary p-2"><?=$words->get('Donate_Account')?></p>
			</div>
            <div class="tab-pane card p-2" id="hello_asso" role="tabpanel">
                <h3><?php echo $words->get('donate.hello.asso');?><i class="fa fa3x fa-coins ml-2"></i></h3>
                <a href="<?= $helloAssoLink ?>" target="_blank" class="btn btn-primary btn-block"><i class="fa fa-coins mr-1"></i><?php echo $words->get('donate.hello.asso');?></a>
                <p><?php echo $words->get('donate.hello.asso.text');?></p>
            </div>
			<div class="tab-pane card p-2" id="paypal" role="tabpanel">
				<form action="<?= $_ENV['PaypalUrl'] ?>" method="post">
					<h3><?=$words->get('Donate_Paypal_Legend')?></h3>
					<p><img src="images/misc/paymethods.gif" alt="methods of payment" /></p>
					<p>
						<input type="hidden" name="cmd" value="_xclick" />
						<input type="hidden" name="business" value="<?= $_ENV['PaypalBusinessAddress'] ?>" />
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
						if ($this->session->has( "lang" ) ) {
							switch ($this->session->get("lang")){
								case 'fr' :
									echo "FR" ;
									break ;
								case 'de' :
									echo "DE" ;
									break ;
								case 'it' :
									echo "IT" ;
									break ;
								case 'es' :
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
                        <input type="hidden" name="notify_return" value="<?=PVars::getObj('env')->baseuri?>donate/notify" />
                        <input type="hidden" name="cancel_return" value="<?=PVars::getObj('env')->baseuri?>donate/cancel" />
						<input type="hidden" name="cn" value="comment" />
						<input type="hidden" name="currency_code" value="EUR" />
						<input type="hidden" name="tax" value="0" />
						<input type="hidden" name="bn" value="PP-DonationsBF" />
                        <input type="hidden" name="rm" value="0" />
						<input type="submit" class="btn btn-primary btn-block" name="submit" alt="<?php echo $words->getBuffered('Donate_DonateNow'); ?>" onmouseover="return('<?php echo $words->getBuffered('Donate_DonateNow'); ?>')" value="<?php echo $words->getBuffered('Donate_DonateNow'); ?>" />
						<img alt="Donate now" src="<?= $_ENV['PaypalScrPixel'] ?>" width="1" height="1" />
					</p>
				</form>
				<p><?=$words->get('Donate_Process')?></p>
			</div>
			<div class="tab-pane card p-2" id="time" role="tabpanel">
                <?=$words->get('donation.donatetime')?>
			</div>
		</div>

		<div class="mt-2">
			<h3><?php echo $words->get('Donate_FurtherInfo'); ?></h3>
			<p><?php echo $words->get('Donate_FurtherInfoText');?></p>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<a id="why"></a>
		<h3><?php echo $words->get('Donate_Why');?></h3>
		<p><?php echo $words->getFormatted('Donate_WhyText','<a href="feedback">','</a>')?></p>

		<a id="transparency"></a>
		<h3><?php echo $words->get('Donate_Transparency'); ?></h3>
		<p><?php echo $words->getFormatted('Donate_TransparencyText'); ?></p>

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

<script type="text/javascript" src="build/scrollingtabs.b1531774.js"></script>
