<?php

namespace futuretek\options;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Options component
 *
 * @package app\classes\options
 * @author  Lukáš Černý <lukas.cerny@marbes.cz>
 * @link    http://www.marbes.cz
 */
class Option extends Component
{
    /** Boolean type */
    const TYPE_BOOL = 'B';

    /** Datetime type */
    const TYPE_DATETIME = 'D';

    /** E-mail type */
    const TYPE_EMAIL = 'E';

    /** Float type */
    const TYPE_FLOAT = 'F';

    /** Int type */
    const TYPE_INT = 'I';

    /** Option (drop-down) type */
    const TYPE_OPTION = 'O';

    /** Phone number type */
    const TYPE_PHONE = 'P';

    /** String type */
    const TYPE_STRING = 'S';

    /** Text type (long string) */
    const TYPE_TEXT = 'X';

    /** HTML type (long string) */
    const TYPE_HTML = 'H';

    /** Time type */
    const TYPE_TIME = 'T';

    /** URL address type */
    const TYPE_URL = 'U';

    /** Password type */
    const TYPE_PASSWORD = 'W';

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     *
     * After the object is created, if you want to change this property, you should only assign
     * it with a DB connection object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';

    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * The option data will be cached using this cache object.
     * Note, that to enable caching you have to set [[enableCaching]] to `true`, otherwise setting this property has no effect.
     *
     * After the object is created, if you want to change this property, you should only assign
     * it with a cache object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     * @see cachingDuration
     * @see enableCaching
     */
    public $cache = 'cache';

    /**
     * @var string the name of the option table.
     */
    public $optionTable = '{{%option}}';

    /**
     * @var int the time in seconds that the options can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var bool whether to enable options caching
     */
    public $enableCaching = false;

    /** @var string Options config file */
    public $configFile;

    /** @var array Options config */
    public $config;

    private static $_optionList = [];

    /**
     * Initializes the Option component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * Configured [[cache]] component would also be initialized.
     * @throws \Exception
     * @throws InvalidConfigException if [[db]] is invalid or [[cache]] is invalid.
     */
    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::class);
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
        }

        if (!file_exists($this->configFile)) {
            throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config file does not exist.'));
        }
        $this->config = require $this->configFile;

        $this->_normalizeConfig();
        $this->_parseConfig();
    }

    /**
     * Loads the options.
     *
     * @param string $context Option context
     * @param mixed $context_id Context ID
     * @return array the loaded options. The keys are option names, and the values are option values.
     * @throws \yii\db\Exception
     */
    protected function loadOptions($context, $context_id)
    {
        if ($this->enableCaching) {
            $key = [__NAMESPACE__, __CLASS__, $context, $context_id];
            $options = $this->cache->get($key);
            if ($options === false) {
                $options = $this->loadOptionsFromDb($context, $context_id);
                $this->cache->set($key, $options, $this->cachingDuration);
            }

            return $options;
        }

        return $this->loadOptionsFromDb($context, $context_id);
    }

    /**
     * Loads the messages from database.
     * You may override this method to customize the message storage in the database.
     * @param string $context Option context
     * @param mixed $context_id Context ID
     * @return array the messages loaded from database.
     * @throws \yii\db\Exception
     */
    protected function loadOptionsFromDb($context, $context_id)
    {
        $mainQuery = (new Query())
            ->select(['name', 'value'])
            ->from($this->optionTable)
            ->where(['context' => $context, 'context_id' => $context_id]);

        $options = $mainQuery->createCommand($this->db)->queryAll();

        return ArrayHelper::map($options, 'name', 'value');
    }

    /**
     * Check and normalize option config
     *
     * @throws \Exception
     * @throws InvalidConfigException
     */
    private function _normalizeConfig()
    {
        if (!is_array($this->config)) {
            throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config group have to be an array.'));
        }
        foreach ($this->config as &$group) {
            if (!array_key_exists('title', $group)) {
                throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config group title must be set.'));
            }
            if (!array_key_exists('visible', $group)) {
                $group['visible'] = true;
            }
            if (!array_key_exists('items', $group)) {
                $group['items'] = [];
            }
            if (!is_array($group['items'])) {
                throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config group items have to be an array.'));
            }
            foreach ($group['items'] as &$item) {
                if (!is_array($item)) {
                    continue;
                }
                if (!array_key_exists('name', $item)) {
                    throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config item name must be set.'));
                }
                if (!array_key_exists('type', $item)) {
                    throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config item type must be set.'));
                }
                if (!array_key_exists('title', $item)) {
                    throw new InvalidConfigException(Yii::t('fts-yii2-options', 'Option config item title must be set.'));
                }
                if (!array_key_exists('hint', $item)) {
                    $item['hint'] = null;
                }
                if (!array_key_exists('context', $item)) {
                    $item['context'] = false;
                }
                if (!array_key_exists('visible', $item)) {
                    $item['visible'] = true;
                }
                if (!array_key_exists('default', $item)) {
                    $item['default'] = null;
                }
            }
        }
    }

    /**
     * Parse options from option config to array for easier access
     */
    private function _parseConfig()
    {
        foreach ($this->config as $group) {
            foreach ($group['items'] as $item) {
                if (is_array($item)) {
                    self::$_optionList[$item['name']] = $item;
                }
            }
        }
    }

    /**
     * Get option value
     *
     * @param string $name Option name
     * @param string $context Option context
     * @param mixed $context_id Context ID
     * @param mixed|null $defaultValue Default value
     * @return null|string Option value or defaultValue when option not found
     * @throws \yii\base\InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public function get($name, $context = null, $context_id = null, $defaultValue = null)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(Yii::t('fts-yii2-options', 'Option with name {name} not defined.', ['name' => $name]));
        }

        if (self::$_optionList[$name]['context'] && ($context !== self::$_optionList[$name]['context'] || $context_id === null)) {
            throw new InvalidArgumentException(Yii::t('fts-yii2-options', 'Option with name {name} is defined as context option but no context was specified.', ['name' => $name]));
        }

        $options = $this->loadOptions($context, $context_id);
        if (!array_key_exists($name, $options)) {
            return $defaultValue !== null ? $defaultValue : self::$_optionList[$name]['default'];
        }

        //Decrypt password
        if (self::$_optionList[$name]['type'] === self::TYPE_PASSWORD) {
            return Yii::$app->getSecurity()->decryptByPassword(base64_decode($options[$name]), Yii::$app->params['salt']);
        }

        return $options[$name];
    }

    /**
     * Get all options in associative array name => value
     *
     * @param string $context Option context
     * @param mixed $context_id Context ID
     * @param bool $decrypt Decrypt password options
     * @return array
     * @throws \yii\db\Exception
     */
    public function getAll($context = null, $context_id = null, $decrypt = false)
    {
        $options = $this->loadOptions($context, $context_id);

        //Decrypt password
        if ($decrypt) {
            foreach ($options as $name => &$value) {
                if (self::$_optionList[$name]['type'] === self::TYPE_PASSWORD) {
                    $value = Yii::$app->getSecurity()->decryptByPassword(base64_decode($value), Yii::$app->params['salt']);
                }
            }
        }

        return $options;
    }

    /**
     * Get options definition
     *
     * @return array[]
     */
    public function getDefinition()
    {
        return self::$_optionList;
    }

    /**
     * Set option value / create option
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param string $context Option context
     * @param mixed $context_id Context ID
     * @return bool If option was set successfully
     * @throws \Exception
     */
    public function set($name, $value, $context = null, $context_id = null)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(Yii::t('fts-yii2-options', 'Option with name {name} not defined.', ['name' => $name]));
        }

        if (($context !== self::$_optionList[$name]['context'] || $context_id === null) && self::$_optionList[$name]['context']) {
            throw new InvalidArgumentException(Yii::t('fts-yii2-options', 'Option with name {name} is defined as context option but no context was specified.', ['name' => $name]));
        }

        if (self::$_optionList[$name]['type'] === self::TYPE_PASSWORD) {
            $value = base64_encode(Yii::$app->getSecurity()->encryptByPassword($value, Yii::$app->params['salt']));
        }

        $selectQuery = (new Query())
            ->select(['id'])
            ->from($this->optionTable)
            ->where(['name' => $name, 'context' => $context, 'context_id' => $context_id]);

        $id = $selectQuery->createCommand($this->db)->queryScalar();

        if ($id) {
            //Update
            $result = $this->db->createCommand()->update($this->optionTable, [
                'value' => $value,
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ], [
                'id' => $id,
            ])->execute();
        } else {
            //Create
            $result = $this->db->createCommand()->insert($this->optionTable, [
                'name' => $name,
                'value' => $value,
                'context' => $context,
                'context_id' => $context_id,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ])->execute();
        }

        $this->_invalidateCache($context, $context_id);

        return $result === 1;
    }

    /**
     * Check if option with specified name exists
     *
     * @param string $name Option name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, self::$_optionList);
    }

    /**
     * Invalidates cache for specified context
     *
     * @param string $context Context
     * @param mixed $context_id Context ID
     */
    private function _invalidateCache($context, $context_id)
    {
        $this->cache->delete([__NAMESPACE__, __CLASS__, $context, $context_id]);
    }

    /**
     * Get data from option item definition
     *
     * @param array $optionItem Option Item
     * @return array
     * @throws \Exception
     * @throws InvalidConfigException
     */
    public static function getData($optionItem)
    {
        if (!array_key_exists('data', $optionItem)) {
            return [];
        }

        if (is_callable($optionItem['data'])) {
            $dataMethod = $optionItem['data'];

            return $dataMethod();
        }

        if (is_array($optionItem['data'])) {
            return $optionItem['data'];
        }

        throw new InvalidConfigException(\Yii::t('fts-yii2-options', 'Invalid data type for option item {name}.', ['name' => $optionItem['name']]));
    }
}