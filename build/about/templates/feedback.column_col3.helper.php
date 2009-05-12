<?php
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AboutController', 'feedbackCallback');

$model = new FeedbackModel();

$categories = $model->getFeedbackCategories();

?>