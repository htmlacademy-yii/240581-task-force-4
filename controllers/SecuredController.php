<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

abstract class SecuredController extends Controller
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
                    return $this->redirect(['login/index']);
                },
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ],
            ]
        ];
    }


    /**
     * Метод регистрации титла страницы и метатегов для поисковых механизмов
     * 
     * @param string $title - титл страницы;
     * @param string $description - дескрипшен страницы;
     * @param string $keywords - ключевые слова страницы;
     */
    protected function setMeta($title = null, $description = null, $keywords = null): void
    {
        $this->view->title = $title;
        $this->view->registerMetaTag(['name' => 'description', 'content' => $description]);
        $this->view->registerMetaTag(['name' => 'keywords', 'content' => $keywords]);
        $this->view->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
    }
}
