<?php

use app\models\Category;
use app\models\forms\TaskAddForm;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var TaskAddForm $taskAddForm */
?>
<main class="main-content main-content--center container">
    <div class="add-task-form regular-form">

        <?php $form = ActiveForm::begin([
            'id' => 'task-create-form',
            'method' => 'post',
            'options' => ['enctype' => 'multipart/form-data']
        ]); ?>
        <h3 class="head-main head-main">Публикация нового задания</h3>
        <?= $form->field($taskAddForm, 'taskName', ['options' => ['class' => 'form-group control-label']]); ?>
        <?= $form->field($taskAddForm, 'taskEssence', ['options' => ['class' => 'form-group control-label']]); ?>
        <?= $form->field($taskAddForm, 'taskDetails', ['options' => ['class' => 'form-group control-label']])->textarea(); ?>
        <?= $form->field($taskAddForm, 'category', ['options' => ['class' => 'head-card']])->dropDownList(ArrayHelper::map(
            Category::find()->all(),
            'category_id',
            'category_name'
        ), ['class' => 'form-group control-label']); ?>
        <div class="form-group">
            <label class="control-label" for="location">Локация</label>
            <input class="location-icon" id="location" type="text">
            <span class="help-block">Error description is here</span>
        </div>

        <div class="half-wrapper">
            <?= $form->field($taskAddForm, 'taskBudget', ['options' => ['class' => 'form-group control-label']])->input('taskBudget', ['class' => 'budget-icon']); ?>
            <?= $form->field($taskAddForm, 'taskDeadline', ['options' => ['class' => 'form-group control-label']])->input('date'); ?>
        </div>

        <?= $form->field($taskAddForm, 'files[]')->fileInput(['multiple' => true, 'class' => 'new-file form-label', 'placeholder' => 'Добавить новый файл']); ?>
        <input type="submit" class="button button--blue" value="Опубликовать">
        <?php ActiveForm::end(); ?>
    </div>
</main>