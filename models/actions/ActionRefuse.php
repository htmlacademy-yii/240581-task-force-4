<?php

namespace app\models\actions;

class ActionRefuse extends ActionAbstract
{
    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'Отказаться от задания';
    }

    /**
     * @inheritDoc
     */
    public function getInternalName(): string
    {
        return 'actionRefuse';
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
        return 'orange';
    }

    /**
     * Метод возврата значения data-атрибута для элементов с заданием
     * 
     * @return string - data-атрибут
     */
    public function getDataAction(): string
    {
        return 'refusal';
    }

    /**
     * @inheritDoc
     */
    public static function checkRights(
        int $userId,
        int $customerId,
        int $executorId
    ): bool {
        return $userId === $executorId;
    }
}
