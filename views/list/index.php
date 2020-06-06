<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $module->name;
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $module->version ?>]</small>
    </h1>
    <?php if ($feed_url = $module->getFeedURL()) : ?>
        <p><?= Yii::t('app/modules/turbo', 'Turbo-pages feed of the current site is available at: {url}',
                ['url' => Html::a($feed_url, $feed_url, ['target' => '_blank', 'data-pjax' => 0])]
            ) ?></p>
    <?php endif; ?>
</div>
<div class="turbo-index">
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => null,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'url',
            'name',
            'title',
            'image',
            'description',
            /*'content',*/
            'updated_at',
            'status'
        ],
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => '',
            'nextPageCssClass' => '',
            'firstPageCssClass' => 'previous',
            'lastPageCssClass' => 'next',
            'firstPageLabel' => Yii::t('app/modules/turbo', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/turbo', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/turbo', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/turbo', 'Next page &rarr;')
        ],
    ]); ?>
    <hr/>
    <div class="btn-group">
        <?= Html::a(Yii::t('app/modules/turbo', 'Clear cache'), ['list/clear'], ['class' => 'btn btn-info']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>
