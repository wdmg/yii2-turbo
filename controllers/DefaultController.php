<?php

namespace wdmg\turbo\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * DefaultController implements actions
 */
class DefaultController extends Controller
{

    public $defaultAction = 'turbo';

    /**
     * Displays the Yandex turbo-pages for frontend.
     *
     * @return string
     */
    public function actionTurbo() {

        $module = $this->module;
        if ($module->cacheExpire !== 0 && ($cache = Yii::$app->getCache())) {
            $data = $cache->getOrSet(md5('yandex-turbo'), function () use ($module) {
                return [
                    'items' => $module->getTurboItems(),
                    'builded_at' => date('r')
                ];
            }, intval($module->cacheExpire));
        } else {
            $data = [
                'items' => $module->getTurboItems(),
                'builded_at' => date('r')
            ];
        }

        $channel = [];
        if (is_array($module->channelOptions))
            $channel = $module->channelOptions;

        if (!isset($channel['title']))
            $channel['title'] = Yii::$app->name;

        if (!isset($channel['link']))
            $channel['link'] = Url::base(true);

        if (!isset($channel['language']))
            $channel['language'] = Yii::$app->language;

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->getResponse()->getHeaders()->set('Content-Type', 'text/xml; charset=UTF-8');
        return $this->renderPartial('turbo', [
            'channel' => $channel,
            'items' => $data['items']
        ]);
    }

}
