<?php

use yii\helpers\Url;


$this->title = 'Список задач';

$setStateUrl = Url::to(['task/set-state']);

$js = <<<JS
// Обработчик клика по чекбоксу

JS;

$this->registerJs($js);

?>

    <h1><?= \yii\helpers\Html::encode($this->title) ?></h1>

<?= 'Табличные данные' ?>