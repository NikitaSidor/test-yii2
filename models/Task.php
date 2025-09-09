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
            [['order', 'title'], 'required'], // ID не обязателен для новых записей
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

        // Загрузка параметров фильтрации
        $this->load($params);

        // Фильтрация
        $filteredTasks = array_filter($tasks, function($task) {
            $match = true;
            if ($this->title) {
                $match = stripos($task->title, $this->title) !== false;
            }
            if ($match && $this->checked !== null && $this->checked !== '') {
                // Преобразуем к boolean для сравнения
                $taskChecked = filter_var($task->checked, FILTER_VALIDATE_BOOLEAN);
                $filterChecked = filter_var($this->checked, FILTER_VALIDATE_BOOLEAN);
                $match = $taskChecked === $filterChecked;
            }
            return $match;
        });

        // Сортировка по order
        usort($filteredTasks, fn($a, $b) => $a->order - $b->order);

        return new ArrayDataProvider([
            'allModels' => $filteredTasks,
            'sort' => [
                'attributes' => ['id', 'order', 'title', 'checked'],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $tasks = self::getAllTasks();
        $found = false;

        // Если это новая запись (без ID), генерируем новый ID
        if (empty($this->id)) {
            $this->id = $this->generateNewId($tasks);
        } else {
            $this->id = (int)$this->id;
        }

        // Преобразуем checked к правильному формату
        $this->checked = filter_var($this->checked, FILTER_VALIDATE_BOOLEAN);

        foreach ($tasks as $index => $task) {
            if ((int)$task->id === (int)$this->id) {
                $tasks[$index] = clone $this;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $tasks[] = clone $this;
        }

        // Сохраняем в кэш
        Yii::$app->cache->set('tasks', $tasks);
        return true;
    }

    public function delete()
    {
        $tasks = self::getAllTasks();
        $newTasks = array_filter($tasks, function($task) {
            return (int)$task->id !== (int)$this->id;
        });

        return Yii::$app->cache->set('tasks', array_values($newTasks));
    }

    private function generateNewId($tasks)
    {
        $maxId = 0;
        foreach ($tasks as $task) {
            if ($task->id > $maxId) {
                $maxId = $task->id;
            }
        }
        return $maxId + 1;
    }

    public static function findOne($id)
    {
        if ($id === null) return null;

        $tasks = self::getAllTasks();
        foreach ($tasks as $task) {
            if ((int)$task->id === (int)$id) {
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
                $task->checked = filter_var($data['checked'], FILTER_VALIDATE_BOOLEAN);
                $tasks[] = $task;
            }
            Yii::$app->cache->set('tasks', $tasks);
        }
        return $tasks;
    }
}