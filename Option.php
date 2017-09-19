<?php

namespace futuretek\options;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;

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
            [['value', 'created_at', 'updated_at'], 'safe'],
            [['name', 'title'], 'string', 'max' => 128],
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
     * @return null|string Option value or "NOT_SET" when option not found
     *
     * @static
     */
    public static function get($name, $context = 'Option', $context_id = null, $defaultValue = null)
    {
        $condition = ['name' => $name, 'context' => $context];
        if ($context_id) {
            $condition['context_id'] = $context_id;
        }

        /** @var Option $option */
        $option = self::find()->where($condition)->one();

        if ($option !== null) {
            if ($option->type === self::TYPE_PASSWORD) {
                $value = Yii::$app->getSecurity()->decryptByPassword(base64_decode($option->value), Yii::$app->params['salt']);
            } else {
                $value = $option->value;
            }
        } else {
            $value = $defaultValue;
        }

        if ($value === false && $defaultValue !== null) {
            return $defaultValue;
        }

        return $value;
    }

    /**
     * Get option value fromOption context
     *
     * @param string $name Option name
     * @return null|string Option value or boolean false when option not found
     *
     * @static
     */
    public static function getOpt($name)
    {
        /** @var Option $option */
        $option = self::find()->where(['name' => $name, 'context' => 'Option', 'context_id' => null])->one();
        if ($option === null) {
            return null;
        }

        if ($option->type === self::TYPE_PASSWORD) {
            $value = Yii::$app->getSecurity()->decryptByPassword(base64_decode($option->value), Yii::$app->params['salt']);
        } else {
            $value = $option->value;
        }

        return $value;
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
        $options = self::find()->where(['context' => $context, 'context_id' => $context_id])->asArray()->all();

        foreach ($options as &$option) {
            if ($option['type'] === self::TYPE_PASSWORD) {
                $option['value'] = Yii::$app->getSecurity()->decryptByPassword(base64_decode($option['value']), Yii::$app->params['salt']);
            }
        }

        return array_column($options, 'value', 'name');
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

        return $option->save();
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
}
