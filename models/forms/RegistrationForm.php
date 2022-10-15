<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\City;
use app\models\User;

class RegistrationForm extends Model
{
    public string $name = '';
    public string $email = '';
    public string $city = '';
    public string $password = '';
    public string $passwordRepeat = '';
    public bool $isExecutor = false;

    public function rules(): array
    {
        return [
            [['name', 'email', 'password', 'passwordRepeat', 'isExecutor'], 'required'],

            [['city'], 'required', 'when' => function ($model) {
                return $this->isExecutor === true;
            }],
            [['name', 'email'], 'string', 'max' => 255],
            [['password', 'passwordRepeat'], 'string', 'min' => 6, 'max' => 64],
            [['passwordRepeat'], 'compare', 'compareAttribute' => 'password'],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => ['email' => 'email'], 'message' => 'Пользователь с таким e-mail уже существует'],
            [['city'], 'exist', 'when' => function ($model) {
                return $this->isExecutor === true;
            }, 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city' => 'city_id']],
            [['isExecutor'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Ваше имя',
            'email' => 'Email',
            'password' => 'Пароль',
            'passwordRepeat' => 'Повтор пароля',
            'isExecutor' => 'Я собираюсь откликаться на заказы',
            'city' => 'Ваш город',
        ];
    }

    public function createUser()
    {
        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->user_role = User::ROLE_CUCTOMER;
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);

        if ($this-> isExecutor) {
            $user->user_role = User::ROLE_EXECUTOR;
        }

        $user->city_id = $this->city;
        $user->save();
    }
}