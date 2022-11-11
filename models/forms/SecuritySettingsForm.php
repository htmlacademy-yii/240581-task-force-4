<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\forms\RegistrationForm;

class SecuritySettingsForm extends Model
{

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordRepeat = '';
    public bool $showContacts = true;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['currentPassword'], 'required'],
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'string', 'min' => RegistrationForm::MIN_LENGTH_PASSWORD, 'max' => RegistrationForm::MAX_LENGTH_PASSWORD],
            [['currentPassword'], 'validatePassword'],
            [['newPasswordRepeat'], 'compare', 'compareAttribute' => 'newPassword', 'skipOnEmpty' => false, 'message' => 'Пароли не совпадают'],
            [['showContacts'], 'boolean'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => 'Старый пароль',
            'newPassword' => 'Новый пароль',
            'newPasswordRepeat' => 'Повтор нового пароля',
            'showContacts' => 'Показывать контактные данные только заказчику',
        ];
    }

    /**
     * Метод валидации пароля при изменении настроек безопасности
     * 
     * @param string $attribute - строка из поля 'password' формы настроек
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['user_id' => Yii::$app->user->id]);;
            if (!$user || !$user->validatePassword($this->currentPassword)) {
                $this->addError($attribute, 'Неверный пароль');
            }
        }
    }

    /**
     * Метод сохранения изменений в настройках безопасности
     * 
     * @return bool
     */
    public function changeSettings(): bool
    {
        $currentUser = Yii::$app->user->identity;
        $user = User::findOne($currentUser->id);

        if ($this->newPassword) {
            $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->newPassword);
        }

        $user->show_contacts = $this->showContacts ? User::HIDE_CONTACTS : User::SHOW_CONTACTS;

        return $user->save();
    }
}
