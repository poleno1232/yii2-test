<?php

namespace app\models;

use Exception;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user This property is read-only.
 *
 */
class ApiLoginData extends Model
{
    public $username;
    public $password;
    public $rememberMe;

    private $user = null;

    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            ['rememberMe', 'default', 'value' => false],
        ];
    }

    public function getUser()
    {
        if ($this->user == false) {
            $this->user = User::findByUsername($this->username);
        }

        return $this->user;
    }

    public function login()
    {
        if ($this->validate()) {
            Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);

            return $this->user = Yii::$app->user;
        }

        return null;
    }
}
