<?php

namespace app\models\actions;

class ActionRespond extends ActionAbstract
{
    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'Откликнуться на задание';
    }

    /**
     * @inheritDoc
     */
    public function getInternalName(): string
    {
        return 'actionRespond';
    }

    /**
     * Метод возврата ссылки на страницу задания
     * 
     * @param Task $task - объект класса app\models\Task
     * @return string|null - url страницы задания
     */
    public function getLink(): ?string
    {
        return null;
    }

    /**
     * Метод возврата CSS-свойства для элементов с заданием
     * 
     * @return string - CSS-свойство
     */
    public function getStyleClass(): string
    {
        return 'blue';
    }

    /**
     * Метод возврата значения data-атрибута для элементов с заданием
     * 
     * @return string - data-атрибут
     */
    public function getDataAction(): string
    {
        return 'act_response';
    }

    /**
     * @inheritDoc
     */
    public static function checkRights(
        int $userId,
        int $customerId,
        ?int $executorId = null
    ): bool {
        return $userId !== $customerId;
    }
}
