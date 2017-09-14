<?php

namespace futuretek\options {

    use futuretek\admin\classes\Hook;

    /**
     * Class AdminHook
     * @package futuretek\options
     */
    class AdminHook extends Hook
    {

        public function init()
        {
            $this->controllerRoute = 'options';
            $this->controllerClass = 'futuretek\options\AdminController';
        }

        /**
         * Return menu collection
         *
         * @return array
         */
        public function getMenuArray()
        {
            return [
                [
                    'label' => $this->getName(),
                    'icon' => $this->getIcon(),
                    'url' => [$this->baseUrl('options/index')]
                ]
            ];
        }

        /**
         * Get extension name
         *
         * @return string Name
         */
        public function getName()
        {
            return \Yii::t('fts-yii2-options', 'Options');
        }

        /**
         * Get extension description
         *
         * @return string Description
         */
        public function getDescription()
        {
            return \Yii::t('fts-yii2-options', 'Options editor');
        }

        /**
         * Get extension icon
         *
         * @return string Icon
         */
        public function getIcon()
        {
            return 'fa fa-cogs';
        }
    }

}