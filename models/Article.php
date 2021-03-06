<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $date
 * @property string $image
 * @property int $viewed
 * @property int $user_id
 * @property int $status
 * @property int $category_id
 *
 * @property ArticleTag[] $articleTags
 * @property Comment[] $comments
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'description', 'content'], 'string'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['date'], 'default', 'value' => date('Y-m-d')],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @param $filename
     * @return bool
     * Сохранение название файла в БД
     */
    public function saveImage($filename)
    {
        $this->image = $filename;
        return $this->save(false);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'date' => 'Date',
            'image' => 'Image',
            'viewed' => 'Viewed',
            'user_id' => 'User ID',
            'status' => 'Status',
            'category_id' => 'Category ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArticleTags()
    {
        return $this->hasMany(ArticleTag::className(), ['article_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['article_id' => 'id']);
    }

    /**
     * удаление картинки из папки uploads когда удаляеться пост
     */
    public function deleteImage()
    {
        $imageUpploadModel = new ImageUppload();
        $imageUpploadModel->deleteCurrentImage($this->image);
    }

    /**
     * @return bool
     *автоматически запускаеться перед тем как статься удалиться и вызовит метод удаления файла
     */
    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete();
    }

    public function getImage()
    {
        return ($this->image)? '/uploads/' . $this->image : '/noimage.gif';
    }



    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

//    public function getTitleCategory()
//    {
//        $category = $this->getCategory();
//        echo ('<pre>');
//        var_dump($category);
//        echo ('</pre>');die;
//    }
    /**
     * @param $category_id
     * @return bool
     * description присвоение вовой категории и сохранение в БД
     */
    public function saveCategory($category_id)
    {
        $category = Category::findOne($category_id);
        if($category != null){
            $this->link('category', $category);
            return true;
        }
    }

    /**
     * @param $tags
     * description сохранение новых тегов в таблицу БД
     */
    public function saveTags($tags)
    {
        if(is_array($tags)) //если массив то
        {
            $this->cleanCurrentTags();  //удаляем старые значения тегов

            foreach ($tags as $tag_id){ //проходим по циклу
                $tag = Tag::findOne($tag_id);   //присваеваем тег по айдишнику из списка всех тегов
                $this->link('tags', $tag);  //при помощи link создаем связь согласно getTags()
            }
        }
    }

    /**
     * @return int
     * description удаление всех текущих тегов в статье
     */
    public function cleanCurrentTags()
    {
        return ArticleTag::deleteAll(['article_id'=>$this->id]);
    }

    /**
     * @return $this
     * description связь многие к многим с применением третей таблицы
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('article_tag', ['article_id' => 'id']);
    }

    /**
     * @return string
     */

    public function getDate()
    {
        return Yii::$app->formatter->asDate($this->date);
    }

    /**
     * @return array
     */
    public function getSelectedTags()
    {
        $selectTags = $this->getTags()->select('id')->asArray()->all();
        return ArrayHelper::getColumn($selectTags, 'id');
    }

    /**
     * @param int $pageSize
     * @return array
     */
    public static function getAll($pageSize = 6){
        $query = Article::find();
        $count = $query->count();
        $pages = new Pagination(['totalCount' => $count, 'pageSize'=>$pageSize]);

        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $data['articles'] = $models;
        $data['pagination'] = $pages;

        return $data;
    }

    /**
     * @param int $limPop
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPopular($limPop = 3){
        return Article::find()->orderBy('viewed desc')->limit($limPop)->all();
    }

    /**
     * @param int $limLast
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getLast($limLast = 4)
    {
        return Article::find()->orderBy('date desc')->limit($limLast)->all();
    }

}
