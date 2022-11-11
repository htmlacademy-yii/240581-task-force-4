<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\Task;


/**
 * This is the model class for table "user".
 *
 * @property int $user_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property string $date_add
 * @property int|null $city_id
 * @property string|null $phone
 * @property string|null $telegram
 * @property string|null $personal_information
 * @property int|null $rating
 * @property string|null $status
 * @property string|null $birthday
 * @property string $role
 *
 * @property Category[] $categories
 * @property City $city
 * @property Respond[] $responds
 * @property Task[] $customerTasks
 * @property Task[] $executorTasks
 * @property Task[] $respondedTasks
 * @property UserCategory[] $userCategories
 * @property int TasksCount $tasksCount
 * @property int FailTasksCount $failTasksCount
 * @property int SumGrade $sumGrade
 * @property int AverageGrade $averageGrade
 * @property int Rating $rating
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const USER_AVATAR_UPLOAD_PATH = '/uploads/avatars/';
    // Статусы исполнителя
    public const STATUS_FREE = 'free';
    public const STATUS_BUSY = 'busy';

    // Роли пользователя
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_EXECUTOR = 'executor';

    // Опции показа контактов
    public const SHOW_CONTACTS = 1; // Показывать
    public const HIDE_CONTACTS = 0; // Скрывать

    public const MAX_LENGTH_USERNAME = 50;
    public const MAX_LENGTH_FILD = 255;
    public const PHONE_LENGTH = 11;
    public const TELEGRAM_LENGTH = 64;
    public const MAX_LENGTH_STATUS = 10;
    public $password_repeat;

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email', 'password'], 'required'],
            [['date_add', 'birthday', 'password_repeat'], 'safe'],
            [['city_id', 'rating'], 'integer'],
            [['personal_information'], 'string'],
            [['name'], 'string', 'max' => self::MAX_LENGTH_USERNAME],
            [['email', 'password', 'avatar'], 'string', 'max' => self::MAX_LENGTH_FILD],
            [['phone'], 'string', 'max' => self::PHONE_LENGTH],
            [['telegram'], 'string', 'max' => self::TELEGRAM_LENGTH],
            [['status'], 'string', 'max' => self::MAX_LENGTH_STATUS],
            [
                'phone', 'match', 'pattern' => '/^[\d]{11}/i',
                'message' => 'Номер телефона должен состоять из 11 цифр'
            ],
            [['email'], 'unique'],
            [['avatar'], 'unique'],
            [['phone'], 'unique'],
            [['telegram'], 'unique'],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city_id' => 'city_id']],
        ];
    }

    /**
     * Метод валидации пароля
     * 
     * @param string $password - строка из поля 'password'
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'ID пользователя',
            'name' => 'Имя пользователя',
            'email' => 'Email пользователя',
            'password' => 'Пароль пользователя',
            'password_repeat' => 'Повтор пароля',
            'avatar' => 'Аватар пользователя',
            'date_add' => 'Дата регитрации пользователя',
            'city_id' => 'ID города',
            'executor_phone' => 'Телефон исполнителя',
            'executor_telegram' => 'Telegram исполнителя',
            'personal_information' => 'Персональная информация',
            'executor_rating' => 'Рейтинг исполнителя',
            'executor_status' => 'Статус исполнителя',
            'executor_birthday' => 'День рождения исполнителя',
        ];
    }

    /**
     * Функция возвращения "карты" статусов исполнителя
     * 
     * @return array - массив со статусами исполнителя
     */
    public static function getExecutorStatusMap(): array
    {
        return [
            self::STATUS_FREE => 'Открыт для новых заказов',
            self::STATUS_BUSY => 'Занят',
        ];
    }

    /**
     * Функция возвращения "карты" статусов исполнителя
     * 
     * @return array - массив со статусами исполнителя
     */
    public static function getUserRoleMap(): array
    {
        return [
            self::ROLE_CUSTOMER => 'Заказчик',
            self::ROLE_EXECUTOR => 'Исполнитель',
        ];
    }

    /**
     * Gets query for [[Categories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['category_id' => 'category_id'])->viaTable('user_category', ['user_id' => 'user_id']);
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
     * Gets query for [[Responds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResponds()
    {
        return $this->hasMany(Respond::class, ['executor_id' => 'user_id']);
    }

    /**
     * Gets query for [[Tasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerTasks()
    {
        return $this->hasMany(Task::class, ['customer_id' => 'user_id']);
    }

    /**
     * Gets query for [[executorTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExecutorTasks()
    {
        return $this->hasMany(Task::class, ['executor_id' => 'user_id']);
    }

    /**
     * Gets query for [[Tasks1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRespondedTasks()
    {
        return $this->hasMany(Task::class, ['task_id' => 'task_id'])->viaTable('respond', ['executor_id' => 'user_id']);
    }

    /**
     * Gets query for [[UserCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserCategories()
    {
        return $this->hasMany(UserCategory::class, ['user_id' => 'user_id']);
    }

    /**
     * Получение количества выполненных исполнителем заданий, включая проваленные
     *
     * @return int - количество выполненных исполнителем заданий, включая проваленные
     */
    public function getExecutorTasksCount(): int
    {
        return $this->getExecutorTasks()->count();
    }

    /**
     * Получение количества проваленных исполнителем заданий
     *
     * @return int - количество проваленных исполнителем заданий
     */
    public function getFailTasksCount(): int
    {
        return $this->getExecutorTasks()->where(['task_status' => Task::STATUS_FAILED])->count();
    }

    /**
     * Получение общей суммы оценок, полученных исполнителем за выполненные заданий
     *
     * @return int - Общая сумма оценок, полученных исполнителем за выполненные заданий
     */
    public function getSumGrade(): ?int
    {
        return $this->getExecutorTasks()->sum('grade');
    }

    /**
     * Получение общего количества оценок, полученных исполнителем за выполненные заданий
     *
     * @return int - Общее количества оценок, полученных исполнителем за выполненные заданий
     */
    public function getCountGrade(): int
    {
        return $this->getExecutorTasks()->where(['not in', 'grade', [null]])->count();
    }

    /**
     * Получение средней оценки, полученных исполнителем за выполненные заданий с учётом проваленных
     *
     * @return float - Средняя оценка, полученная исполнителем за выполненные заданий с учётом проваленных
     */
    public function getAverageGrade(): float
    {
        $average = $this->getExecutorTasks()
            ->where(['IN', 'task_status', [Task::STATUS_PERFORMED, Task::STATUS_FAILED]])
            ->average('grade');

        if (!$average) {
            return 0;
        }
        return $average;
    }

    /**
     * Получение рейтинга исполнителя среди других исполнителей
     *
     * @return int -  Рейтинг исполнителя
     */
    public function getRating(): ?int
    {
        $data = User::find()
            ->alias('u')
            ->leftJoin(Task::tableName() . ' t', 't.executor_id = u.user_id')
            ->where(['IN', 't.task_status', [Task::STATUS_PERFORMED, Task::STATUS_FAILED]])
            ->groupby(['u.user_id'])
            ->having(new Expression('AVG(t.grade) >= :grade', [':grade' => $this->getAverageGrade()]))
            ->orderBy(['AVG(t.grade)' => SORT_DESC])
            ->all();

        for ($i = count($data) - 1; $i >= 0; $i--) {
            if ($data[$i]->user_id === $this->user_id) {

                return $i + 1;
            }
        }
        return null;
    }
}
