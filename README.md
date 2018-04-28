Yii2 Options
============

This extension provide application configuration support stored in database.

**WARNING**: Version 2.x introduces breaking changes. 
Upgrading from version 1.x is possible but requires additional actions.

Steps to migrate to version 2.x:
* Create options config for every used option item
* Include component in Yii2 config
* Rewrite commands to new syntax (or use compat class `futuretek\options\compat\Option`)    
  

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist futuretek/yii2-options "*"
```

or add

```
"futuretek/yii2-options": "*"
```

to the require section of your `composer.json` file.


Configuration
-------------

To start using this extension you have to load it in Yii config:

```php
return [
    'components' => [
        'options' => [
            'class' => 'futuretek\options\Option',
            'configFile' => __DIR__ . '/options.php',
            'enableCaching' => true,
        ],     
    ],
];
```

The component itself can be configured with the following properties:

| Property | Default value | Description |
|----------|---------------|-------------| 
| db | db | The DB connection object or the application component ID of the DB connection. |
| cache | cache |The cache object or the application component ID of the cache object. The option data will be cached using this cache object. |
| optionTable | {{%option}} | The name of the option table. |
| cachingDuration | 0 | The time in seconds that the options can remain valid in cache. Use 0 to indicate that the cached data will never expire. |
| enableCaching | false | Whether to enable options caching |
| configFile |  | Options config file. This file will be required. File must return config array. |
| config |  | Variable into which the config from configFile will be loaded. |

**Notice**: Option config is lazy-loaded on init (as opposite of including it in the 
application config). This is because the translation engine is not available at that time.  

Option config/definition array contains list of the option groups.


#### Option group

Every option group consists of these attributes. Attributes marked with (*) are required.

* **title** (*) - Group title    
* **items** (*) - Contains array of option items (see below)     
* **visible** - If the group is visible    


#### Option items

Option group attribute `items` is array containing option items.

Option item consists of these attributes. Attributes marked with (*) are required.

* **name** (*) - Option name (unique)    
* **type** (*) - Option type. See Option::TYPE_\* constants.    
* **title** (*) - Option title    
* **hint** - Option description    
* **visible** - If the option is visible    
* **default** - Specify option default value. Getting defined but unset option yields 
    this value. When not set, `null` will be returned. 
* **context** - If the option is context option. If this is true, option will not be 
    rendered using provided actions.    
* **data** - For option type=TYPE_OPTION this attribute contains data in form of:
    1. array of arrays containing keys id and name (see example below) or
    2. anonymous function returning the same array     

**ADVICE**: If you need to create divider between options in the same option group, 
simply put string as one of the `items` array item. 

Config example

```
return [
    [
        'title' => Yii::t('app', 'Group one'),
        'visible' => true,
        'items' => [
            ['name' => 'PHONE', 'type' => Option::TYPE_PHONE, 'title' => Yii::t('app', 'Phone number')],
            ['name' => 'EMAIL', 'type' => Option::TYPE_EMAIL, 'title' => Yii::t('app', 'Email address')],
            Yii::t('app', 'Apperance'), //Divider
            [
                'name' => 'COLOR',
                'type' => Option::TYPE_OPTION,
                'title' => Yii::t('app', 'Color scheme'),
                'data' => [
                    ['id' => 'ff0000', 'name' => 'Red'],
                    ['id' => '00ff00', 'name' => 'Green'],
                    ['id' => '0000ff', 'name' => 'Blue'],
                ],
            ],
        ],
    ],
    [
        'title' => Yii::t('app', 'Another group'),
        'visible' => YII_ENV_DEV, //Visible only in dev env
        'items' => [
            [
                'name' => 'LOGIN_TYPE',
                'type' => Option::TYPE_OPTION,
                'title' => Yii::t('app', 'Login Type'),
                'hint' => Yii::t('app', 'Please select desired login type'),
                'data' => function () {
                    return \app\classes\Tools::findAvailableLoginProviders();
                },
            ],
        ],
    ],
];
```


Usage
-----

This extension creates `options` component for storing configuration.

**Getting options**

```php
$value = \Yii::$app->options->get('OPTION_NAME');
``` 

**Setting options**

```php
\Yii::$app->options->set('OPTION_NAME', $value);
``` 

**Context options**

Context comes handy in case you need to deal with storing different configuration for
multiple entities (for example user configuration). 

In this use case context can be uses. Simply specify entity identificator as `context` and
entity record id as `context_id` and you are done.

Getting and setting options is done via another arguments of methods get() and set().

```php
$userValue = \Yii::$app->options->get('OPTION_NAME', self::class, $this->getPrimaryKey());
\Yii::$app->options->set('OPTION_NAME', $userValue, self::class, $this->getPrimaryKey());
```


### Rendering settings page

If you want to render settings page, there is `futuretek\options\IndesAction` provided. 
You can simply add it to the controller `actions()` method.

There is also `futuretek\options\OptionsHelper` that provides method for rendering option 
edit field based on option type.

See mentioned files for more details.
 
 
Development
-----------

### Translations

Translations are managed trough standard Yii2 translations. To automatically 
register extension translations we recommend to use our another extension 
[futuretek/yii2-composer](https://github.com/futuretek-solutions-ltd/ext-yii2-composer).

To generate messages for translating run the following command in extension root directory.

```
yii message/extract messages/config.php
```


### Assets

Assets are managed by [Compass](http://compass-style.org/)

* While developing run `compass watch` in extension root directory
* To compile assets for final distribution run `compass compile -e production --force` in extension root directory.
