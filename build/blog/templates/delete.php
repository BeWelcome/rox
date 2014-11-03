<?php
$words = new MOD_words();
$callback = $this->getCallbackOutput('BlogController','deleteProcess');
echo <<<HTML
<h2>{$words->get('BlogDeleteTitle')}</h2>
<form method="post" action="blog/{$post->user_handle}">
    <p>
        <input type="hidden" name="id" value="{$post->blog_id}"/>
        {$callback}
        <input type="submit" class="button" name="n" value="{$words->get('No')}"/>
        <input type="submit" class="button" name="y" value="{$words->get('Yes')}"/>
    </p>
</form>
HTML;
