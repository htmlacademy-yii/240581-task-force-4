<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Respond;
use app\models\Task;
use app\models\exceptions\DataSaveException;

class RespondForm extends Model
{
    public $taskId;
    public $offerMessage;
    public $challengerPrice;

    private const PRICE_VALUE = 0;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['taskId', 'offerMessage', 'challengerPrice'], 'required'],
            [['taskId'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => ['taskId' => 'task_id']],
            [['challengerPrice'], 'compare', 'compareValue' => self::PRICE_VALUE, 'operator' => '>', 'type' => 'number'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'offerMessage' => 'Ваше предложение',
            'challengerPrice' => 'Ваша цена',
        ];
    }

    /**
     * Метод создания нового отклика на задание
     * 
     * @return Respond $response - объект класса Respond
     * @throws DataSaveException
     */
    public function createResponse(): Respond
    {
        $response = new Respond();
        $response->executor_id = Yii::$app->user->id;
        $response->task_id = $this->taskId;
        $response->challenger_price = $this->challengerPrice;
        $response->promising_message = $this->offerMessage;

        if (!$response->save()) {
            throw new DataSaveException('Ошибка сохранения задания');
        }
        return  $response;
    }
}
