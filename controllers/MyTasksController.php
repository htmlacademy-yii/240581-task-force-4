<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\Task;
use app\models\User;
use yii\db\Expression;


class MyTasksController extends SecuredController
{
    public const NEW_TASKS = 'Новые';
    public const IN_WORK_TASKS = 'В процессе';
    public const OVERDUE_TASKS = 'Просрочено';
    public const CLOSED_TASKS = 'Закрытые';

    /**
     * Страница со списком новых заданий пользователя
     *
     * @return string - код страницы со списком заданий
     */
    public function actionIndex(): string
    {
        $this->setMeta('Мои задания в работе');

        $user = Yii::$app->user->identity;

        if (Yii::$app->user->can(User::ROLE_CUSTOMER)) {
            $this->setMeta('Мои новые задания');

            $query = Task::getMyTasks(Task::STATUS_NEW, $user->user_id);
        }

        if (Yii::$app->user->can(User::ROLE_EXECUTOR)) {
            $this->setMeta('Мои новые задания');

            $query = Task::getMyTasks(Task::STATUS_IN_WORK, $user->user_id);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => [
                    'task_date_create' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Страница со списком заданий пользователя со статусом "В работе"
     *
     * @return string - код страницы со списком заданий
     */
    public function actionWork(): string
    {
        $this->setMeta('Мои задания в работе');

        $user = Yii::$app->user->identity;

        $query = Task::getMyTasks(Task::STATUS_IN_WORK, $user->user_id);

        if (Yii::$app->user->can(User::ROLE_EXECUTOR)) {
            $this->setMeta('Мои просроченные задания');
            
            $query->andWhere('task_deadline < :timestamp', ['timestamp' => new Expression(date("Y-m-d H:i:s"))]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => [
                    'task_date_create' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Страница со списком заданий пользователя со статусами: "Отменено", "Выполнено", "Провалено"
     *
     * @return string - код страницы со списком заданий
     */
    public function actionClosed(): string
    {
        $this->setMeta('Мои закрытые задания');

        $user = Yii::$app->user->identity;
        $query = Task::getMyClosedTasks($user->user_id);

        if (Yii::$app->user->can(User::ROLE_EXECUTOR)) {
            $this->setMeta('Мои закрытые задания');

            $query->andWhere(['not in', 'task_status', ['task_status' => Task::STATUS_CANCELED]]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
            'sort' => [
                'defaultOrder' => [
                    'task_date_create' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }
}
