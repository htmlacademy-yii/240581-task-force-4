<?php

namespace app\models\actions;

use Yii;
use app\models\Task;

class ActionCancel extends ActionAbstract
{
    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'Отменить задание';
    }

    /**
     * @inheritDoc
     */
    public function getInternalName(): string
    {
        return 'actionCancel';
    }

    /**
     * Метод возврата ссылки на страницу задания
     * 
     * @param Task $task - объект класса app\models\Task
     * @return string - url страницы задания
     */
    public function getLink(Task $task): string
    {
        return Yii::$app->urlManager->createUrl(['tasks/cancel', 'id' => $task->task_id]);
    }

    /**
     * Метод возврата CSS-свойства для элементов с заданием
     * 
     * @return string - CSS-свойство
     */
    public function getStyleClass(): string
    {
        return 'orange';
    }

    /**
     * Метод возврата значения data-атрибута для элементов с заданием
     * 
     * @return string - data-атрибут
     */
    public function getDataAction(): string
    {
        return 'cancel';
    }

    /**
     * @inheritDoc
     */
    public static function checkRights(
        int $userId,
        int $customerId,
        ?int $executorId = null,
    ): bool {
        return $userId === $customerId;
    }
}
