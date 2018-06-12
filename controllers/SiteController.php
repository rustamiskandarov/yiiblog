<?php

namespace app\controllers;

use app\models\Article;
use app\models\Category;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
        $data = Article::getAll();
        $popular = Article::getPopular();
        $last = Article::getLast();
        $categoryes = Category::getAll();

        return $this->render('index',[
            'models' => $data['articles'],
            'pages' => $data['pagination'],
            'popular' => $popular,
            'last' => $last,
            'categoryes' => $categoryes,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */

    public function actionView($id)
    {
        $data = Article::getAll();
        $article = Article::findOne($id);
        $popular = Article::getPopular();
        $last = Article::getLast();
        $categoryes = Category::getAll();

        return $this->render('single', [
            'models' => $data['articles'],
            'pages' => $data['pagination'],
            'article' => $article,
            'popular' => $popular,
            'last' => $last,
            'categoryes' => $categoryes,
        ]);
    }

    public function actionCategory($id)
    {
        $query = Article::find()->where(['category_id' => $id]);
        $count = $query->count();
        $pages = new Pagination(['totalCount' => $count, 'pageSize'=>9]);

        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $popular = Article::getPopular();
        $last = Article::getLast();
        $categoryes = Category::getAll();
        return $this->render('category', [
            'models' => $models,
            'pages' => $pages,
            'popular' => $popular,
            'last' => $last,
            'categoryes' => $categoryes,
        ]);
    }



    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
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
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
