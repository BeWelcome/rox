<?= print_r($vars, true); ?>

<div class="card card-block">
    <form method="post" action="<?php echo $baseuri . 'signup/4' ?>" name="signup" id="user-register-form">
        <?= $callback_tag ?>

        <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="alert alert-danger">' . $errors['inserror'] . '</span>';
        }
        ?>

        <div class="d-flex flex-row">
            <div class="d-block mr-3 pr-3">

                <h4 class="text-center mb-2">Step 4/4</h4>

                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 100%;"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><span class="white">100%</span></div>
                </div>

                <div class="h4 text-center d-none d-md-block mt-1">
                    <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                    <div class="my-3"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                    <div class="my-3"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                    <div class="my-3"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?>
                    </div>
                </div>

            </div>

            <div class="d-block w-50">


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
                            echo $vars['birthday'] . " - " . $vars['birthmonth'] . " - " . $vars['birthyear'];
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

                    <thead class="thead-default mt-5">
                    <tr>
                        <th colspan="2">
                            <?php echo $words->get('Accommodation'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('Location'); ?>
                        </th>
                        <td class="w-100 border-0">
                            <?php
                            echo $vars['location'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="float-right border-0 text-nowrap">
                            <?php echo $words->get('Accommodation'); ?>
                        </th>
                        <td class="border-0">
                            <?php
                            echo "<img src=\"images/icons/" . $vars['accommodation'] . ".png\">";
                            ?>
                        </td>
                    </tr>
                    </tbody>
                    <thead class="thead-default">
                    <tr>
                        <th colspan="2"><?php echo $words->get('SignupFeedback'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="2" class="border-0">
                            <p><?php echo $words->get('SignupFeedbackDescription'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="border-0">
                            <textarea class="form-control" name="feedback" rows="10">
                                <?php echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : ''; ?>
                            </textarea>
                        </td>
                    </tr>
                    </tbody>

                </table>

                <div class="d-flex">
                    <!-- terms -->
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="terms" name="terms" required
                            <?php
                            if (isset ($vars["terms"])) echo " checked=\"checked\""; // if user has already clicked, we will not bore him again
                            echo " >";
                            ?>
                            <?php echo $words->get('IAgreeWithTerms'); ?>
                        </label>
                    </div>
                    <?php
                    if (in_array('SignupMustAcceptTerms', $vars['errors'])) {
                        echo '<span class="text-muted alert alert-danger">' . $words->get('SignupTermsAndConditions') . '</span>';
                    }
                    ?>

                </div>

                <div class="d-flex">
                    <button type="submit"
                            class="form-control btn btn-primary"><?php echo $words->getSilent('SubmitForm'); ?> <i
                                class="fa fa-check-square"></i></button>
                    <?php echo $words->flushBuffer(); ?>

                </div>
            </div>
        </div>
    </form>
</div>

<!-- signup2 -->
<script type="text/javascript">
    jQuery(".select2").select2(); // {no_results_text: "<?= htmlentities($words->getSilent('SignupNoLanguageFound'), ENT_COMPAT); ?>"});
</script>