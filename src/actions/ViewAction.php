<?php
/**
 * @copyright Copyright(c) 2016 Webtools Ltd
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
**/

namespace thamtech\scheduler\actions;

use thamtech\scheduler\models\SchedulerLog;
use Yii;
use yii\base\Action;
use thamtech\scheduler\models\SchedulerTask;

/**
 * View a task instance.
 */
class ViewAction extends Action
{
    /**
     * @var string the view file to be rendered. If not set, it will take the value of [[id]].
     * That means, if you name the action as "index" in "SchedulerController", then the view name
     * would be "index", and the corresponding view file would be "views/scheduler/index.php".
     */
    public $view;

    /**
     * Runs the action
     *
     * @return string result content
     */
    public function run($id)
    {
        $model = SchedulerTask::findOne($id);
        $request = Yii::$app->getRequest();

        if (!$model) {
            throw new \yii\web\HttpException(404, 'The requested page does not exist.');
        }

        if ($model->load($request->post())) {
            $model->save();
        }

        $logModel = new SchedulerLog();
        $logModel->scheduler_task_id = $model->id;
        $logDataProvider = $logModel->search($_GET);

        return $this->controller->render($this->view ?: $this->id, [
            'model' => $model,
            'logModel' => $logModel,
            'logDataProvider' => $logDataProvider,
        ]);
    }
}
