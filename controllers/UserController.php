<?php

namespace app\controllers;

use Yii;
use yii\web\UploadedFile;
use app\models\Category;
use app\models\User;
use app\models\forms\EditProfileForm;
use app\models\forms\SecuritySettingsForm;
use app\models\helpers\CalculateHelper;
use app\models\helpers\FormatDataHelper;
use yii\web\NotFoundHttpException;


class UserController extends SecuredController
{
    /**
     * Страница просмотра профиля исполнителя
     *
     * @param int $id - id пользователя
     * @return string - код страницы просмотра задания
     * @throws NotFoundHttpException
     */

    public function actionView(int $id): string
    {
        // Страница предназначена только для показа профилей исполнителей. Соответственно, если этот пользователь не является исполнителем, то страница должна быть недоступна: вместо неё надо показывать ошибку 404.
        if (User::ROLE_CUSTOMER === array_values(Yii::$app->authManager->getRolesByUser($id))[0]->name) {
            throw new NotFoundHttpException();
        }

        $this->setMeta('Мой профиль');

        $currentUser = Yii::$app->user->identity;
        $showContacts = false;
        $userAge = CalculateHelper::calculateAge($currentUser->birthday);

        $this->setMeta('Профиль исполнителя');
        $keywords = 'Специализии: ';

        $user = User::find()
            ->with('city', 'categories', 'executorTasks')
            ->where(['user_id' => $id])
            ->one();

        $city = $user->city;
        $executorCategories = $user->categories;
        $executorTasks = $user->executorTasks;

        $clients = [];

        foreach ($executorTasks as $executorTask) {
            $clients[] = $executorTask->customer_id;
        }

        $clients = array_unique($clients, SORT_NUMERIC);

        foreach ($executorCategories as $executorCategory) {
            $keywords .= ', ' . $executorCategory->category_name;
        }

        if (Yii::$app->user->can(User::ROLE_EXECUTOR) && $user->show_contacts === User::SHOW_CONTACTS && $user->status === User::STATUS_FREE) {
            $description = 'Страница исполнителя, ищущего работу.' . $keywords;
            $this->setMeta('Профиль исполнителя', $description, $keywords);
        }

        // Блок с контактами показывается всем, только если у исполнителя в настройках не отмечена опция «Показывать мои контакты только заказчику». В противном случае, этот блок будет виден только пользователям, у которых данный исполнитель был назначен на задание и самому исполнителю
        if ($currentUser->id === $user->user_id || $user->show_contacts === User::SHOW_CONTACTS || in_array($currentUser->id, $clients, true)) {
            $showContacts = true;
        }

        $executorAverageGrade = round($user->getAverageGrade(), 1, PHP_ROUND_HALF_UP);
        $executorRatingPosition = $user->getRating();
        $user->status = User::getExecutorStatusMap()[$user->status];
        $user->date_add = FormatDataHelper::formatData($user->date_add);
        $user->phone = FormatDataHelper::formatPhone($user->phone);

        return $this->render(
            'view',
            [
                'user' => $user,
                'executorCategories' => $executorCategories,
                'city' => $city,
                'executorTasks' => $executorTasks,
                'userAge' => $userAge,
                'executorAverageGrade' => $executorAverageGrade,
                'executorRatingPosition' => $executorRatingPosition,
                'showContacts' => $showContacts,
            ]
        );
    }

    /**
     * Действие по "разлогиниванию" пользователя
     *
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return Yii::$app->response->redirect(['login']);
    }

    /**
     * Страница с формой настройки профиля пользователя
     * 
     */
    public function actionEdit()
    {
        $this->setMeta('Настройки профиля');

        $categories = Category::getAllCategories();
        $editProfileForm = new EditProfileForm();
        $user = $editProfileForm->getUser();
        $editProfileForm->autocompleteForm($editProfileForm, $user);

        if (Yii::$app->request->getIsPost()) {
            $editProfileForm->load(Yii::$app->request->post());
            $editProfileForm->avatar = UploadedFile::getInstance($editProfileForm, 'avatar');

            if ($editProfileForm->validate()) {
                $editProfileForm->takeUser($user);

                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            }
        }
        return $this->render('edit-profile', ['editProfileForm' => $editProfileForm, 'categories' => $categories]);
    }

    /**
     * Страница с формой настроек безопасности пользователя
     * 
     * @return string Страница с формой настроек безопасности
     */
    public function actionSet(): string
    {
        $this->setMeta('Настройки безопасности');

        $securitySettingsForm = new SecuritySettingsForm();

        if (Yii::$app->request->getIsPost()) {
            $securitySettingsForm->load(Yii::$app->request->post());


            if ($securitySettingsForm->validate() && $securitySettingsForm->changeSettings()) {

                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            }
        }
        return $this->render('edit-password', ['securitySettingsForm' => $securitySettingsForm]);
    }
}
