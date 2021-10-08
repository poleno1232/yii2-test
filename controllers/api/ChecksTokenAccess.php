<?php

namespace app\controllers\api;

use Yii;
use yii\web\ForbiddenHttpException;

trait ChecksTokenAccess
{
    /**
     * Returns an array, where keys represent action by its ID and required claims for them
     *
     * @return array
     */
    abstract protected function requiredClaims();

    public function checkAccess($action, $model = null, $params = [])
    {
        if (empty($requiredClaims = $this->requiredClaims())) {
            return true;
        }

        if (!$requiredClaims[$action->actionMethod]) {
            return true;
        }

        if ($token = $this->getToken()) {
            Yii::$app->jwt->setToken($token);

            foreach ($requiredClaims[$action->actionMethod] as $name => $value) {
                $claimData = Yii::$app->jwt->getClaim($name);

                if ($claimData !== $value) {
                    throw new ForbiddenHttpException();
                }
            }

            return true;
        }

        throw new ForbiddenHttpException();
    }

    /**
     * Returns string representation of token
     *
     * @return string|null
     */
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
