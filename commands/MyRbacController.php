<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;

/**
 * Инициализатор RBAC выполняется в консоли php yii my-rbac/init
 */
class MyRbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll(); //На всякий случай удаляем старые данные из БД...

        // Создадим роли заказчика и исполнителя заданий
        $customer = $auth->createRole(User::ROLE_CUSTOMER);
        $customer->description = 'Заказчик';
        $executor = $auth->createRole(User::ROLE_EXECUTOR);
        $executor->description = 'Исполнитель';

        // запишем их в БД
        $auth->add($customer);
        $auth->add($executor);

        // Назначаем роль заказчик пользователей по ID
        $auth->assign($customer, 2);
        $auth->assign($customer, 3);
        $auth->assign($customer, 4);
        $auth->assign($customer, 8);
        $auth->assign($customer, 9);
        $auth->assign($customer, 10);
        $auth->assign($customer, 14);
        $auth->assign($customer, 16);
        $auth->assign($customer, 17);
        $auth->assign($customer, 18);
        $auth->assign($customer, 32);
        $auth->assign($customer, 33);
        $auth->assign($customer, 34);
        $auth->assign($customer, 36);
        $auth->assign($customer, 37);
        $auth->assign($customer, 39);
        $auth->assign($customer, 40);
        $auth->assign($customer, 42);

        // Назначаем роль исполнитель пользователей по ID
        $auth->assign($executor, 1);
        $auth->assign($executor, 5);
        $auth->assign($executor, 6);
        $auth->assign($executor, 7);
        $auth->assign($executor, 11);
        $auth->assign($executor, 12);
        $auth->assign($executor, 13);
        $auth->assign($executor, 15);
        $auth->assign($executor, 19);
        $auth->assign($executor, 20);
        $auth->assign($executor, 35);
        $auth->assign($executor, 41);
    }
}
