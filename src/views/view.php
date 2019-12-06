<?php
/**
 * @copyright Copyright(c) 2018 Thamtech, LLC
 * @link https://github.com/thamtech/yii2-scheduler
 * @license https://opensource.org/licenses/MIT
 *
 *
 * Task View
 *
 * @var yii\web\View $this
 * @var thamtech\scheduler\models\SchedulerTask $model
 */

use yii\helpers\Html;
use thamtech\scheduler\models\SchedulerTask;
use yii\bootstrap\Tabs;
use yii\grid\GridView;
use yii\widgets\DetailView;


$this->title = $model->__toString();
$this->params['breadcrumbs'][] = ['label' => SchedulerTask::label(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->__toString();
?>
<div class="task-view">

    <h1><?=$this->title ?></h1>

    <?php $this->beginBlock('main'); ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'display_name',
            'description',
            'schedule',
            'status',
            [
                'attribute' => 'started_at',
                'format' => 'raw',
                'value' => $model->status_id == SchedulerTask::STATUS_RUNNING ? $model->started_at : '',
            ],
            'last_run',
            'next_run',
        ],
    ]) ?>
    <?php $this->endBlock(); ?>



    <?php $this->beginBlock('logs'); ?>
    <div class="table-responsive">
        <?php \yii\widgets\Pjax::begin(['id' => 'logs']); ?>
        <?= GridView::widget([
            'layout' => '{summary}{pager}{items}{pager}',
            'dataProvider' => $logDataProvider,
            'pager' => [
                'class' => yii\widgets\LinkPager::className(),
                'firstPageLabel' => Yii::t('app', 'First'),
                'lastPageLabel' => Yii::t('app', 'Last'),
            ],
            'columns' => [
                [
                    'attribute' => 'started_at',
                    'format' => 'raw',
                    'value' => function ($m) {
                        return Html::a(Yii::$app->getFormatter()->asDatetime($m->started_at), ['view-log', 'id' => $m->id]);
                    }
                ],
                'ended_at:datetime',
                [
                    'label' => 'Duration',
                    'value' => function ($m) {
                        return $m->getDuration();
                    }
                ],
                [
                    'label' => 'Result',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($m) {
                        return Html::tag('span', $m->output, [
                            'class' => $m->error == 0 ? 'text-success glyphicon glyphicon-ok-circle' : 'text-danger glyphicon glyphicon-remove-circle'
                        ]);
                    }
                ],
            ],
        ]); ?>
        <?php \yii\widgets\Pjax::end(); ?>
    </div>
    <?php $this->endBlock(); ?>

    <?= Tabs::widget([
        'encodeLabels' => false,
        'id' => 'customer',
        'items' => [
            'overview' => [
                'label'   => Yii::t('app', 'Overview'),
                'content' => $this->blocks['main'],
                'active'  => true,
            ],
            'logs' => [
                'label' => 'Logs',
                'content' => $this->blocks['logs'],
            ],
        ]
    ]);?>
</div>
