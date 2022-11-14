<?php

/** @var yii\web\View $this
 * @var Task[] $tasks
 */

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\File;
use app\models\Respond;
use app\models\Task;
use app\models\User;
use app\widgets\ButtonPopupWidget;
use app\widgets\ExecutorStarsWidget;
use app\widgets\MapWidget;

?>

<main class="container main-content">
    <div class="left-column">
        <div class="head-wrapper">
            <h3 class="head-main" data-name="<?= Html::encode($task->task_name) ?>"><?= Html::encode($task->task_name) ?></h3>
            <p class="price price--big"><?= Html::encode($task->task_budget) ?> ₽</p>
        </div>
        <p class="task-description"><?= Html::encode($task->task_details) ?></p>
        <?php if ($showAvailableAction) : ?>
            <?= Html::a(Html::encode($availableAction->getActionName()), $availableAction->getLink($task), ['class' => "button button--{$availableAction->getStyleClass()} action-btn", 'data-action' => $availableAction->getDataAction()]); ?>
        <?php endif; ?>
        <?php if (isset($task->task_latitude, $task->task_longitude)) : ?>
            <div class="task-map">
                <?= MapWidget::widget(['task' => $task]) ?>
            </div>
        <?php endif; ?>
        <?php if ($user->user_id === $task->customer_id || Yii::$app->user->can('executor')) : ?>
            <h4 class="head-regular">Отклики на задание</h4>
            <?php foreach ($task->responds as $response) : ?>
                <?php if ($user->user_id === $task->customer_id || $user->user_id === $response->executor_id) : ?>
                    <div class="response-card">
                        <img class="customer-photo" src="<?= file_exists($response->executor->avatar) ? Html::encode(User::USER_AVATAR_UPLOAD_PATH . $response->executor->avatar) : Html::encode('img/man-glasses.png') ?>" width="146" height="156" alt="Фото заказчиков">
                        <div class="feedback-wrapper">
                            <a href="<?= Url::to(['user/view', 'id' => $response->executor_id]) ?>" class="link link--block link--big"><?= Html::encode($response->executor->name) ?></a>
                            <div class="response-wrapper">
                                <div class="stars-rating small"><?= ExecutorStarsWidget::widget(['rating' => $response->executor->averageGrade]) ?></div>
                                <p class="reviews"><?= Html::encode($response->executor->getCountGrade()) ?> отзыва</p>
                            </div>
                            <p class="response-message">
                                <?= Html::encode($response->promising_message) ?>
                            </p>

                        </div>
                        <div class="feedback-wrapper">
                            <p class="info-text"><span class="current-time"><?= isset($response->date_add) ? Yii::$app->formatter->asRelativeTime($response->date_add) : '' ?> </span>назад</p>
                            <p class="price price--small"><?= Html::encode($response->challenger_price) ?></p>
                        </div>
                        <?php if ($user->user_id === $task->customer_id && Respond::STATUS_REJECTED !== $response->accepted && Task::STATUS_NEW === $task->task_status) : ?>
                            <div class="button-popup">
                                <?= ButtonPopupWidget::widget(['response' => $response]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="right-column">
        <div class="right-card black info-card">
            <h4 class="head-card">Информация о задании</h4>
            <dl class="black-list">
                <dt>Категория</dt>
                <dd><?= Html::encode($category->category_name) ?></dd>
                <dt>Дата публикации</dt>
                <dd><?= Yii::$app->formatter->asRelativeTime($task->task_date_create) ?></dd>
                <dt>Срок выполнения</dt>
                <dd><?= Html::encode($deadline) ?></dd>
                <dt>Статус</dt>
                <dd><?= Html::encode(Task::getStatusMap()[$task->task_status]) ?></dd>
            </dl>
        </div>
        <div class="right-card white file-card">
            <h4 class="head-card">Файлы задания</h4>
            <ul class="enumeration-list">
                <?php foreach ($files as $file) : ?>
                    <li class="enumeration-item">
                        <?= Html::a(Html::encode($file->task_file_base_name), ['tasks/download', 'path' => $file->task_file_name], ['class' => 'link link--block link--clip']) ?>
                        <p class="file-size"><?= Yii::$app->formatter->asShortSize(filesize(Yii::getAlias(File::USER_FILE_UPLOAD_PATH) . $file->task_file_name)); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <?= $this->render('respond', ['task' => $task, 'responseForm' => $responseForm]); ?>
    <?= $this->render('complete', ['task' => $task, 'completeForm' => $completeForm]); ?>
    <?= $this->render('refuse', ['task' => $task]); ?>
</main>