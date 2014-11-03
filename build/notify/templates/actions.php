<div class="clearfix small">
    <img src="images/icons/add.png" class="float_left">
    <div class="float_left">
    <label>IdMember</label><br />
    <input type="text" name="IdMember" type="text" size="4" maxlength="10" value="" /> 
    </div>
    <div class="float_left">
    <label>IdRelMember</label><br />
    <input type="text" name="IdRelMember" type="text" size="4" maxlength="10" value="" /> 
    </div>
    <div class="float_left">
    <label>Type</label><br />
    <input type="text" name="Type" type="text" size="10" value="" /> 
    </div>
    <div class="float_left">
    <label>Link</label><br />
    <input type="text" name="Link" type="text" size="10" value="" />
    </div>
    <div class="float_left">
    <label>WordCode</label><br />
    <input type="text" name="WordCode" type="text" size="10" value="" /> 
    </div>
    <div class="float_left">
    <label>Checked</label><br />
    <input type="text" name="Checked" type="text" size="1" maxlength="1" value="" /> 
    </div>
    <div class="float_left">
    <label>SendMail</label><br />
    <input type="text" name="SendMail" type="text" size="1" maxlength="1" value="" />
    </div>
    <div class="float_left">
    <label>Created</label><br />
    <input type="text" name="Created" type="text" size="8" value="" />
    </div>
</div>
    <input type="hidden" name="action" value="create"/>
    <?=$callback_tag?>
    <input type="submit" class="button" name="button" value="Create" id="button" />
