[![Yii2](https://img.shields.io/badge/required-Yii2_v2.0.35-blue.svg)](https://packagist.org/packages/yiisoft/yii2)
[![Downloads](https://img.shields.io/packagist/dt/wdmg/yii2-turbo.svg)](https://packagist.org/packages/wdmg/yii2-turbo)
[![Packagist Version](https://img.shields.io/packagist/v/wdmg/yii2-turbo.svg)](https://packagist.org/packages/wdmg/yii2-turbo)
![Progress](https://img.shields.io/badge/progress-ready_to_use-green.svg)
[![GitHub license](https://img.shields.io/github/license/wdmg/yii2-turbo.svg)](https://github.com/wdmg/yii2-turbo/blob/master/LICENSE)

<img src="./docs/images/yii2-turbo.png" width="100%" alt="Yii2 Yandex.Turbo" />

# Yii2 Yandex.Turbo
Yandex.Turbo pages generator.

This module is an integral part of the [Butterfly.СMS](https://butterflycms.com/) content management system, but can also be used as an standalone extension.

Copyrights (c) 2019-2023 [W.D.M.Group, Ukraine](https://wdmg.com.ua/)

# Requirements 
* PHP 5.6 or higher
* Yii2 v.2.0.35 and newest
* [Yii2 Base](https://github.com/wdmg/yii2-base) module (required)
* [Yii2 Options](https://github.com/wdmg/yii2-options) module (optionality)
* [Yii2 Pages](https://github.com/wdmg/yii2-pages) module (support)
* [Yii2 News](https://github.com/wdmg/yii2-news) module (support)

# Installation
To install the module, run the following command in the console:

`$ composer require "wdmg/yii2-turbo"`

After configure db connection, run the following command in the console:

`$ php yii turbo/init`

And select the operation you want to perform:
  1) Apply all module migrations
  2) Revert all module migrations
  3) Flush Yandex turbo-pages cache

# Migrations
In any case, you can execute the migration and create the initial data, run the following command in the console:

`$ php yii migrate --migrationPath=@vendor/wdmg/yii2-turbo/migrations`

# Configure
To add a module to the project, add the following data in your configuration file:

    'modules' => [
        ...
        'turbo' => [
            'class' => 'wdmg\turbo\Module',
            'supportModels'  => [ // list of supported models for displaying a Turbo-pages feed
                'news' => 'wdmg\news\models\News',
            ],
            'cacheExpire' => 3600, // cache lifetime, `0` - for not use cache
            'channelOptions' => [], // default channel options
            'turboRoute' => '/' // default route to render Turbo-pages feed (use "/" - for root)
        ],
        ...
    ],

# Routing
Use the `Module::dashboardNavItems()` method of the module to generate a navigation items list for NavBar, like this:

    <?php
        echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
            'label' => 'Modules',
            'items' => [
                Yii::$app->getModule('turbo')->dashboardNavItems(),
                ...
            ]
        ]);
    ?>

# Status and version [ready to use]
* v.1.1.0 - Update copyrights, fix menu dashboard
* v.1.0.3 - Update dependencies, README.md
* v.1.0.2 - Added support for Blog module, fixed models items retrieved