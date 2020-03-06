<?php

namespace wdmg\turbo;

/**
 * Yii2 RSS-feeds manager
 *
 * @category        Module
 * @version         1.0.0
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-turbo
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * RSS-feed module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\turbo\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "list/index";

    /**
     * @var string, the name of module
     */
    public $name = "Yandex.Turbo";

    /**
     * @var string, the description of module
     */
    public $description = "Turbo-pages generator";

    /**
     * @var array list of supported models for displaying a Turbo-pages feed
     */
    public $supportModels = [
        'pages' => 'wdmg\pages\models\Pages',
        'news' => 'wdmg\news\models\News',
    ];

    /**
     * @var int cache lifetime, `0` - for not use cache
     */
    public $cacheExpire = 3600;

    /**
     * @var array default channel options
     */
    public $channelOptions = [];

    /**
     * @var string default route to render Turbo-pages feed (use "/" - for root)
     */
    public $turboRoute = "/turbo";

    /**
     * @var string the module version
     */
    private $version = "1.0.0";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        // Process and normalize route for frontend
        $this->turboRoute = self::normalizeRoute($this->turboRoute);

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'icon' => 'fa fa-fw fa-rocket',
            'url' => [$this->routePrefix . '/'. $this->id],
            'active' => (in_array(\Yii::$app->controller->module->id, [$this->id]) &&  Yii::$app->controller->id == 'list'),
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        if (isset(Yii::$app->params["turbo.supportModels"]))
            $this->supportModels = Yii::$app->params["turbo.supportModels"];

        if (isset(Yii::$app->params["turbo.cacheExpire"]))
            $this->cacheExpire = Yii::$app->params["turbo.cacheExpire"];

        if (isset(Yii::$app->params["turbo.channelOptions"]))
            $this->channelOptions = Yii::$app->params["turbo.channelOptions"];

        if (isset(Yii::$app->params["turbo.turboRoute"]))
            $this->turboRoute = Yii::$app->params["turbo.turboRoute"];

        if (!isset($this->supportModels))
            throw new InvalidConfigException("Required module property `supportModels` isn't set.");

        if (!isset($this->cacheExpire))
            throw new InvalidConfigException("Required module property `cacheExpire` isn't set.");

        if (!isset($this->channelOptions))
            throw new InvalidConfigException("Required module property `channelOptions` isn't set.");

        if (!isset($this->turboRoute))
            throw new InvalidConfigException("Required module property `turboRoute` isn't set.");

        if (!is_array($this->supportModels))
            throw new InvalidConfigException("Module property `supportModels` must be array.");

        if (!is_array($this->channelOptions))
            throw new InvalidConfigException("Module property `channelOptions` must be array.");

        if (!is_integer($this->cacheExpire))
            throw new InvalidConfigException("Module property `cacheExpire` must be integer.");

        if (!is_string($this->turboRoute))
            throw new InvalidConfigException("Module property `turboRoute` must be a string.");

        // Add route to pass turbo-pages in frontend
        $turboRoute = $this->turboRoute;
        if (empty($turboRoute) || $turboRoute == "/") {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => '/turbo',
                    'route' => 'admin/turbo/default',
                    'suffix' => '.xml'
                ],
                '/turbo.xml' => 'admin/turbo/default'
            ], true);
        } else if (is_string($turboRoute)) {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => $turboRoute . '/feed',
                    'route' => 'admin/turbo/default',
                    'suffix' => '.xml'
                ],
                $turboRoute . '/feed.xml' => 'admin/turbo/default'
            ], true);
        }

        // Attach to events of create/change/remove of models for the subsequent clearing cache of feeds
        if (!($app instanceof \yii\console\Application)) {
            if ($cache = $app->getCache()) {
                if (is_array($models = $this->supportModels)) {
                    foreach ($models as $name => $class) {
                        if (class_exists($class)) {
                            $model = new $class();
                            \yii\base\Event::on($class, $model::EVENT_AFTER_INSERT, function ($event) use ($cache) {
                                $cache->delete(md5('yandex-turbo'));
                            });
                            \yii\base\Event::on($class, $model::EVENT_AFTER_UPDATE, function ($event) use ($cache) {
                                $cache->delete(md5('yandex-turbo'));
                            });
                            \yii\base\Event::on($class, $model::EVENT_AFTER_DELETE, function ($event) use ($cache) {
                                $cache->delete(md5('yandex-turbo'));
                            });
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate current RSS-feed URL
     *
     * @return null|string
     */
    public function getFeedURL() {
        $url = null;
        $turboRoute = $this->turboRoute;
        if (empty($turboRoute) || $turboRoute == "/") {
            $url = Url::to('/turbo.xml', true);
        } else {
            $url = Url::to($turboRoute . '/feed.xml', true);
        }
        return $url;
    }


    /**
     * Get items for building a Yandex turbo-pages
     *
     * @return array
     */
    public function getTurboItems() {
        $items = [];
        if (is_array($models = $this->supportModels)) {
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