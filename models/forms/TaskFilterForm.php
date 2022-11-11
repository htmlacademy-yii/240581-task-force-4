<?php

namespace app\models\forms;

use app\models\Task;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * This is the model class for task filter form.
 * 
 * @property array TASK_PERIOD
 * @property string|array $categories
 * @property bool $distantWork
 * @property bool $noResponse
 * @property string|int $period
 * 
 * @property NewTasks[] newTasks
 */
class TaskFilterForm extends Model
{
    // Параметры фильтра отображения заданий в 'index.search-form' по прошедшему времени с момента объявления 
    public const TASK_PERIOD = [
        '1' => '1 час',
        '12' => '12 часов',
        '24' => '24 часа',
        '' => 'Все задания',
    ];

    public string|array $categories = '';
    public bool $distantWork = false;
    public bool $noResponse = false;
    public string|int $period = '';

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return 'taskFilterForm';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'categories' => 'Категории',
            'distantWork' => 'Удаленная работа',
            'noResponse' => 'Без откликов',
            'period' => 'Период',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['categories', 'distantWork', 'noResponse', 'period'], 'safe'],
        ];
    }

    /**
     * Функция выборки параметров нового задания
     * 
     * @return ActiveQuery - результат выборки
     */
    public function getNewTasks(): ActiveQuery
    {
        $query = Task::find()
            ->with('category')
            ->filterWhere([
                'task_status' => Task::STATUS_NEW,
                'category_id' => $this->categories,
            ]);

        if ($this->distantWork) {
            $query->andWhere(
                ['task_latitude' => null],
            );
        }

        if ($this->noResponse) {
            $query->andWhere(
                ['executor_id' => null],
            );
        }

        if ($this->period) {
            $query->andWhere(
                'task_date_create >= NOW() - INTERVAL :period  HOUR',
                [':period' => $this->period],
            );
        }

        return $query->orderBy(
            ['task_date_create' => SORT_DESC],
        );
    }
}
