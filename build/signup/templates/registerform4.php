<div class="card card-block w-100">
    <form method="post" name="signup" id="user-register-form">
        <?= $callback_tag ?>
        <input type="hidden" name="feedback" id="feedback" value="">
        <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="alert alert-danger">' . $errors['inserror'] . '</span>';
        }
        ?>

        <div class="row">
            <div class="col-12 col-md-3">

                <h4 class="text-center mb-2">Step 4/5</h4>

                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 80%;"
                         aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"><span class="white">80%</span></div>
                </div>

                <div class="h4 text-center d-none d-md-block mt-1">
                    <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                    <div class="my-3"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                    <div class="my-3"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                    <div class="my-3"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?>
                    </div>
                </div>

            </div>

            <div class="col-12 col-md-9">

                    <!-- terms -->
                    <div class="checkbox w-100">
                        <label>
                            <input type="checkbox" id="terms" name="terms" required
                            <?php
                            if (isset ($vars["terms"])) echo " checked"; // if user has already clicked, we will not bore him again
                            echo " >";
                            ?>
                            <?php echo $words->get('IAgreeWithTerms'); ?>
                        </label>
                    </div>
                    <?php
                    if (in_array('SignupMustAcceptTerms', $vars['errors'])) {
                        echo '<div class="w-100 text-muted alert alert-danger">' . $words->get('SignupTermsAndConditions') . '</div>';
                    }
                    ?>

                    <button type="submit"
                            class="form-control btn btn-primary"><?php echo $words->getSilent('SubmitForm'); ?> <i
                                class="fa fa-check-square"></i></button>
                    <?php echo $words->flushBuffer(); ?>


                <table class="table">
                    <thead class="thead-default">
                    <tr>
                        <th colspan="2">
                            <?php
                            echo $words->get('LoginInformation');
                            ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('SignupUsername'); ?>
                        </th>
                        <td class="w-100 border-0"><?php
                            echo htmlentities($vars['username'], ENT_COMPAT, 'utf-8');
                            ?></td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('SignupPassword'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            // TODO: replace special characters
                            $password = preg_replace("^[A-Za-z0-9]^", "*", $vars['password']);
                            echo $password;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-top-0 text-nowrap">
                            <?php echo $words->get('SignupEmail'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            echo htmlentities($vars['email']);
                            ?>
                        </td>
                    </tr>
                    </tbody>

                    <thead class="thead-default">
                    <tr>
                        <th colspan="2">
                            <?php echo $words->get('SignupName'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">Full name</th>
                        <td class="w-100 border-0">
                            <?php
                            echo htmlentities(strip_tags($vars['firstname']));
                            if (isset($vars['secondname'])) {
                                echo " " . htmlentities(strip_tags($vars['secondname']));
                            }
                            echo " " . htmlentities(strip_tags($vars['lastname']));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('SignupBirthDate'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            //TODO: name the month to prevent confusion
                            echo $vars['birthdate'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('SignupGender'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            echo $vars['gender'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-top-0 text-nowrap">
                            <?php echo $words->get('LanguageLevel_MotherLanguage'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            // TODO: Select the name of the language ID
                            echo $vars['mothertongue'];
                            ?>
                        </td>
                    </tr>
                    </tbody>

                    <tbody>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('Accommodation'); ?>
                        </th>
                        <td class="w-100 border-0">
                            <?php
                            echo '<img src="images/icons/' . $vars['accommodation'] . '.png"> ' . $vars['location'];
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <button type="submit"
                        class="form-control btn btn-primary"><?php echo $words->getSilent('SubmitForm'); ?> <i
                            class="fa fa-check-square"></i></button>
                <?php echo $words->flushBuffer(); ?>

            </div>
        </div>
    </form>
</div>