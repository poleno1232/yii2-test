<?php

namespace app\models;

use yii\db\ActiveRecord;

class Heading extends ActiveRecord
{
    public static function tableName()
    {
        return '{{headings}}';
    }
}