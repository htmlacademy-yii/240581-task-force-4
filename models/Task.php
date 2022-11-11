<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use app\models\actions\ActionCancel;
use app\models\actions\ActionRespond;
use app\models\actions\ActionComplete;
use app\models\actions\ActionRefuse;
use app\models\exceptions\TaskException;


/**
 * This is the model class for table "task".
 *
 * @property int $task_id
 * @property int $customer_id
 * @property int|null $executor_id
 * @property int $category_id
 * @property int|null $city_id
 * @property string $task_name
 * @property string $task_essence
 * @property string $task_details
 * @property int|null $task_budget
 * @property string|null $task_latitude
 * @property string|null $task_longitude
 * @property string $task_date_create
 * @property string|null $task_status
 * @property string|null $task_deadline
 * @property int|null $grade
 * @property string|null $review
 * @property string|null $review_data_create
 *
 * @property Category $category
 * @property City $city
 * @property User $customer
 * @property User $executor
 * @property User[] $respondedExecutors
 * @property File[] $files
 * @property Respond[] $responds
 */
class Task extends \yii\db\ActiveRecord
{
    // Статусы задания
    public const STATUS_NEW = 'New';
    public const STATUS_CANCELED = 'Canceled';
    public const STATUS_IN_WORK = 'InWork';
    public const STATUS_PERFORMED = 'Performed';
    public const STATUS_FAILED = 'Failed';

    // Действия над заданием
    private const ACTION_CANCEL = 'actionCancel';
    private const ACTION_RESPOND = 'actionRespond';
    private const ACTION_COMPLETE = 'actionComplete';
    private const ACTION_REFUSE = 'actionRefuse';

    public const MAX_LENGTH_TASKNAME = 50;
    public const MAX_LENGTH_TASKESSENSE = 80;

    private const MAX_LENGTH_TASKSTATUS = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'category_id', 'task_name', 'task_essence', 'task_details'], 'required'],
            [['customer_id', 'executor_id', 'category_id', 'city_id', 'task_budget', 'grade'], 'integer'],
            [['task_date_create', 'task_deadline', 'review_data_create'], 'safe'],
            [['task_name'], 'string', 'max' => self::MAX_LENGTH_TASKNAME],
            [['task_essence'], 'string', 'max' => self::MAX_LENGTH_TASKESSENSE],
            [['task_details'], 'string'],
            [['task_latitude', 'task_longitude', 'review'], 'string', 'max' => City::MAX_LENGTH_COORDINATES],
            [['task_status'], 'string', 'max' => self::MAX_LENGTH_TASKSTATUS],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['customer_id' => 'user_id']],
            [['executor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'user_id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'category_id']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'city_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'task_id' => 'ID задания',
            'customer_id' => 'ID заказчика',
            'executor_id' => 'ID исполнителя',
            'category_id' => 'ID категории задания',
            'city_id' => 'ID города',
            'task_name' => 'Задание',
            'task_essence' => 'Требования задания',
            'task_details' => 'Подробности задания',
            'task_budget' => 'Бюджет задания',
            'task_longitude' => 'Долгота локации задания',
            'task_latitude' => 'Широта локации задания',
            'task_date_create' => 'Дата размещения задания',
            'task_status' => 'Статус задания',
            'task_deadline' => 'Срок исполнения задания',
            'grade' => 'Оценка задания',
            'review' => 'Отзыв',
            'review_date_create' => 'Дата создания отзыва',
        ];
    }

    /**
     * Функция возвращения  статуса, в которой перейдёт задание после выполнения указанного действия
     * 
     * @param string $action - применяемое к заданию действие
     * @return string - следующй статус задания либо null
     */
    public function getNextStatus(string $action): string
    {
        $map = [
            self::ACTION_CANCEL => self::STATUS_CANCELED,
            self::ACTION_RESPOND => self::STATUS_IN_WORK,
            self::ACTION_COMPLETE => self::STATUS_PERFORMED,
            self::ACTION_REFUSE => self::STATUS_FAILED,
        ];

        if (!isset($map[$action])) {
            throw new TaskException("'{$action}' - Неверное имя действия!");
        }
        return $map[$action] ?? null;
    }

    /**
     * Функция возвращения доступных действий для задания в зависимости от каегории актора
     * 
     * @param int $userId - id пользователя
     * @return object - доступное пользователю действие с заданием или null
     */
    public function getAvailableActions($user): ?object
    {
        if (!Yii::$app->user->can('executor') && !Yii::$app->user->can('customer')) {
            return null;
        }

        if ($this->task_status === self::STATUS_NEW && ActionCancel::checkRights($user->user_id, $this->customer_id)) return new ActionCancel();
        if ($this->task_status === self::STATUS_NEW && ActionRespond::checkRights($user->user_id, $this->customer_id)) return new ActionRespond();
        if ($this->task_status === self::STATUS_IN_WORK && ActionComplete::checkRights($user->user_id, $this->customer_id, $this->executor_id)) return new ActionComplete();
        if ($this->task_status === self::STATUS_IN_WORK && ActionRefuse::checkRights($user->user_id, $this->customer_id, $this->executor_id)) return new ActionRefuse();

        return null;
    }

    /**
     * Функция возвращения "карты" статусов задания
     * 
     * @return array - массив со статусами заданий
     */

    public static function getStatusMap(): array
    {
        return [
            self::STATUS_NEW => 'Новое',
            self::STATUS_CANCELED => 'Отменено',
            self::STATUS_IN_WORK => 'В работе',
            self::STATUS_PERFORMED => 'Выполнено',
            self::STATUS_FAILED => 'Провалено',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['category_id' => 'category_id']);
    }

    /**
     * Gets query for [[City]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['city_id' => 'city_id']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(User::class, ['user_id' => 'customer_id']);
    }

    /**
     * Gets query for [[Executor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExecutor()
    {
        return $this->hasOne(User::class, ['user_id' => 'executor_id']);
    }

    /**
     * Gets query for [[Executors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRespondedExecutors()
    {
        return $this->hasMany(User::class, ['user_id' => 'executor_id'])->viaTable('respond', ['task_id' => 'task_id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['task_id' => 'task_id']);
    }

    /**
     * Gets query for [[Responds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponds()
    {
        return $this->hasMany(Respond::class, ['task_id' => 'task_id']);
    }

    /**
     * Метод получения задач пользователя
     * 
     * @param string $taskStatus - статус задания
     * @param int $user_id - id пользователя
     * @return \yii\db\ActiveQuery
     */
    public static function getMyTasks(string $taskStatus, int $user_id): ActiveQuery
    {
        return Task::find()
            ->where(['or', ['customer_id' => $user_id], ['executor_id' => $user_id]])
            ->andWhere(['task_status' => $taskStatus]);
    }

    /**
     * Метод получения закрытых задач пользователя (неимеющих статус 'Новое' и ' В работе')
     * 
     * @param int $user_id - id пользователя
     * @return \yii\db\ActiveQuery
     */
    public static function getMyClosedTasks(int $user_id): ActiveQuery
    {
        return Task::find()
            ->where(['or', ['customer_id' => $user_id], ['executor_id' => $user_id]])
            ->andWhere(['not in', 'task_status', ['task_status' => Task::STATUS_NEW,  Task::STATUS_IN_WORK]]);
    }
}
