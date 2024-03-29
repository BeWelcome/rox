<div class="card">
    <div class="card-header" id="heading-contactinfo">
        <a data-toggle="collapse" href="#collapse-contactinfo" aria-expanded="false"
           aria-controls="collapse-contactinfo" class="mb-0 d-block collapsed">
            <?= $words->get('ContactInfo') ?>
        </a>
    </div>
    <div id="collapse-contactinfo" class="collapse" data-parent="#editProfile" aria-labelledby="heading-contactinfo">
        <div class="card-body">
            <?php if ($this->adminedit || !$CanTranslate) { // member translator is not allowed to update crypted data ?>
                <div class="form-row">
                    <div class="o-form-group col-12 col-md-5">
                        <label for="Street" class="col-form-label">
                            <?= $words->get('Street') ?>
                        </label>

                        <input class="o-input<?php if (isset($errorStreet)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="Street"
                               id="Street"
                               value="<?php echo htmlentities($vars['Street'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <div class="o-checkbox">
                            <input type="checkbox" class="o-checkbox__input" value="Yes" name="IsHidden_Address" id="IsHidden_Address"
                            <?php if ($vars['IsHidden_Address'])
                                echo 'checked="checked"';
                            ?>>
                            <label class="o-checkbox__label" for="IsHidden_Address"><?= $words->get('hidden') ?></label>
                        </div>

                        <?php if (isset($errorStreet)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidStreet') ?></div>
                        <?php } ?>
                    </div>


                    <div class="o-form-group col-12 col-md-2">
                        <label for="HouseNumber" class="col-form-label">
                            <?= $words->get('HouseNumber') ?>
                        </label>

                        <input class="o-input<?php if (isset($errorHouseNumber)) { ?> error-input-text<?php } ?>"
                               type="text"
                               name="HouseNumber" id="HouseNumber"
                               value="<?php echo htmlentities($vars['HouseNumber'], ENT_COMPAT, 'UTF-8'); ?>"
                               size="6"/>
                        <?php if (isset($errorHouseNumber)) { ?>
                            <div
                                class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidHouseNumber') ?></div>
                        <?php } ?>

                    </div>

                    <div class="o-form-group col-12 col-md-5">
                        <label for="Zip" class="col-form-label">
                            <?= $words->get('Post code') ?>
                        </label>

                        <input class="o-input<?php if (isset($errorZip)) { ?> error-input-text<?php } ?>" type="text"
                               name="Zip" id="Zip"
                               value="<?php echo htmlentities($vars['Zip'], ENT_COMPAT, 'UTF-8'); ?>" size="8"/>
                        <div class="o-checkbox">
                        <input type="checkbox" class="o-checkbox__input" value="Yes" name="IsHidden_Zip" id="IsHidden_Zip"
                            <?php if ($vars['IsHidden_Zip'])
                                echo 'checked="checked"';
                            ?> />
                            <label class="o-checkbox__label" for="IsHidden_Zip"><?= $words->get('hidden') ?></label></div>
                        <?php if (isset($errorZip)) { ?>
                            <div class="w-100 alert alert-danger"><?= $words->get('SignupErrorInvalidZip') ?></div>
                        <?php } ?>
                    </div>
                </div>


                <div class="form-row">
                    <div class="o-form-group col-12 col-md-4">
                        <label for="HomePhoneNumber" class="col-form-label">
                            <?= $words->get('ProfileHomePhoneNumber') ?>
                        </label>
                        <input type="text" id="HomePhoneNumber" name="HomePhoneNumber" class="o-input"
                               value="<?php echo htmlentities($vars['HomePhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>

                        <div class="o-checkbox">
                        <input type="checkbox" class="o-checkbox__input" value="Yes" id="IsHidden_HomePhoneNumber" name="IsHidden_HomePhoneNumber"
                            <?php if ($vars['IsHidden_HomePhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                            <label for="IsHidden_HomePhoneNumber" class="m-0 ml-1 o-checkbox__label"><?= $words->get('hidden') ?></label></div>
                    </div>

                    <div class="o-form-group col-12 col-md-4">
                        <label for="CellPhoneNumber" class="col-form-label">
                            <?= $words->get('ProfileCellPhoneNumber') ?>
                        </label>
                        <input type="text" id="CellPhoneNumber" name="CellPhoneNumber" class="o-input"
                               value="<?php echo htmlentities($vars['CellPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <div class="o-checkbox">
                        <input type="checkbox" class="o-checkbox__input" value="Yes" id="IsHidden_CellPhoneNumber" name="IsHidden_CellPhoneNumber"
                            <?php if ($vars['IsHidden_CellPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <label for="IsHidden_CellPhoneNumber" class="m-0 ml-1 o-checkbox__label"><?= $words->get('hidden') ?></label>
                        </div>
                    </div>

                    <div class="o-form-group col-12 col-md-4">
                        <label for="WorkPhoneNumber" class="col-form-label">
                            <?= $words->get('ProfileWorkPhoneNumber') ?>
                        </label>
                        <input type="text" name="WorkPhoneNumber" class="o-input"
                               value="<?php echo htmlentities($vars['WorkPhoneNumber'], ENT_COMPAT, 'UTF-8'); ?>"/>
                        <div class="o-checkbox">
                        <input type="checkbox" class="o-checkbox__input" value="Yes" name="IsHidden_WorkPhoneNumber"
                            <?php if ($vars['IsHidden_WorkPhoneNumber'])
                                echo 'checked="checked"';
                            ?> />
                        <label for="IsHidden_WorkPhoneNumber" class="m-0 ml-1 o-checkbox__label"><?= $words->get('hidden') ?></label>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label for="WebSite" class="col-form-label">
                        <?= $words->get('Website') ?>
                    </label>
                    <input type="text" class="o-input" id="WebSite" name="WebSite"
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
                                <i class="<?php if ($me["image"] == 'user-plus') {
                                    echo 'fa';
                                } else {
                                    echo 'fab';
                                } ?> fa-<?= $me["image"]; ?> pr-2" alt="<?= $me["image"]; ?>"
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
        </div>
    </div>
</div>
