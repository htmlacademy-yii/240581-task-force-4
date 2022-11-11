<?php

namespace app\models;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\exceptions\DataSaveException;


/**
 * This is the model class for table "respond".
 *
 * @property int $respond_id
 * @property int $executor_id
 * @property int $task_id
 * @property int|null $accepted
 * @property int|null $challenger_price
 * @property string $date_add
 * @property string|null $promising_message
 *
 * @property User $executor
 * @property Task $task
 */
class Respond extends \yii\db\ActiveRecord
{
    // Статусы отклика
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ACCEPTED = 'accepted';

    // Действия над откликом
    private const ACTION_REJECT = 'actionReject';
    private const ACTION_ACCEPT = 'actionAccept';

    private const MAX_LENGTH_ACCEPTED = 10;
    private const MAX_PROMISING_MESSAGE = 255;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'respond';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['executor_id', 'task_id'], 'required'],
            [['executor_id', 'task_id', 'challenger_price'], 'integer'],
            [['date_add'], 'safe'],
            [['accepted'], 'string', 'max' => self::MAX_LENGTH_ACCEPTED],
            [['promising_message'], 'string', 'max' => self::MAX_PROMISING_MESSAGE],
            [['executor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'user_id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'task_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'respond_id' => 'ID отклика',
            'executor_id' => 'ID исполнителя',
            'task_id' => 'ID задания',
            'accepted' => 'Принято к исполнению',
            'challenger_price' => 'Цена исполнителя',
            'date_add' => 'Дата отклика',
            'promising_message' => 'Сообщение исполнителя',
        ];
    }

    /**
     * Функция возвращения "карты" статусов отклика
     * 
     * @return array - массив со статусами отклика
     */
    public static function getUserRoleMap(): array
    {
        return [
            self::STATUS_REJECTED => 'Отказано',
            self::STATUS_ACCEPTED => 'Принято',
        ];
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
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['task_id' => 'task_id']);
    }

    /**
     * Действие заказчика с откликом на задание
     * 
     * @param int $respond_id - id отклика
     * @param string $offerStatus - новый статус задания
     * @return Respond|null
     * @throws NotFoundHttpException
     * @throws DataSaveException
     */
    public static function processOffer(int $respond_id, string $offerStatus): ?Respond
    {
        $user = Yii::$app->user->getIdentity();
        $response = Respond::find()
            ->with('task')
            ->where(['respond_id' => $respond_id])
            ->one();

        $task = $response->task;

        if ($task->executor_id) {
            return $response;
        }

        if (!$response) {
            throw new NotFoundHttpException();
        }

        if ($user->user_id === $task->customer_id) {
            $response->accepted = $offerStatus;

            $task->executor_id = $response->executor_id;
            $task->task_status = Task::STATUS_IN_WORK;

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $response->save();

                if (Respond::STATUS_ACCEPTED === $offerStatus) {
                    $task->save();
                }

                $transaction->commit();

                return $response;
            } catch (DataSaveException $e) {
                $transaction->rollback();

                throw new DataSaveException('Не удалось выполнить действие', $e);
            }
        }
    }

    /**
     * Метод получения отклика на задание
     * 
     * @param int $user_id - id исполнителя
     * @param int $task_id - id задания
     * @return Respond|null
     */
    public static function getResponse(int $user_id, int $task_id): ?Respond
    {
        return Respond::find()
            ->andWhere(['executor_id' => $user_id, 'task_id' => $task_id])
            ->one();
    }
}
