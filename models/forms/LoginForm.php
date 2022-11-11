<?php

namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $email;
    public $password;

    private $_user;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Метод валидации пароля при входе пользователя
     * 
     * @param string $attribute - строка из поля 'password' формы входа
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неправильный email или пароль');
            }
        }
    }

    /**
     * Метод получения данных пользователя по email
     * 
     * @return User|null $user - объект класса User
     */
    public function getUser(): User
    {
        if (null === $this->_user) {
            $this->_user = User::findOne(['email' => $this->email]);
        }
        return $this->_user;
    }
}
