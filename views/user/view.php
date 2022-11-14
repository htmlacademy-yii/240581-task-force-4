<?php

/** @var yii\web\View $this
 * @var Executor[] $executors
 */

use yii\helpers\Html;
use \yii\helpers\Url;
use app\models\Task;
use app\models\User;
use app\widgets\ExecutorStarsWidget;

?>

<main class="container main-content">
    <div class="left-column">
        <h3 class="head-main"><?= Html::encode($user->name) ?></h3>
        <div class="user-card">
            <div class="photo-rate">
                <img class="card-photo" src="<?= file_exists($user->avatar) ? Html::encode(User::USER_AVATAR_UPLOAD_PATH . $user->avatar) : Html::encode('img/man-glasses.png') ?>" width="191" height="190" alt="Фото пользователя">
                <?php if (Yii::$app->user->can(User::ROLE_EXECUTOR)) : ?>
                    <div class="card-rate">
                        <div class="stars-rating big"><?= ExecutorStarsWidget::widget(['rating' => $executorAverageGrade]) ?></div>
                        <span class="current-rate"><?= Html::encode($executorAverageGrade) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (Yii::$app->user->can(User::ROLE_EXECUTOR)) : ?>
                <p class="user-description">
                    <?= Html::encode($user->personal_information) ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="specialization-bio">
            <div class="specialization">
                <p class="head-info">Специализации</p>
                <ul class="special-list">
                    <?php foreach ($executorCategories as $executorCategory) : ?>
                        <li class="special-item">
                            <a href="<?= Url::to(['tasks/index', 'category' => Html::encode($executorCategory->category_id)]) ?>" class="link link--regular"><?= $executorCategory->category_name ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bio">
                <p class="head-info">Био</p>
                <p class="bio-info"><span class="country-info">Россия</span>, <span class="town-info"><?= isset($city->city_name) ?  Html::encode($city->city_name) : ''; ?></span>, <span class="age-info"><?= Yii::$app->i18n->format('{n, plural, =0{} =1{# год} one{# год} few{# лет} many{# год} other{# лет}}', ['n' => $userAge], 'ru_RU') ?></p>
            </div>
        </div>
        <?php if ($executorAverageGrade) : ?>
            <h4 class="head-regular">Отзывы заказчиков</h4>
            <?php foreach ($executorTasks as $executorTask) : ?>
                <div class="response-card">
                    <?php $taskCustomer = Task::find()->with('customer')->where(['customer_id' => $executorTask->customer_id])->one() ?>

                    <img class="customer-photo" src="<?= file_exists($taskCustomer->customer->avatar) ? Html::encode($taskCustomer->customer->avatar) : Html::encode('img/man-sweater.png') ?>" width="120" height="127" alt="Фото заказчиков">
                    <div class="feedback-wrapper">
                        <p class="feedback"><?= Html::encode($executorTask->review) ?></p>
                        <p class="task">Задание «
                            <a href="<?= Url::to(['tasks/view', 'id' => $executorTask->task_id]) ?>" class="link link--small">
                                <?= Html::encode($executorTask->task_name) ?></a>»
                            <?php if (Task::STATUS_FAILED === $executorTask->task_status) : ?>
                                <?= 'не' ?>
                                </a>
                            <?php endif ?>
                            выполнено
                        </p>
                    </div>
                    <div class="feedback-wrapper">
                        <div class="stars-rating small"><?= ExecutorStarsWidget::widget(['rating' => $executorTask->grade]) ?></div>
                        <p class="info-text"><span class="current-time"><?= isset($executorTask->review_date_create) ? Yii::$app->formatter->asRelativeTime($executorTask->review_date_create) : '' ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="right-column">
        <div class="right-card black">
            <h4 class="head-card">Статистика исполнителя</h4>
            <dl class="black-list">
                <dt>Всего заказов</dt>
                <dd><?= Html::encode($user->executorTasksCount) ?> выполнено, <?= Html::encode($user->failTasksCount) ?> провалено</dd>
                <dt>Место в рейтинге</dt>
                <dd><?= Html::encode($executorRatingPosition) ?> место</dd>
                <dt>Дата регистрации</dt>
                <dd><?= Html::encode($user->date_add) ?></dd>
                <dt>Статус</dt>
                <dd><?= Html::encode($user->status) ?></dd>
            </dl>
        </div>
        <?php if ($showContacts) : ?>
            <div class="right-card white">
                <h4 class="head-card">Контакты</h4>
                <ul class="enumeration-list">
                    <li class="enumeration-item">
                        <a href="#" class="link link--block link--phone"><?= Html::encode($user->phone) ?></a>
                    </li>
                    <li class="enumeration-item">
                        <a href="#" class="link link--block link--email"><?= Html::encode($user->email) ?></a>
                    </li>
                    <li class="enumeration-item">
                        <a href="#" class="link link--block link--tg"><?= Html::encode($user->telegram) ?></a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</main>