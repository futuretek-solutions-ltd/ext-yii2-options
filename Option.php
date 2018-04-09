<?php

namespace futuretek\options;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Model Option
 *
 * @property integer $id ID
 * @property string $name Option name (key)
 * @property string $value Option value
 * @property string $title Option title
 * @property string $description
 * @property string $default_value
 * @property string $unit
 * @property integer $system
 * @property string $type
 * @property string $context
 * @property string $data
 * @property string $category
 * @property int $context_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @package futuretek\options
 * @author  Lukáš Černý <lukas.cerny@futuretek.cz>, Petr Compel <petr.compel@futuretek.cz>
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class Option extends ActiveRecord
{
    /**
     * Boolean type
     */
    const TYPE_BOOL = 'B';
    /**
     * Datetime type
     */
    const TYPE_DATETIME = 'D';
    /**
     * E-mail type
     */
    const TYPE_EMAIL = 'E';
    /**
     * Float type
     */
    const TYPE_FLOAT = 'F';
    /**
     * Int type
     */
    const TYPE_INT = 'I';
    /**
     * Option (drop-down) type
     */
    const TYPE_OPTION = 'O';
    /**
     * Phone number type
     */
    const TYPE_PHONE = 'P';
    /**
     * String type
     */
    const TYPE_STRING = 'S';
    /**
     * Text type (long string)
     */
    const TYPE_TEXT = 'X';
    /**
     * Text type (long string)
     */
    const TYPE_HTML = 'H';
    /**
     * Time type
     */
    const TYPE_TIME = 'T';
    /**
     * URL address type
     */
    const TYPE_URL = 'U';
    /**
     * Password type
     */
    const TYPE_PASSWORD = 'W';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'option';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description', 'data', 'context', 'unit'], 'string'],
            [['system', 'context_id'], 'integer'],
            [['value', 'created_at', 'updated_at', 'default_value'], 'safe'],
            [['name', 'title'], 'string', 'max' => 128],
            [['category'], 'string', 'max' => 64],
            [['type'], 'string', 'max' => 1],
            [['context'], 'string', 'max' => 16],
            [['unit'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('fts-yii2-options', 'ID'),
            'name' => Yii::t('fts-yii2-options', 'Name'),
            'value' => Yii::t('fts-yii2-options', 'Value'),
            'default_value' => Yii::t('fts-yii2-options', 'Default Value'),
            'unit' => Yii::t('fts-yii2-options', 'Unit'),
            'title' => Yii::t('fts-yii2-options', 'Title'),
            'description' => Yii::t('fts-yii2-options', 'Description'),
            'data' => Yii::t('fts-yii2-options', 'Data'),
            'system' => Yii::t('fts-yii2-options', 'System'),
            'type' => Yii::t('fts-yii2-options', 'Type'),
            'category' => Yii::t('fts-yii2-options', 'Category'),
            'context' => Yii::t('fts-yii2-options', 'Context'),
            'context_id' => Yii::t('fts-yii2-options', 'Context'),
            'created_at' => Yii::t('fts-yii2-options', 'Created At'),
            'updated_at' => Yii::t('fts-yii2-options', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert && $this->hasAttribute('created_at')) {
            $this->created_at = (new \DateTime())->format('Y-m-d H:i:s');
        }
        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = (new \DateTime())->format('Y-m-d H:i:s');
        }

        return parent::beforeSave($insert);
    }

    /**
     * Get option value
     *
     * @param string $name Option name
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @param mixed|null $defaultValue Default value
     * @return null|string Option value or defaultValue when option not found
     *
     * @static
     */
    public static function get($name, $context = 'Option', $context_id = null, $defaultValue = null)
    {
        $options = self::_loadCache($context, $context_id);
        if (!array_key_exists($name, $options)) {
            return $defaultValue;
        }
        if ($options[$name]['type'] === self::TYPE_PASSWORD) {
            return Yii::$app->getSecurity()->decryptByPassword(base64_decode($options[$name]['value']), Yii::$app->params['salt']);
        }

        return $options[$name]['value'];
    }

    /**
     * Get option value from Option context
     *
     * @param string $name Option name
     * @return null|string Option value or boolean false when option not found
     *
     * @static
     * @deprecated Please use Option::get() instead.
     */
    public static function getOpt($name)
    {
        return self::get($name);
    }

    /**
     * Get all context options
     *
     * @param string $context Context
     * @param int $context_id Context ID
     *
     * @return array Associative array of options (name => value)
     * @deprecated
     * @static
     */
    public static function getAll($context = 'Option', $context_id = null)
    {
        $options = self::_loadCache($context, $context_id);

        foreach ($options as &$option) {
            if ($option['type'] === self::TYPE_PASSWORD) {
                $option['value'] = Yii::$app->getSecurity()->decryptByPassword(base64_decode($option['value']), Yii::$app->params['salt']);
            }
        }

        return ArrayHelper::map($options, 'name', 'value');
    }

    /**
     * Set option value / create option
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @param string $type Option type (default: Option::TYPE_STRING)
     * @param bool $system Is this option system (default: false)
     *
     * @return bool If option was set successfully
     *
     * @throws \yii\base\InvalidConfigException
     * @static
     */
    public static function set($name, $value, $context = 'Option', $context_id = null, $type = Option::TYPE_STRING, $system = false)
    {
        $option = self::findOne(['name' => $name, 'context' => $context, 'context_id' => $context_id]);

        if ($option === null) {
            $option = new self();
            $option->name = $name;
            $option->type = $type;
            $option->system = (int)$system;
            $option->context = $context;
            $option->context_id = $context_id === null ? null : (int)$context_id;
        } else {
            $type = $option->type;
        }

        if ($type === self::TYPE_PASSWORD) {
            $value = base64_encode(Yii::$app->getSecurity()->encryptByPassword($value, Yii::$app->params['salt']));
        }

        $option->value = $value;

        self::_invalidateCache($context, $context_id);

        return $option->save();
    }

    /**
     * Check if option with specified name exists
     *
     * @param string $name Option name
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @return bool
     */
    public static function has($name, $context = 'Options', $context_id = null)
    {
        $options = self::_loadCache($context, $context_id);

        return array_key_exists($name, $options);
    }

    /**
     * Set option value / create option
     *
     * @param string $name Option name
     * @param string $title Option title
     * @param string $description Option description
     * @param mixed $defaultValue Default value
     * @param string $type Option type
     * @param bool $system Is this system option
     * @param string $unit Value unit
     * @param mixed $data Option data (for use with select option type)
     * @param string $context Option context
     * @param int|null $context_id Context ID
     * @return bool If option was set successfully
     *
     * @static
     */
    public static function create($name, $title, $description, $defaultValue, $type, $system, $unit, $data, $context = 'Option', $context_id = null)
    {
        $option = self::findOne(['name' => $name, 'context' => $context, 'context_id' => $context_id]);

        if ($option === null) {
            $option = new self();
            $option->name = strtoupper($name);
            $option->context = $context;
            $option->context_id = $context_id === null ? null : (int)$context_id;
        }
        $option->title = $title;
        $option->description = $description;
        $option->default_value = $defaultValue;
        $option->type = $type;
        $option->system = (int)$system;
        $option->unit = $unit;
        $option->setData($data);
        $option->data = '';

        self::_invalidateCache($context, $context_id);

        return $option->save();
    }

    /**
     * Set option value to default value
     *
     * @param string $name Option name
     * @param string $context Option context
     * @param int|null $context_id Context ID
     *
     * @return bool If default value was set successfully
     * @static
     */
    public static function setDefaultValue($name, $context = 'Option', $context_id = null)
    {
        $option = self::findOne(['name' => $name, 'context' => $context, 'context_id' => $context_id]);

        if ($option === null) {
            return false;
        }

        $option->value = $option->default_value;

        self::_invalidateCache($context, $context_id);

        return $option->save();
    }

    /**
     * Delete option
     *
     * @param string $name Option name
     * @param string $context Context
     * @param int|null $context_id Context ID
     *
     * @return bool If option was deleted successfully
     *
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @static
     */
    public static function del($name, $context = 'Option', $context_id = null)
    {
        $condition = ['name' => $name, 'context' => $context];
        if ($context_id) {
            $condition['context_id'] = $context_id;
        }

        self::_invalidateCache($context, $context_id);

        return (bool)self::deleteAll($condition);
    }

    /**
     * Delete all options for given context and context ID
     *
     * @param string $context Context
     * @param int $context_id Context ID
     *
     * @return bool If options was deleted successfully
     * @static
     */
    public static function delAll($context, $context_id)
    {
        self::_invalidateCache($context, $context_id);

        return (bool)self::deleteAll(['context' => $context, 'context_id' => $context_id]);
    }

    /**
     * Get option from user context
     *
     * @param string $name Option name
     * @param int|null $userId User ID
     * @param string|null $defaultValue Default value in case value is not set
     *
     * @return bool|string Option value or boolean false when option not found
     *
     * @deprecated
     * @throws Exception
     * @static
     */
    public static function getUser($name, $userId = null, $defaultValue = null)
    {
        return self::get($name, 'User', $userId, $defaultValue);
    }

    /**
     * Set option value in User context
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param int|null $userId User ID
     *
     * @return bool If option was set successfully
     *
     * @deprecated
     * @throws Exception
     * @static
     */
    public static function setUser($name, $value, $userId = null)
    {
        self::_invalidateCache('User', $userId);

        return self::set($name, $value, 'User', $userId);
    }

    /**
     * Get option data
     *
     * @return array
     * @throws \yii\base\InvalidParamException
     * @throws \RuntimeException
     */
    public function getData()
    {
        $data = unserialize($this->data);

        if ($data instanceof OptionData) {
            return $data->getData();
        }

        return $data;
    }

    /**
     * Set option data
     *
     * @param OptionData $data Data definition
     */
    public function setData(OptionData $data)
    {
        $this->data = serialize($data);
    }

    /**
     * Loads all options from specified context (using cache)
     *
     * @param string $context Context
     * @param int $context_id Context ID
     * @return array[]
     */
    private static function _loadCache($context, $context_id)
    {
        $key = [__NAMESPACE__, __CLASS__, 'opt', $context, $context_id];
        $cache = Yii::$app->getCache()->get($key);
        if ($cache !== false) {
            return $cache['data'];
        }

        $result = self::find()
            ->select(['name', 'value', 'type'])
            ->where(['context' => $context, 'context_id' => $context_id])
            ->asArray()
            ->all();

        $result = ArrayHelper::index($result, 'name');

        Yii::$app->getCache()->set($key, ['data' => $result]);

        return $result;
    }

    /**
     * Invalidates cache for specified context
     *
     * @param string $context Context
     * @param int $context_id Context ID
     */
    private static function _invalidateCache($context, $context_id)
    {
        Yii::$app->getCache()->delete([__NAMESPACE__, __CLASS__, 'opt', $context, $context_id]);
    }
}
