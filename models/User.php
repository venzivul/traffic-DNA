<?php

namespace app\models;
use Yii;
use yii\base\Object;

class User extends Object implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users;

    public function init()
    {
        parent::init();
        /**
         *  If file with users dosnt exist create some test users
         * */
        if (!file_exists(Yii::$app->params['dataPath'])){
            $users = [
                '1'=>[
                    'id'=>1,
                    'username'=>'test',
                    'password'=>Yii::$app->security->generatePasswordHash('zaq123'),
                    'authKey' => 'test1key',
                    'accessToken' => '1-token',

                ],
                '2'=>[
                    'id'=>2,
                    'username'=>'test2',
                    'password'=>Yii::$app->security->generatePasswordHash('zaq123'),
                    'authKey' => 'test2key',
                    'accessToken' => '2-token',

                ],
            ];
            file_put_contents(Yii::$app->params['dataPath'], serialize($users));
        }
        self::$users = unserialize(file_get_contents(Yii::$app->params['dataPath']));
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        self::init();
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        self::init();
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        self::init();
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        self::init();
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        self::init();
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        self::init();
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        self::init();
        return Yii::$app->security->validatePassword($password, $this->password);
    }
}
