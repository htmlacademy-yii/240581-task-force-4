<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $category_id
 * @property string $category_name
 * @property string $icon
 *
 * @property Task[] $tasks
 * @property UserCategory[] $userCategories
 * @property User[] $users
 */
class Category extends \yii\db\ActiveRecord
{
    private const MAX_LENGTH_CATEGORY_NAME = 30;
    private const MAX_LENGTH_ICON = 10;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_name', 'icon'], 'required'],
            [['category_name'], 'string', 'max' => self::MAX_LENGTH_CATEGORY_NAME],
            [['icon'], 'string', 'max' => self::MAX_LENGTH_ICON],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'ID категории',
            'category_name' => 'Категория',
            'icon' => 'Иконка категории',
        ];
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery|TaskQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['category_id' => 'category_id']);
    }

    /**
     * Gets query for [[UserCategories]].
     *
     * @return \yii\db\ActiveQuery|UserCategoryQuery
     */
    public function getUserCategories()
    {
        return $this->hasMany(UserCategory::class, ['category_id' => 'category_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['user_id' => 'user_id'])->viaTable('user_category', ['category_id' => 'category_id']);
    }

    /**
     * {@inheritdoc}
     * @return Category[]|array
     */
    public static function getAllCategories()
    {
        return Category::find()
            ->select('category_name')
            ->indexBy('category_id')
            ->column();
    }
}
