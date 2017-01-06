<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $readonly = false;
        $this->unsetBlock();

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()){
                $this->unsetBlock();
                return $this->redirect("/user/index");
            }else{
                if (!isset($_SESSION["block"])){
                    $_SESSION["block"] = 0;
                }
                if (isset($_SESSION["block"])){
                    $_SESSION["block"]++;
                    if ($_SESSION["block"]==3)
                    $_SESSION["blockTime"]= time()+(60*5);
                }
            }
        }
        if (isset($_SESSION["blockTime"]))
            $readonly = true;

        return $this->render('login', [
            'model' => $model,
            'readonly' => $readonly
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    private function unsetBlock(){
        if (isset($_SESSION["blockTime"])){
            if ($_SESSION["blockTime"]-time()<=0){
                unset($_SESSION["block"]);
                unset($_SESSION["blockTime"]);
            }
        }
    }
}
