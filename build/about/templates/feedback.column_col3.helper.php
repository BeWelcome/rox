<?php
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AboutController', 'feedbackCallback');

$categories = $this->model->getFeedbackCategories();

?>
