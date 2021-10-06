<?php

namespace app\controllers\api;

use Yii;
use yii\web\ForbiddenHttpException;

trait ChecksTokenAccess
{
    protected function requiredClaims()
    {
        return [];
    }



    public function checkAccess($action, $model = null, $params = [])
    {
        if (empty($requiredClaims = $this->requiredClaims())) {
            return true;
        }

        if ($token = $this->getToken()) {
            Yii::$app->jwt->setToken($token);

            foreach ($requiredClaims as $name => $value) {
                $claimData = Yii::$app->jwt->getClaim($name);

                if ($claimData !== $value) {
                    throw new ForbiddenHttpException();
                }
            }

            return true;
        }

        throw new ForbiddenHttpException();
    }

    protected function getToken()
    {
        $header = Yii::$app->getRequest()->getHeaders()->get('Authorization');
        $pattern = '/^Bearer\s+(.*?)$/';

        if (preg_match($pattern, $header, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->checkAccess($action);

        return true;
    }
}
