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

                <?php if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                <div class="form-row">
                    <div class="form-group col-12 col-md-5">
                        <label for="Street" class="col-form-label">
                            <?= $words->get('Street') ?>
                        </label>

                            <input class="o-input<?php if (isset($errorStreet)) { ?> error-input-text<?php } ?>" type="text"
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


                    <div class="form-group col-12 col-md-2">
                        <label for="HouseNumber" class="col-form-label">
                            <?= $words->get('HouseNumber') ?>
                        </label>

                            <input class="o-input<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                                   type="text"
                                   name="HouseNumber" id="HouseNumber"
                                   value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                                   size="6"/>
                            <?php if (isset($errorHouseNumber)) { ?>
                                <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                            <?php } ?>

                    </div>

                    <div class="form-group col-12 col-md-5">
                        <label for="Zip" class="col-form-label">
                            <?= $words->get('Post code') ?>
                        </label>

                            <input class="o-input<?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
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
                </div>


            <div class="form-row">
                <div class="form-group col-12 col-md-4">
                    <label for="HomePhoneNumber" class="col-form-label">
                        <?= $words->get('ProfileHomePhoneNumber') ?>
                    </label>
                    <input type="text" name="HomePhoneNumber" class="o-input"
                           value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <input type="checkbox" value="Yes" name="IsHidden_HomePhoneNumber"
                        <?php if ($vars['IsHidden_HomePhoneNumber'])
                            echo 'checked="checked"';
                        ?> />
                    <label for="IsHidden_HomePhoneNumber" class="m-0 ml-1"><?= $words->get('hidden') ?></label>
                </div>

                <div class="form-group col-12 col-md-4">
                    <label for="CellPhoneNumber" class="col-form-label">
                        <?= $words->get('ProfileCellPhoneNumber') ?>
                    </label>
                    <input type="text" name="CellPhoneNumber" class="o-input"
                           value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                    <input type="checkbox" value="Yes" name="IsHidden_CellPhoneNumber"
                        <?php if ($vars['IsHidden_CellPhoneNumber'])
                            echo 'checked="checked"';
                        ?> />
                    <label for="IsHidden_CellPhoneNumber" class="m-0 ml-1"><?= $words->get('hidden') ?></label>
                </div>

                <div class="form-group col-12 col-md-4">
                    <label for="WorkPhoneNumber" class="col-form-label">
                        <?= $words->get('ProfileWorkPhoneNumber') ?>
                    </label>
                        <input type="text" name="WorkPhoneNumber" class="o-input"
                               value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <input type="checkbox" value="Yes" name="IsHidden_WorkPhoneNumber"
                            <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                    <label for="IsHidden_WorkPhoneNumber" class="m-0 ml-1"><?= $words->get('hidden') ?></label>
                </div>
            </div>

            <div class="form-row">
                <label for="WebSite" class="col-form-label">
                        <?= $words->get('Website') ?>
                </label>
                        <input type="text" class="o-input" name="WebSite"
                               value="<?php echo htmlentities($vars['WebSite'], ENT_COMPAT, 'UTF-8'); ?>"/>
            </div>

                    <div class="row mt-3">
                        <div class="col-12">
                        <?= $words->get('profile.contactinfo.socialmedia') ?>
                        </div>
                    </div>

                    <?php
                    if (isset($vars['messengers'])) {
                        foreach ($vars['messengers'] as $me) {
                            $val = 'chat_' . $me['network_raw'];
                            ?>
            <div class="form-row">
                            <div class="col-12 col-md-3 mb-0">
                                <i class="<?php if ($me["image"] == 'user-plus'){ echo 'fa'; } else { echo 'fab'; } ?> fa-<?= $me["image"]; ?> pr-2" alt="<?= $me["image"]; ?>"
                                   title="<?= $me["image"]; ?>"></i>
                                <label for="<?= $val ?>" class="col-form-label"><?= $me["network"] ?></label>
                            </div>
                            <div class="col-12 col-md-9 mb-2">
                                <input type="text" name="<?= $val ?>" class="o-input"
                                       value="<?php echo htmlentities($me["address"], ENT_COMPAT, 'UTF-8'); ?>"/>
                            </div>
            </div>
                            <?php
                        }
                    }
                }
                ?>
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-primary float-right m-2" name="submit"
                           value="<?= $words->getSilent('Save Profile') ?>"/> <?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
