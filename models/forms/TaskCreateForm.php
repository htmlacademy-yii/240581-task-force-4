<?php

namespace app\models\forms;

use Yii;
use app\models\helpers\GeocoderHelper;
use app\models\exceptions\DataSaveException;
use yii\base\Model;
use app\models\Category;
use app\models\File;
use app\models\Task;

class TaskCreateForm extends Model
{
    public $taskName;
    public $taskEssence;
    public $taskDetails;
    public $category;
    public $location;
    public $taskBudget;
    public $taskDeadline;
    public $files;

    private const MIN_LENGTH_TASKNAME = 3;
    private const MIN_LENGTH_TASKESSENSE = 10;
    private const MIN_LENGTH_TASKDETAILS = 10;
    private const MIN_VALUE_TASKBUDGET = 0;
    private const MAX_COUNT_FILES = 10;

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'taskName' => 'Название задания',
            'taskEssence' => 'Мне нужно',
            'taskDetails' => 'Подробности задания',
            'category' => 'Категория задания',
            'location' => 'Локация',
            'taskBudget' => 'Бюджет',
            'taskDeadline' => 'Срок исполнения',
            'files' => 'Файлы',
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['taskName', 'taskEssence', 'taskDetails', 'category'], 'required'],
            ['taskName', 'string', 'min' => self::MIN_LENGTH_TASKNAME, 'max' => Task::MAX_LENGTH_TASKNAME],
            ['taskEssence', 'string', 'min' => self::MIN_LENGTH_TASKESSENSE, 'max' => Task::MAX_LENGTH_TASKESSENSE],
            ['taskDetails', 'string', 'min' => self::MIN_LENGTH_TASKDETAILS],
            [['taskDeadline'], 'date', 'when' => function ($form) {
                return strtotime($form->taskDeadline) < time();
            }, 'message' => 'Дата выполнения задания не может быть раньше текущей даты'],
            ['category', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category' => 'category_id']],
            ['location', 'string'],
            ['taskBudget', 'integer', 'min' => self::MIN_VALUE_TASKBUDGET],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => self::MAX_COUNT_FILES],
        ];
    }

    /**
     * Метод добавления нового задания
     * 
     * @return Task $task - объект класса Task
     * @throws DataSaveException
     */
    public function addTask()
    {
        $task = new Task();

        $task->task_status = Task::STATUS_NEW;
        $task->task_name = $this->taskName;
        $task->task_essence = $this->taskEssence;
        $task->task_details = $this->taskDetails;
        $task->category_id = $this->category;

        if ($this->location) {
            $coordinates = GeocoderHelper::getCoordinates($this->location);
            $task->task_longitude = $coordinates[GeocoderHelper::GEOCODER_LONGITUDE_KEY];
            $task->task_latitude = $coordinates[GeocoderHelper::GEOCODER_LATITUDE_KEY];
            $task->city_id = Yii::$app->user->identity->city_id;
        }

        $task->task_deadline = $this->taskDeadline;
        $task->task_budget = $this->taskBudget;
        $task->customer_id = Yii::$app->user->id;

        if (!$task->save()) {
            throw new DataSaveException('Ошибка сохранения задания');
        }
        return  $task;
    }

    /**
     * Метод добавления файла(файлов) нового задания
     * 
     * @param int $taskId - id нового задания
     * @return bool
     * @throws DataSaveException
     */
    public function uploadFiles($taskId): bool
    {
        if ($this->validate() && !empty($this->files)) {
            foreach ($this->files as $file) {
                // Базовое имя будет использоваться для публичного отображения имени файла в удобочитаемом формате на усмотрение пользователя
                $addedFileBaseName = $file->baseName;
                // Уникальное имя файла в БД 
                $addedFileName = md5(microtime(true)) . '.' . $file->extension;
                $file->saveAs(File::USER_FILE_UPLOAD_PATH . $addedFileName);
                $addedFile = new File();
                $addedFile->task_id = $taskId;
                $addedFile->task_file_name = $addedFileName;
                $addedFile->task_file_base_name = $addedFileBaseName;

                if (!$addedFile->save()) {
                    throw new DataSaveException('Ошибка загрузки файла задания');
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Метод выполнения транзакции: добавление нового задания и файла (файлов) к нему в БД
     * 
     * @param object $taskAddForm - id нового задания
     * @return Response
     * @throws DataSaveException
     */
    public function makeTransaction($taskAddForm)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $task = $taskAddForm->addTask();
            $taskAddForm->uploadFiles($task->task_id);
            $transaction->commit();

            return Yii::$app->response->redirect(['tasks/view', 'id' => $task->task_id]);
        } catch (DataSaveException $e) {
            $transaction->rollback();

            throw new DataSaveException('Ошибка создания задания', $e);
        }
    }
}
