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

    /**
     * Get items for building a Yandex turbo-pages
     *
     * @return array
     */
    private function getTurboItems() {
        $items = [];
        if (is_array($models = $this->module->supportModels)) {
            foreach ($models as $name => $class) {
                if (class_exists($class)) {
                    $append = [];
                    $model = new $class();
                    foreach ($model->getAll(['in_turbo' => true]) as $item) {
                        $append[] = [
                            'url' => (isset($item->url)) ? $item->url : null,
                            'name' => (isset($item->name)) ? $item->name : null,
                            'title' => (isset($item->title)) ? $item->title : null,
                            'image' => (isset($item->image)) ? $model->getImagePath(true) . '/' . $item->image : null,
                            'description' => (isset($item->excerpt)) ? $item->excerpt : ((isset($item->description)) ? $item->description : null),
                            'content' => (isset($item->content)) ? $item->content : null,
                            'updated_at' => (isset($item->updated_at)) ? $item->updated_at : null,
                            'status' => (isset($item->status)) ? (($item->status) ? true : false) : false
                        ];
                    };
                    $items = ArrayHelper::merge($items, $append);
                }
            }
        }

        return $items;
    }
}
