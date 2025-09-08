<?php


namespace app\controllers;


use app\models\Task;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class TaskController extends Controller
{
    public function actionIndex()
    {
        $task = new Task();
        $dataProvider = $task->search(Yii::$app->request->queryParams);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionSetState()
    {
        $id = Yii::$app->request->post('id');
        $checked = Yii::$app->request->post('checked');

        $model = Task::findOne($id);
        if($model) {
            $model->checked = (bool)$checked;
            if ($model->save()) {
                return json_encode(['success' => true]);
            }
        }
        throw new BadRequestHttpException();
    }
}