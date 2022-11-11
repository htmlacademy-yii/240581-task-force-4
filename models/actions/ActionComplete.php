<?php

namespace app\models\actions;

class ActionComplete extends ActionAbstract
{
    /**
     * @inheritDoc
     */
    public function getActionName(): string
    {
        return 'Завершить задание';
    }

    /**
     * @inheritDoc
     */
    public function getInternalName(): string
    {
        return 'actionComplet';
    }

    /**
     * Метод возврата ссылки на страницу задания
     * 
     * @param Task $task - объект класса app\models\Task
     * @return string - url страницы задания
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
        return 'pink';
    }

    /**
     * Метод возврата значения data-атрибута для элементов с заданием
     * 
     * @return string - data-атрибут
     */
    public function getDataAction(): string
    {
        return 'completion';
    }

    /**
     * @inheritDoc
     */
    public static function checkRights(
        int $userId,
        int $customerId,
        int $executorId
    ): bool {
        return $userId === $customerId;
    }
}
