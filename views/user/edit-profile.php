<?php

use app\models\User;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\widgets\UserProfileMenuWidget;

?>

<main class="main-content main-content--left container">
    <div class="left-menu left-menu--edit">
        <?= UserProfileMenuWidget::widget() ?>
    </div>
    <div class="my-profile-form">
        <?php
        $form = ActiveForm::begin(['id' => 'options-form']) ?>
        <h3 class="head-main head-regular">Мой профиль</h3>
        <div class="photo-editing">
            <div>
                <p class="form-label">Аватар</p>
                <img class="avatar-preview" src="<?= file_exists(Yii::$app->user->identity->avatar) ? Html::encode(User::USER_AVATAR_UPLOAD_PATH . Yii::$app->user->identity->avatar) : Html::encode('img/man-glasses.png') ?>" width="83" height="83">
            </div>
            <?= $form->field($editProfileForm, 'avatar')->fileInput(['hidden' => ''])->label('Сменить аватар', ['class' => 'button button--black']) ?>
        </div>
        <?= $form->field($editProfileForm, 'name')->textInput(['labelOptions' => ['class' => 'control-label']]) ?>
        <div class="half-wrapper">
            <?= $form->field($editProfileForm, 'email')->input('email', ['labelOptions' => ['class' => 'control-label']]) ?>
            <?= $form->field($editProfileForm, 'birthday')->input('date', ['format' => 'php:dd.mm.YYYY', 'labelOptions' => ['class' => 'control-label']]) ?>
        </div>
        <div class="half-wrapper">
            <?= $form->field($editProfileForm, 'phone')->input('tel', ['labelOptions' => ['class' => 'control-label']]) ?>
            <?= $form->field($editProfileForm, 'telegram')->textInput(['labelOptions' => ['class' => 'control-label']]) ?>
        </div>
        <?php if (Yii::$app->user->can(User::ROLE_EXECUTOR)) : ?>
            <?= $form->field($editProfileForm, 'personalInformation')->textarea(['labelOptions' => ['class' => 'control-label']]) ?>
            <div class="form-group">
                <?= $form->field($editProfileForm, 'categories')->checkboxList($categories, [
                    'class' => 'checkbox-profile',
                    'itemOptions' => [
                        'labelOptions' => [
                            'class' => 'control-label',
                        ],
                    ],
                ]) ?>
            </div>
        <?php endif; ?>
        <input type="submit" class="button button--blue" value="Сохранить">
        <?php ActiveForm::end() ?>
    </div>
</main>