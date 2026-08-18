<?php

namespace futuretek\options\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Renders a Bootstrap-style tabs component (`nav-tabs` + `tab-content`) without requiring
 * yiisoft/yii2-bootstrap. Adapted from yii\bootstrap\Tabs (Yii Software LLC, BSD-3-Clause);
 * nested dropdown items are not supported since this package never used them.
 */
class Tabs extends Widget
{
    public $items = [];
    public $itemOptions = [];
    public $headerOptions = [];
    public $linkOptions = [];
    public $encodeLabels = true;
    public $navType = 'nav-tabs';
    public $renderTabContent = true;
    public $tabContentOptions = [];
    public $template = '{headers}{panes}';
    public $options = [];
    public $clientOptions = [];
    public $clientEvents = [];

    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        Html::addCssClass($this->options, ['widget' => 'nav', $this->navType]);
        Html::addCssClass($this->tabContentOptions, 'tab-content');
    }

    public function run()
    {
        $this->registerPlugin('tab');

        return $this->renderItems();
    }

    /**
     * Emits the same client-side JS as yii\bootstrap\Tabs, without registering a Bootstrap
     * JS asset bundle - the host application is expected to load its own Bootstrap JS.
     */
    protected function registerPlugin($name)
    {
        $id = $this->options['id'];

        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
            $this->getView()->registerJs("jQuery('#$id').$name($options);");
        }

        if (!empty($this->clientEvents)) {
            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
            $this->getView()->registerJs(implode("\n", $js));
        }
    }

    protected function renderItems()
    {
        $headers = [];
        $panes = [];

        if (!$this->hasActiveTab()) {
            $this->activateFirstVisibleTab();
        }

        foreach ($this->items as $n => $item) {
            if (!ArrayHelper::remove($item, 'visible', true)) {
                continue;
            }
            if (!array_key_exists('label', $item)) {
                throw new InvalidConfigException("The 'label' option is required.");
            }
            if (isset($item['items'])) {
                throw new InvalidConfigException('Dropdown tab items are not supported.');
            }

            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $headerOptions = array_merge($this->headerOptions, ArrayHelper::getValue($item, 'headerOptions', []));
            $linkOptions = array_merge($this->linkOptions, ArrayHelper::getValue($item, 'linkOptions', []));

            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $options['id'] = ArrayHelper::getValue($options, 'id', $this->options['id'] . '-tab' . $n);

            Html::addCssClass($options, ['widget' => 'tab-pane']);
            if (ArrayHelper::remove($item, 'active')) {
                Html::addCssClass($options, 'active');
                Html::addCssClass($headerOptions, 'active');
            }

            if (isset($item['url'])) {
                $header = Html::a($label, $item['url'], $linkOptions);
            } else {
                if (!isset($linkOptions['data-toggle'])) {
                    $linkOptions['data-toggle'] = 'tab';
                }
                $header = Html::a($label, '#' . $options['id'], $linkOptions);
            }

            if ($this->renderTabContent) {
                $tag = ArrayHelper::remove($options, 'tag', 'div');
                $panes[] = Html::tag($tag, isset($item['content']) ? $item['content'] : '', $options);
            }

            $headers[] = Html::tag('li', $header, $headerOptions);
        }

        $headersHtml = Html::tag('ul', implode("\n", $headers), $this->options);
        $panesHtml = $this->renderPanes($panes);

        return strtr($this->template, [
            '{headers}' => $headersHtml,
            '{panes}' => $panesHtml,
        ]);
    }

    protected function hasActiveTab()
    {
        foreach ($this->items as $item) {
            if (isset($item['active']) && $item['active'] === true) {
                return true;
            }
        }

        return false;
    }

    protected function activateFirstVisibleTab()
    {
        foreach ($this->items as $i => $item) {
            $active = ArrayHelper::getValue($item, 'active', null);
            $visible = ArrayHelper::getValue($item, 'visible', true);
            if ($visible && $active !== false) {
                $this->items[$i]['active'] = true;
                return;
            }
        }
    }

    public function renderPanes($panes)
    {
        return $this->renderTabContent ? "\n" . Html::tag('div', implode("\n", $panes), $this->tabContentOptions) : '';
    }
}
