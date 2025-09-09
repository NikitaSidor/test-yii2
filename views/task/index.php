<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->title = 'Список задач';
$this->registerCsrfMetaTags();
$setStateUrl = Url::to(['task/set-state']);

$js = <<<JS
$(document).on('change', '.task-checkbox', function() {
    var checkbox = $(this);
    var taskId = checkbox.data('id');
    var isChecked = checkbox.is(':checked');
    var originalState = !isChecked;
    var csrf = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: '$setStateUrl',
        type: 'POST',
        dataType: 'json',
        data: {
            id: taskId,
            checked: isChecked ? 1 : 0,
            _csrf: csrf
        },
        success: function(response) {
            if (!response || !response.success) {
                checkbox.prop('checked', originalState);
                alert('Ошибка при сохранении');
            }
        },
        error: function(xhr, status, error) {
            checkbox.prop('checked', originalState);
            console.error('Ошибка AJAX:', error);
            alert('Ошибка сети. Попробуйте еще раз.');
        }
    });
});
JS;

$this->registerJs($js);
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php Pjax::begin(); ?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel ?? null,
        'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                        'attribute' => 'id',
                        'label' => 'ID',
                ],
                [
                        'attribute' => 'title',
                        'label' => 'Название задачи',
                ],
                [
                        'attribute' => 'checked',
                        'label' => 'Статус',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::checkbox('checked', (bool)$model->checked, [
                                    'class' => 'task-checkbox',
                                    'data-id' => $model->id,
                            ]);
                        },
                        'filter' => [0 => 'Не выполнена', 1 => 'Выполнена'],
                ],
        ],
]); ?>

<?php Pjax::end(); ?>
