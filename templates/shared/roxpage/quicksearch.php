&nbsp;<form action="quicksearch" method="get" id="form-quicksearch">
    <input type="hidden" name="quicksearch_callbackId" value="1"/>
    <input type="text" name="vars" size="15" maxlength="30" id="text-field" value="<?echo htmlentities($words->getSilent('TopMenuSearchtext'),ENT_QUOTES, "UTF-8");?>..." onclick="this.value='';" />
    <input type="submit" class="display: none" name="submit-button" id="submit-button" /><label for="submit-button"><i class="icon icon-search" style="color: white"></i></label>
    <?=$words->flushBuffer()?>
</form>
