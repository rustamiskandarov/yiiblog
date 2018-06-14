<?php
/**
 * Created by PhpStorm.
 * User: Rust
 * Date: 13.06.2018
 * Time: 14:40
 */

namespace app\controllers;


use app\models\LoginForm;
use app\models\SignupForm;
use app\models\User;
use Yii;
use yii\web\Controller;

class AuthController extends Controller
{
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            //die("Пользователь авторизован");
            return $this->goHome();
        }
        //die("Пользователь гость");

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            //$usr = Yii::$app->user->isGuest;
            //var_dump(Yii::$app->user->identity->name);die;
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */

    public function actionSignup()
    {
        $model = new SignupForm();

        if(Yii::$app->request->isPost)
        {
            $model->load(Yii::$app->request->post());
            if($model->signup())
            {
                return $this->redirect(['auth/login']);
            }
        }

        return $this->render('signup', ['model'=>$model]);
    }
}