<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use app\models\ChangePassword;
use yii\base\DynamicModel;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (isset($_GET['notif']))
            $notif = $_GET['notif'];
        else
            $notif = '';

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notif' => $notif,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if (isset($_GET['notif']))
            $notif = $_GET['notif'];
        else
            $notif = '';
        return $this->render('view', [
            'model' => $this->findModel($id),
            'notif' => $notif,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    // public function actionCreate()
    // {
    //     $model = new User();

    //     if ($model->load(Yii::$app->request->post()) && $model->save()) {
    //         return $this->redirect(['view', 'id' => $model->id]);
    //     } else {
    //         return $this->render('create', [
    //             'model' => $model,
    //         ]);
    //     }
    // }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id, 'notif' => 'Berhasil diperbarui']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionUploadphoto($id)
    {
        $model = new DynamicModel([
            'file_id'
        ]);
        
        // behavior for upload file
        $model->attachBehavior('upload', [
            'class' => 'mdm\upload\UploadBehavior',
            'attribute' => 'file',
            'savedAttribute' => 'file_id', 
            //'uploadPath' => Yii::$app->homeUrl.'/files',
        ]);

        $model2 = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->saveUploadedFile() !== false) {
                if ($model->file_id !== NULL && $model->file_id !== '')
                {
                    Yii::$app->db->createCommand('UPDATE uploaded_file SET type = "photo" WHERE id = '.$model->file_id)->execute();
                    $model2->image = $model->file_id;
                    $model2->save();
                    return $this->redirect(['view', 'id' => $model2->id, 'notif' => 'Foto berhasil diunggah']);
                }
            }
            else return $this->redirect(['view', 'id' => $model2->id, 'notif' => 'Foto gagal diunggah, silahkan periksa ukuran foto.']);
        } else {
            return $this->render('uploadphoto', [
                'model' => $model,
                'model2' => $model2,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $notif = 'User berhasil dihapus';

        return $this->redirect(['index', 'notif' => $notif]);
    }

     public function actionBan($id)
    {
        $this->findModel($id)->ban();
        $notif = 'Status user berhasil diubah';

        return $this->redirect(['index', 'notif' => $notif]);
    }

    public function actionPromote($id)
    {
        $this->findModel($id)->promote();
        $notif = 'Role user berhasil diubah';

        return $this->redirect(['index', 'notif' => $notif]);
    }

    public function actionChangepassword($id)
    {
        $model = new ChangePassword();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->setPassword()) {
                    $notif = "Password berhasil diganti";
                    $model = new ChangePassword();
                    return $this->redirect(['view', 'id' => $id, 'notif' => $notif]);
                }
            }
               
        return $this->render('changepassword', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
