<div class="tab-pane fade card" id="contactinfo" role="tabpanel" aria-labelledby="contactinfo-tab">
    <div class="card-header" role="tab" id="heading-contactinfo">
        <h5 class="mb-0">
            <a data-toggle="collapse" href="#collapse-contactinfo" data-parent="#content" aria-expanded="true" aria-controls="collapse-contactinfo">
                <?= $words->get('ContactInfo') ?>
            </a>
        </h5>
    </div>
    <div id="collapse-contactinfo" class="collapse" role="tabpanel" aria-labelledby="heading-contactinfo">
        <div class="card-body">
            <div class="row">
                <? if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('Street') ?>
                    </div>
                    <div class="col-12 col-md-9 form-inline">
                        <input class="<?php if (isset($errorStreet)) { ?>error-input-text<?php } ?>" type="text"
                               name="Street"
                               id="Street"
                               value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_Address"
                            <?php if ($vars['IsHidden_Address'])
                                echo 'checked="checked"';
                            ?>><?= $words->get('hidden') ?>
                        <?php if (isset($errorStreet)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidStreet') ?></div>
                        <?php } ?>
                    </div>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('HouseNumber') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input class="short<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="HouseNumber" id="HouseNumber"
                               value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                               size="6"/>
                        <?php if (isset($errorHouseNumber)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                        <?php } ?>
                    </div>


                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('Post code') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input class="short <?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
                               name="Zip"
                               value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="6"/>
                        <input type="checkbox" value="Yes" name="IsHidden_Zip"
                            <?php if ($vars['IsHidden_Zip'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                        <?php if (isset($errorZip)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidZip') ?></div>
                        <?php } ?>
                    </div>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('ProfileHomePhoneNumber') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input type="text" size="15" name="HomePhoneNumber"
                               value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_HomePhoneNumber"
                            <?php if ($vars['IsHidden_HomePhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('ProfileCellPhoneNumber') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input type="text" size="15" name="CellPhoneNumber"
                               value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_CellPhoneNumber"
                            <?php if ($vars['IsHidden_CellPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('ProfileWorkPhoneNumber') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input type="text" size="15" name="WorkPhoneNumber"
                               value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_WorkPhoneNumber"
                            <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <?= $words->get('hidden') ?>
                    </div>

                    <div class="w-100 mt-3">
                    </div>

                    <div class="col-12 col-md-3 h5 mb-0">
                        <?= $words->get('Website') ?>
                    </div>
                    <div class="col-12 col-md-9">
                        <input type="text" class="w-100" name="WebSite"
                               value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    </div>

                    <div class="w-100 m-3 h5 mb-0">
                        Social media
                    </div>

                    <?php
                    if (isset($vars['messengers'])) {
                        foreach ($vars['messengers'] as $me) {
                            $val = 'chat_' . $me['network_raw'];
                            ?>

                            <div class="col-12 col-md-3 h5 mb-0">
                                <i class="<? if ($me["image"] == 'user-plus'){ echo 'fa'; } else { echo 'fab'; } ?> fa-<?= $me["image"]; ?> pr-2" alt="<?= $me["image"]; ?>"
                                   title="<?= $me["image"]; ?>"></i>
                                <?= $me["network"] ?>
                            </div>
                            <div class="col-12 col-md-9 mb-2">
                                <input type="text" name="<?= $val ?>"
                                       value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>"/>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
                <div class="col-12 mt-3">
                    <input type="submit" class="btn btn-primary float-right m-2" id="submit" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>