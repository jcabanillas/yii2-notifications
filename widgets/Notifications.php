<?php

namespace jcabanillas\notifications\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\db\Query;
use jcabanillas\notifications\NotificationsAsset;


class Notifications extends \yii\base\Widget
{

    public $containerTag = 'li';

    public $options = ['class' => 'dropdown nav-notifications'];

    public $linkTag = 'a';

    public $linkOptions = [
        'href' => '#',
        'class' => 'dropdown-toggle',
        'data-toggle' => 'dropdown'
    ];
    public $spanOptions = [
        'class' => 'glyphicon glyphicon-bell'
    ];
    public $headerTag = 'div';
    
    public $headerOptions = [];
    public $menuOptions = [];
    public $footerOptions = [];

    /**
     * @var string the HTML options for the item count tag. Key 'tag' might be used here for the tag name specification.
     * For example:
     *
     * ```php
     * [
     *     'tag' => 'span',
     *     'class' => 'badge badge-warning',
     * ]
     * ```
     */
    public $countOptions = [];

    /**
     * @var array additional options to be passed to the notification library.
     * Please refer to the plugin project page for available options.
     */
    public $clientOptions = [];
    /**
     * @var integer the XHR timeout in milliseconds
     */
    public $xhrTimeout = 2000;
    /**
     * @var integer The delay between pulls in milliseconds
     */
    public $pollInterval = 60000;

    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->renderNavbarItem();

        $this->registerAssets();
    }

    /**
     * @inheritdoc
     */
    protected function renderNavbarItem()
    {
        $html = Html::beginTag($this->containerTag, $this->options);
        $html .= Html::beginTag($this->linkTag, $this->linkOptions);
        $html .= Html::tag('span', '', $this->spanOptions);

        $count = self::getCountUnseen();
        $countOptions = array_merge([
            'tag' => 'span',
            'data-count' => $count,
        ], $this->countOptions);
        Html::addCssClass($countOptions, 'label label-warning navbar-badge notifications-count');
        if (!$count) {
            $countOptions['style'] = 'display: none;';
        }
        $countTag = ArrayHelper::remove($countOptions, 'tag', 'span');
        $html .= Html::tag($countTag, $count, $countOptions);

        $html .= Html::endTag($this->linkTag);
        $html .= Html::begintag('div', $this->menuOptions);
        $header = Html::a(Yii::t('modules/notifications', 'Mark all as read'), '#', ['class' => 'read-all pull-right']);
        $header .= Yii::t('modules/notifications', 'Notifications');
        $html .= Html::tag($this->headerTag, $header, $this->headerOptions);

        $html .= Html::begintag('div', ['class' => 'notifications-list']);
        //$html .= Html::tag('div', '<span class="ajax-loader"></span>', ['class' => 'loading-row']);
        $html .= Html::tag('div', Html::tag('span', Yii::t('modules/notifications', 'There are no notifications to show'), ['style' => 'display: none;']), ['class' => 'empty-row']);
        $html .= Html::endTag('div');

        $footer = Html::a(Yii::t('modules/notifications', 'View all'), ['/notifications/default/index']);
        $html .= Html::tag('div', $footer, $this->footerOptions);
        $html .= Html::endTag('div');
        $html .= Html::endTag($this->containerTag);

        return $html;
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $this->clientOptions = array_merge([
            'id' => $this->options['id'],
            'url' => Url::to(['/notifications/default/list']),
            'countUrl' => Url::to(['/notifications/default/count']),
            'readUrl' => Url::to(['/notifications/default/read']),
            'readAllUrl' => Url::to(['/notifications/default/read-all']),
            'xhrTimeout' => Html::encode($this->xhrTimeout),
            'pollInterval' => Html::encode($this->pollInterval),
        ], $this->clientOptions);

        $js = 'Notifications(' . Json::encode($this->clientOptions) . ');';
        $view = $this->getView();

        NotificationsAsset::register($view);

        $view->registerJs($js);
    }

    public static function getCountUnseen()
    {
        $userId = Yii::$app->getUser()->getId();
        $count = (new Query())
            ->from('{{%notifications}}')
            ->andWhere(['or', 'user_id = 0', 'user_id = :user_id'], [':user_id' => $userId])
            ->andWhere(['seen' => false])
            ->count();
        return $count;
    }

}
