
<form action="quicksearch" method="get" id="form-quicksearch">
    <input type="text" name="vars" size="15" maxlength="30" id="text-field" value="<?echo htmlentities($words->getSilent('TopMenuSearchtext'),ENT_QUOTES, "UTF-8");?>..." onclick="this.value='';" />
    <input type="hidden" name="quicksearch_callbackId" value="1"/>
    <input type="image" src="images/icons/icon_searchtop.gif" id="submit-button" />
    <?=$words->flushBuffer()?>
</form>
