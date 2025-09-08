<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class Task extends Model
{
    public $id;
    public $order;
    public $title;
    public $checked;

    const INIT_DATA = [
        ['id' => 4, 'order' => 2, 'title' => 'Сделать тестовое задание', 'checked' => false],
        ['id' => 2, 'order' => 1, 'title' => 'Написать документацию', 'checked' => true],
        ['id' => 1, 'order' => 3, 'title' => 'Отправить на проверку', 'checked' => false],
    ];

    public function rules()
    {
        return [
            [['id', 'order'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['checked'], 'boolean'],
            [['id', 'order', 'title'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order' => 'Порядок',
            'title' => 'Название задачи',
            'checked' => 'Выполнена',
        ];
    }

    public function search($params)
    {
        $tasks = self::getAllTasks();

        // Сортировка по полю order в порядке возрастания
        usort($tasks, function ($a, $b) {
            return $a->order - $b->order;
        });

        $dataProvider = new ArrayDataProvider([
            'allModels' => $tasks,
            'sort' => [
                'attributes' => ['id', 'order', 'title', 'checked'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // Загрузка параметров фильтрации
        $this->load($params);

        return $dataProvider;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        // Получаем ВСЕ задачи из кэша
        $tasks = self::getAllTasks();

        $found = false;
        foreach ($tasks as $index => $task) {
            if ($task->id == $this->id) {
                // Обновляем найденную задачу текущими данными модели
                $tasks[$index] = $this;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $tasks[] = $this;
        }

        // КРИТИЧЕСКОЕ ИСПРАВЛЕНИЕ: Сохраняем измененный массив ВСЕХ задач обратно в кэш
        return Yii::$app->cache->set('tasks', $tasks);
    }

    public static function findOne($id)
    {
        // Защита от передачи null или нечислового значения
        if ($id === null) {
            return null;
        }

        $tasks = self::getAllTasks();
        foreach ($tasks as $task) {
            // Строгое сравнение, учитывая тип
            if ($task->id == $id) {
                return $task;
            }
        }
        return null;
    }

    public static function getAllTasks()
    {
        $tasks = Yii::$app->cache->get('tasks');

        if ($tasks === false) {
            $tasks = [];
            foreach (self::INIT_DATA as $data) {
                $task = new self();
                $task->attributes = $data;
                $tasks[] = $task;
            }
            Yii::$app->cache->set('tasks', $tasks);
        }

        return $tasks;
    }
}