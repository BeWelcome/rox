<form class="navbar-form navbar-right" role="search" action="quicksearch" method="get" id="form-quicksearch">
        <div class="form-group">
            <div class="input-group">
                <input class="form-control input-sm" type="text" name="vars" size="15" maxlength="30" placeholder="<?echo htmlentities($words->getSilent('TopMenuSearchtext'),ENT_QUOTES, "UTF-8");?>..." id="text-field" value="<?echo htmlentities($words->getSilent('TopMenuSearchtext'),ENT_QUOTES, "UTF-8");?>..." onclick="this.value='';" />
                <input type="hidden" name="quicksearch_callbackId" value="1"/>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-default btn-sm" id="submit-button"><i class="fa fa-search"></i></button>
                </span>
            </div><!-- /input-group -->
        </div>
</form>
<?=$words->flushBuffer()?>