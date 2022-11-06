<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\Category;
use app\models\UserCategory;
use app\models\User;
use app\models\exceptions\FileExistException;
use TaskForce\exceptions\DataSaveException;

class EditProfileForm extends Model
{
    public $name;
    public $email;
    public $birthday;
    public $phone;
    public $telegram;
    public $personalInformation;
    public $categories;
    public $avatar;

    public function rules(): array
    {
        return [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['personalInformation'], 'string'],
            [['phone'], 'string', 'length' => [User::PHONE_LENGTH, User::PHONE_LENGTH]],
            [['telegram'], 'string', 'length' => [0, User::TELEGRAM_LENGTH]],
            [['birthday'], 'date', 'format' => 'php:Y-m-d'],
            [['categories'], 'each', 'rule' => ['exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['categories' => 'category_id']]],
            [['avatar'], 'file', 'checkExtensionByMimeType' => true, 'extensions' => 'jpg, png', 'wrongExtension' => 'Только форматы jpg и png'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Ваше имя',
            'email' => 'Email',
            'birthday' => 'День рождения',
            'phone' => 'Номер телефона',
            'telegram' => 'Telegram',
            'personalInformation' => 'Информация о себе',
            'categories' => 'Выбор специализации',
            'avatar' => 'Сменить аватар'
        ];
    }

    public function getUser()
    {
        return User::find()
            ->with('categories')
            ->where(['user_id' => Yii::$app->user->id])
            ->one();
    }

    public function autocompleteForm($form, $user): void
    {
        $form->avatar = Yii::$app->user->identity->avatar;
        $form->name = Yii::$app->user->identity->name;
        $form->email = Yii::$app->user->identity->email;
        $form->phone = Yii::$app->user->identity->phone;
        $form->telegram = Yii::$app->user->identity->telegram;
        $form->personalInformation = Yii::$app->user->identity->personal_information;
        $form->birthday = Yii::$app->user->identity->birthday;
        $form->categories = $user->categories;
    }

    public function takeUser($user): void
    {
        if (!$this->uploadAvatar($user) && $this->avatar) {
            throw new FileExistException('Загрузить файл не удалось');
        }

        $user->email = $this->email;
        $user->name = $this->name;
        $user->birthday = $this->birthday;

        if ($this->phone === '') {
            $user->phone = null;
        } else {
            $user->phone = $this->phone;
        }

        if ($this->phone === '') {
            $user->telegram = null;
        } else {
            $user->telegram = $this->telegram;
        }

        $user->personal_information = $this->personalInformation;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!empty($this->categories)) {
                UserCategory::deleteAllUserCategories(Yii::$app->user->id);

                foreach ($this->categories as $userCategory) {
                    $newCategory = new UserCategory();
                    $newCategory->user_id = Yii::$app->user->id;
                    $newCategory->category_id = $userCategory;
                    $newCategory->save();
                }
            }
            if (!$user->save()) {
                throw new DataSaveException('Не удалось сохранить данные пользователя');
            }
            $transaction->commit();
        } catch (DataSaveException $exception) {
            $transaction->rollback();
            throw new DataSaveException($exception->getMessage());
        }
    }

    public function uploadAvatar($user): bool
    {
        if ($this->validate() && $this->avatar) {
            // Уникальное имя файла в БД 
            $addedAvatarName = md5(microtime(true)) . '.' . $this->avatar->extension;
            $user->avatar = $addedAvatarName;

            if (!$this->avatar->saveAs('@webroot/' . User::USER_AVATAR_UPLOAD_PATH . $addedAvatarName)) {
                throw new DataSaveException('Ошибка загрузки аватара');
            }
            return true;
        }
        return false;
    }
}
