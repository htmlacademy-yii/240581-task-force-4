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

    public const MIN_LENGTH_PASSWORD = 6;
    public const MAX_LENGTH_PASSWORD = 64;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['name', 'email', 'password', 'passwordRepeat', 'isExecutor', 'city'], 'required'],
            [['name'], 'string', 'max' => User::MAX_LENGTH_USERNAME],
            [['email'], 'string', 'max' => User::MAX_LENGTH_FILD],
            [['password', 'passwordRepeat'], 'string', 'min' => self::MIN_LENGTH_PASSWORD, 'max' => self::MAX_LENGTH_PASSWORD],
            [['passwordRepeat'], 'compare', 'compareAttribute' => 'password', 'message' => "Пароли не совпадают"],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => ['email' => 'email'], 'message' => 'Пользователь с таким e-mail уже существует'],
            [['city'], 'exist', 'skipOnError' => true, 'targetClass' => City::class, 'targetAttribute' => ['city' => 'city_id']],
            [['isExecutor'], 'boolean'],
        ];
    }

    /**
     * @inheritDoc
     */
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

    /**
     * Метод создания нового пользователя при регистрации
     * 
     * @return bool
     */
    public function createUser(): bool
    {
        $user = new User();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        $user->city_id = $this->city;

        if ($user->save()) {
            $auth = Yii::$app->authManager;
            $userRole = $auth->getRole(User::ROLE_CUSTOMER);

            if ($this->isExecutor) {
                $userRole = $auth->getRole(User::ROLE_EXECUTOR);
            }

            $auth->assign($userRole, $user->getId());
            return true;
        }
        return false;
    }
}
