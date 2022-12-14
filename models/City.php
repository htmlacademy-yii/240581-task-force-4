<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "city".
 *
 * @property int $city_id
 * @property string $city_name
 * @property string|null $city_latitude
 * @property string|null $city_longitude
 *
 * @property Task[] $tasks
 * @property User[] $users
 */
class City extends \yii\db\ActiveRecord
{
    public const MAX_LENGTH_COORDINATES = 255;

    private const MAX_LENGTH_CITY_NAME = 50;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['city_name'], 'required'],
            [['city_name'], 'string', 'max' => self::MAX_LENGTH_CITY_NAME],
            [['city_latitude', 'city_longitude'], 'string', 'max' => self::MAX_LENGTH_COORDINATES],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'city_id' => 'ID города',
            'city_name' => 'Город',
            'city_latitude' => 'Географическая широта',
            'city_longitude' => 'Географическая долгота',
        ];
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['city_id' => 'city_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['city_id' => 'city_id']);
    }
}
