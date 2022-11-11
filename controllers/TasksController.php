<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use app\models\helpers\FormatDataHelper;
use app\models\Category;
use app\models\File;
use app\models\Respond;
use app\models\Task;
use app\models\User;
use app\models\forms\TaskCreateForm;
use app\models\forms\TaskfilterForm;
use app\models\forms\RespondForm;
use app\models\forms\CompleteForm;
use app\models\exceptions\DataSaveException;

class TasksController extends SecuredController
{
    /**
     * Страница со списком новых заданий
     *
     * @return string - код страницы со списком новых заданий
     */
    public function actionIndex(): string
    {
        $description = 'Страница оплачиваемых заданий, ищущих исполнителя';
        $keywords = 'требуется работник, надо сделать, ищу работу, помощь, фриланс, подработка';
        $this->setMeta('Новые задания', $description, $keywords);

        $tasksFilter = new TaskFilterForm();

        // ТЗ: Под заголовком «Специализации» в виде ссылок отображаются выбранные исполнителем категории. Ссылки ведут на страницу списка заданий с фильтрацией по выбранной категории.
        if (Yii::$app->request->get('category')) {
            $tasksFilter->categories = Yii::$app->request->get('category');
        }

        $tasksFilter->load(Yii::$app->request->get());
        $query =  $tasksFilter->newTasks;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $categories = Category::getAllCategories();

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'categories' => $categories,
                'model' => $tasksFilter
            ]
        );
    }

    /**
     * Действие скачивания файла задания
     *
     * @param string $path - имя файла с расширением
     * 
     */
    public function actionDownload($path)
    {
        return Yii::$app->response->sendFile(Yii::getAlias(File::USER_FILE_UPLOAD_PATH) . $path)->send();
    }

    /**
     * Страница просмотра задания
     *
     * @param int $id - id задания
     * @return string - код страницы просмотра задания
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): Response|string
    {
        $user = Yii::$app->user->identity;

        $responseForm = new RespondForm();
        $completeForm = new CompleteForm();

        $task = Task::find()
            ->with('category', 'executor', 'files', 'responds')
            ->where(['task_id' => $id])
            ->one();

        if (!$task) {
            throw new NotFoundHttpException();
        }

        if (Yii::$app->user->can(User::ROLE_CUSTOMER) && $task->customer->user_id !== $user->id) {
            return $this->goHome();
        }

        if ($task->customer->user_id === $user->id || $task->executor_id === $user->id) {
            $this->view->params['my'] = 'list-item--active';
        }

        $files = $task->files;
        $deadline = FormatDataHelper::formatData($task->task_deadline);
        $responses = $task->responds;
        $category = $task->category;
        $showAvailableAction = false;

        // Показ кнопки возможного действия только для новых и находящихся в работе заданий
        if (Task::STATUS_NEW === $task->task_status || (Task::STATUS_IN_WORK === $task->task_status)) {
            $showAvailableAction = true;
        }

        $availableAction = $task->getAvailableActions($user);

        // Исключение возможности исполнителю добавить ещё один отклик на тоже задание
        if ($availableAction && Respond::getResponse($user->id, $task->task_id) && 'actionRespond' === $availableAction->getInternalName()) {
            $showAvailableAction = false;
        }

        $this->setMeta($task->task_name, $task->task_essence, $task->task_essence);

        return $this->render(
            'view',
            [
                'user' => $user,
                'task' => $task,
                'responses' => $responses,
                'category' => $category,
                'deadline' => $deadline,
                'files' => $files,
                'availableAction' => $availableAction,
                'showAvailableAction' => $showAvailableAction,
                'responseForm' => $responseForm,
                'completeForm' => $completeForm,
            ]
        );
    }

    /**
     * Страница с формой создания задания
     * 
     * @return string - код страницы с формой создания задания
     */
    public function actionCreate(): string
    {
        $this->setMeta('Создать задание');

        if (!Yii::$app->user->can('customer')) {
            return $this->goHome();
        }

        $taskAddForm = new TaskCreateForm();

        if (Yii::$app->request->getIsPost()) {
            $taskAddForm->load(Yii::$app->request->post());
            $taskAddForm->files = UploadedFile::getInstances($taskAddForm, 'files');

            if ($taskAddForm->validate()) {
                $taskAddForm->makeTransaction($taskAddForm);
            }
        }
        return $this->render('create', ['taskAddForm' => $taskAddForm]);
    }

    /**
     * Действие по отмене задания
     * 
     * @param int $id - id задания
     * @return Response
     * @throws DataSaveException
     */
    public function actionCancel($id): Response
    {
        $this->setMeta('Отменить задание');

        $task = Task::findOne($id);

        if (Task::STATUS_NEW === $task->task_status) {
            $task->task_status = Task::STATUS_CANCELED;
            $task->date_completion = date("Y-m-d H:i:s");

            if (!$task->save()) {
                throw new DataSaveException('Ошибка отмены задания');
            }
        }
        return $this->redirect(['tasks/view', 'id' => $task->task_id]);
    }

    /**
     * Действие по отказу от отклика на задание
     *
     * @param int $respond_id - id отклика на задание
     * @return Response
     */
    public function actionReject(int $respond_id): Response
    {
        $response = Respond::processOffer($respond_id, Respond::STATUS_REJECTED);

        return $this->redirect(['tasks/view', 'id' => $response->task_id]);
    }

    /**
     * Действие по принятию отклика на задание
     *
     * @param int $respond_id - id отклика на задание
     * @return Response
     */
    public function actionAccept(int $respond_id): Response
    {
        $response = Respond::processOffer($respond_id, Respond::STATUS_ACCEPTED);

        return $this->redirect(['tasks/view', 'id' => $response->task_id]);
    }

    /**
     * Действие по отклику на задание
     *
     * @return Response
     */
    public function actionRespond(): Response
    {
        $responseForm = new RespondForm();
        if (Yii::$app->request->getIsPost()) {
            $responseForm->load(Yii::$app->request->post());

            if ($responseForm->validate() && $responseForm->createResponse()) {
                return Yii::$app->response->redirect(['tasks/view', 'id' => $responseForm->taskId]);
            }
        }
        return $this->goHome();
    }

    /**
     * Действие по отказу от задания
     *
     * @param int $id - id задания
     * @return Response
     * @throws DataSaveException
     */
    public function actionRefuse(int $id): Response
    {
        $task = Task::findOne($id);
        $task->task_status = Task::STATUS_FAILED;
        $task->date_completion = date("Y-m-d H:i:s");

        if (!$task->save()) {
            throw new DataSaveException('Ошибка отказа от задания');
        }
        return $this->redirect(['tasks/view', 'id' => $task->task_id]);
    }

    /**
     * Действие завершению задания пользователем
     *
     * @return Response
     */
    public function actionComplete(): Response
    {
        $completeForm = new CompleteForm();

        if (Yii::$app->request->getIsPost()) {
            $completeForm->load(Yii::$app->request->post());

            if ($completeForm->validate() && $completeForm->createComplete()) {
                return Yii::$app->response->redirect(['tasks/view', 'id' => $completeForm->taskId]);
            }
        }
        return $this->goHome();
    }
}
