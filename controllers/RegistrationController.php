<?php

namespace app\controllers;

use Yii;
use app\models\forms\RegistrationForm;
use yii\filters\AccessControl;

class RegistrationController extends \yii\web\Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'denyCallback' => function () {
                    return $this->redirect(['tasks/index']);
                },
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['?']
                    ]
                ]
            ]
        ];
    }

    /**
     * Страница с формой регистрации нового пользователя
     * 
     * @return string cтраница с формой регистрации
     */
    public function actionIndex()
    {
        $this->view->title = 'Регистрация';

        $registrationForm = new RegistrationForm();

        if (Yii::$app->request->getIsPost()) {
            $registrationForm->load(Yii::$app->request->post());
        }

        if ($registrationForm->validate() && $registrationForm->createUser()) {

            return $this->redirect(['login/index']);
        }

        return $this->render(
            'index',
            [
                'registrationForm' => $registrationForm,
            ]
        );
    }
}
