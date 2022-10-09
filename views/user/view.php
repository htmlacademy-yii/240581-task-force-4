<?php

/** @var yii\web\View $this
 * @var Executor[] $executors
 */

use yii\helpers\Html;
use app\models\Task;
use \yii\helpers\Url;

$this->title = 'Профиль'; ?>

<div class="left-column">
    <h3 class="head-main"><?= Html::encode($executor->executor_name) ?></h3>
    <div class="user-card">
        <div class="photo-rate">
            <img class="card-photo" src="<?= Html::encode($executor->executor_avatar) ?>" width="191" height="190" alt="Фото пользователя">
            <div class="card-rate">
                <div class="stars-rating big"><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span>&nbsp;</span></div>
                <span class="current-rate"><?= Html::encode($executorRating) ?></span>
            </div>
        </div>
        <p class="user-description">
            <?= Html::encode($executor->personal_information) ?>
        </p>
    </div>
    <div class="specialization-bio">
        <div class="specialization">
            <p class="head-info">Специализации</p>
            <ul class="special-list">
                <?php foreach ($executorCategories as $executorCategory) : ?>
                    <li class="special-item">
                        <a href="#" class="link link--regular"><?= $executorCategory->category_name ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="bio">
            <p class="head-info">Био</p>
            <p class="bio-info"><span class="country-info">Россия</span>, <span class="town-info"><?= Html::encode($city->city_name) ?></span>, <span class="age-info"><?= Yii::$app->i18n->format('{n, plural, =0{} =1{# год} one{# год} few{# лет} many{# год} other{# лет}}', ['n' => $executorAge], 'ru_RU') ?></p>
        </div>
    </div>
    <h4 class="head-regular">Отзывы заказчиков</h4>

    <?php foreach ($executorTasks as $executorTask) : ?>
        <div class="response-card">
            <?php $taskCustomer = Task::find()->with('customer')->where(['customer_id' =>$executorTask->customer_id])->one() ?>
            
            <img class="customer-photo" src="<?= Html::encode($taskCustomer->customer->customer_avatar) ?>" width="120" height="127" alt="Фото заказчиков">
            <div class="feedback-wrapper">
                <p class="feedback"><?= Html::encode($executorTask->review) ?></p>
                <p class="task">Задание «
                    <a href="<?= Url::to(['/tasks/view', 'id' => $executorTask->task_id]) ?>" class="link link--small">
                        <?= Html::encode($executorTask->task_name) ?></a>»
                    <?php if (Task::STATUS_FAILED === $executorTask->task_status) : ?>
                        <?= 'не' ?>
                        </a>
                    <?php endif ?>
                    выполнено
                </p>
            </div>
            <div class="feedback-wrapper">
                <div class="stars-rating small"><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span class="fill-star">&nbsp;</span><span>&nbsp;</span></div>
                <p class="info-text"><span class="current-time"><?= Yii::$app->formatter->asRelativeTime($executorTask->review_date_create) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="right-column">
    <div class="right-card black">
        <h4 class="head-card">Статистика исполнителя</h4>
        <dl class="black-list">
            <dt>Всего заказов</dt>
            <dd><?= Html::encode($executor->tasksCount) ?> выполнено, <?= Html::encode($executor->failTasksCount) ?> провалено</dd>
            <dt>Место в рейтинге</dt>
            <dd><?= Html::encode($executorRating) ?> место</dd>
            <dt>Дата регистрации</dt>
            <dd><?= Html::encode($executor->executor_date_add) ?></dd>
            <dt>Статус</dt>
            <dd><?= Html::encode($executor->executor_status) ?></dd>
        </dl>
    </div>
    <div class="right-card white">
        <h4 class="head-card">Контакты</h4>
        <ul class="enumeration-list">
            <li class="enumeration-item">
                <a href="#" class="link link--block link--phone"><?= Html::encode($executor->executor_phone) ?></a>
            </li>
            <li class="enumeration-item">
                <a href="#" class="link link--block link--email"><?= Html::encode($executor->executor_email) ?></a>
            </li>
            <li class="enumeration-item">
                <a href="#" class="link link--block link--tg"><?= Html::encode($executor->executor_telegram) ?></a>
            </li>
        </ul>
    </div>
</div>