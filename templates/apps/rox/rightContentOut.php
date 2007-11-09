<?php
$words = new MOD_words();
?>

<div class="index info">
<p><?php $words->get('IndexPageWord18', '<a
> href="/bw/lostpassword.php">', '</a>'); ?></a>
</p>
<script type="text/javascript">document.getElementById("login-u").focus();</script>
<h3><?php $words->get('SignupNow'); ?></h3>
<p><?php $words->getFormatted('IndexPageWord17', '<a
> href="/bw/signup.php">', '</a>'); ?></p>
</div>
