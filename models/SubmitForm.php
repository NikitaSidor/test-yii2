<?php

namespace app\models;

use yii\base\Model;

class SubmitForm extends Model
{
    public string $access_code = '';

    public function rules()
    {
        return [
            ['access_code', 'required'],
            ['access_code', 'string', 'max' => 10],
        ];
    }

    public function attributeLabels()
    {
        return [
            'access_code' => 'Код доступа'
        ];
    }
}