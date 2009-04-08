<?php
    $words = $this->getWords();
    $layoutkit = $this->layoutkit;
    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'myPreferencesCallback');
    $languages = $this->member->get_languages_all();
    $p = $this->member->preferences;
    // var_dump ($p);
    $ii = 1;
    
    if (!$memory = $formkit->getMemFromRedirect()) {
        // no memory
    } else {
        // from previous form
        if ($memory->post) {
            foreach ($memory->post as $key => $value) {
                $vars[$key] = $value;
            }
        }
        // problems from previous form
        if (is_array($memory->problems)) {
            echo '<div class="error">';
            foreach ($memory->problems as $key => $value) {
                ?>
                <p><?=$words->get($value) ?></p>
                <?php
            }
            echo '</div>';
        }
    }
    // var_dump($vars);
