<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use \yii\helpers\Url;
use yii\bootstrap5\Html;
use app\models\User;
use app\widgets\ActiveMenuWidget;

AppAsset::register($this);

$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => '@web/favicon.ico']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <base href="/">
    <meta charset="utf-8">
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php $this->beginBody() ?>
    <header class="page-header">
        <nav class="main-nav">
            <a href='<?= Url::to('tasks') ?>' class="header-logo">
                <img class="logo-image" src="<?= Yii::$app->request->baseUrl; ?>/img/logotype.png" width=227 height=60 alt="taskforce">
            </a>
            <?php if (!Yii::$app->user->isGuest) : ?>
                <div class="nav-wrapper">
                    <ul class="nav-list">
                        <li class="list-item <?= ActiveMenuWidget::widget(['links' => [Url::to('/tasks')], 'active' =>  'list-item--active']) ?>">
                            <a href="<?= Url::to('tasks') ?>" class="link link--nav">Новое</a>
                        </li>
                        <li class="list-item <?= ActiveMenuWidget::widget(['links' => [Url::to('/my-tasks/index'), Url::to('/my-tasks/work'), Url::to('/my-tasks/closed')], 'active' =>  'list-item--active']) ?>">
                            <a href="<?= Url::to('my-tasks/index') ?>" class="link link--nav">Мои задания</a>
                        </li>
                        <?php if (Yii::$app->user->can('customer')) : ?>
                            <li class="list-item <?= ActiveMenuWidget::widget(['links' => [Url::to('/tasks/create')], 'active' =>  'list-item--active']) ?>">
                                <a href="<?= Url::to('tasks/create') ?>" class="link link--nav">Создать задание</a>
                            </li>
                        <?php endif; ?>
                        <li class="list-item <?= ActiveMenuWidget::widget(['links' => [Url::to('/user/edit'), Url::to('/user/edit/' . Yii::$app->user->identity->id), Url::to('/user/set/' . Yii::$app->user->identity->id)], 'active' =>  'list-item--active']) ?>">
                            <a href="<?= Url::to('user/edit') ?>" class="link link--nav">Настройки</a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </nav>
        <?php if (!Yii::$app->user->isGuest) : ?>
            <div class="user-block">
                <a href="<?= Url::to('user/view/' . Yii::$app->user->identity->id) ?>">
                    <img class="user-photo" src="<?= file_exists(Yii::$app->user->identity->avatar) ? Html::encode(User::USER_AVATAR_UPLOAD_PATH . Yii::$app->user->identity->avatar) : Html::encode('img/man-glasses.png') ?>" width="55" height="55" alt="Аватар">
                </a>
                <div class="user-menu">
                    <p class="user-name"><?= Html::encode(User::findOne(['user_id' => (Yii::$app->user->id)])->name) ?></p>
                    <div class="popup-head">
                        <ul class="popup-menu">
                            <li class="menu-item">
                                <a href="<?= Url::to('user/edit') ?>" class="link">Настройки</a>
                            </li>
                            <li class="menu-item">
                                <a href="#" class="link">Связаться с нами</a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= Url::to('user/logout') ?>" class="link">Выход из системы</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </header>
    <div class="container">
        <?= $content ?>
    </div>
    <script src="js/main.js"></script>
    <script src="js/starRating.js"></script>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?= Html::encode(Yii::$app->params['geocoderKey']) ?>&lang=ru_RU" type="text/javascript"></script>
    <script src="js/yandexMap.js" type="text/javascript"></script>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>