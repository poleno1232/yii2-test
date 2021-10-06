<?php

namespace app\controllers\api;

use app\models\ApiLoginData;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class AuthController extends ActiveController
{
    public $modelClass = \app\models\User::class;

    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    public function actions()
    {
        return array_merge(
            parent::actions(),
            [
                //
            ]
        );
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login'],
        ];
        $behaviors += [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'login' => ['post'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return ['message' => 'Authorized'];
        }

        $model = new ApiLoginData();
        $model->setAttributes(Yii::$app->request->post());

        if ($model->login()) {
            $user = Yii::$app->user->getIdentity();
            $user->auth_token = Yii::$app->security->generateRandomString();
            $user->access_token = Yii::$app->jwt->issue()->toString();
            $user->save();

            return $user->access_token;
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(404);

        return ['message' => 'Access token is invalid'];
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        return true;
    }
}
