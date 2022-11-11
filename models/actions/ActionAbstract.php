<?php

namespace app\models\actions;

abstract class ActionAbstract
{
    /**
     * Метод возврата "публичного" имени действия над заданием
     * 
     * @return string - именя действия
     */
    abstract public function getActionName(): string;

    /**
     * Метод возврата внутреннего имени действия над заданием
     * 
     * @return string - именя действия
     */
    abstract public function getInternalName(): string;

    /**
     * Метод проверки допустимости действия над заданием
     * 
     * @param int $userId - id пользователя
     * @param int $customerId - id заказчика
     * @param int $executorId - id исполнителя
     * @return bool
     */
    abstract public static function checkRights(
        int $userId,
        int $customerId,
        int $executorId,
    ): bool;
}
