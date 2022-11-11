<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\models\forms\LoginForm;
use app\models\User;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;


class LoginController extends Controller
{
    /**
     * {@inheritdoc}
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
                        'roles' => ['?'],

                    ],
                ]
            ]
        ];
    }

    /**
     * Гостевая страница.
     * 
     * 
     */

    public function actionIndex()
    {
        $this->layout = 'landing';

        $loginForm = new LoginForm();

        if (Yii::$app->request->getIsPost()) {
            $loginForm->load(Yii::$app->request->post());

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($loginForm);
            }

            if ($loginForm->validate()) {
                $user = $loginForm->getUser();
                Yii::$app->user->login($user);

                return $this->goHome();
            }
        }
        return $this->render('index', ['loginForm' => $loginForm]);
    }

    /**
     * Объявляет внешние действия для контроллера
     * 
     * @return array
     */
    public function actions(): array
    {
        return [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }


    /**
     * @param $client - экземпляр класса, реализующего yii\authclient\ClientInterface
     * 
     * @return Response
     */
    public function onAuthSuccess(ClientInterface $client)
    {
        $attributes = $client->getUserAttributes();
        $email = ArrayHelper::getValue($attributes, 'email');

        if (!$email) {
            throw new BadRequestHttpException('Email отсутствует');
        }

        $user = User::findOne(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        Yii::$app->user->login($user);

        return $this->goHome();
    }
}
