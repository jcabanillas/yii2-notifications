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

    public $spanTag = 'span';

    public $spanContent = <<<HTML
												<!--begin::Svg Icon | path:/metronic/theme/html/demo1/dist/assets/media/svg/icons/Code/Compiling.svg-->
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.2929 2.70711C11.6834 2.31658 12.3166 2.31658 12.7071 2.70711L15.2929 5.29289C15.6834 5.68342 15.6834 6.31658 15.2929 6.70711L12.7071 9.29289C12.3166 9.68342 11.6834 9.68342 11.2929 9.29289L8.70711 6.70711C8.31658 6.31658 8.31658 5.68342 8.70711 5.29289L11.2929 2.70711Z" fill="currentColor"></path>
												<path d="M11.2929 14.7071C11.6834 14.3166 12.3166 14.3166 12.7071 14.7071L15.2929 17.2929C15.6834 17.6834 15.6834 18.3166 15.2929 18.7071L12.7071 21.2929C12.3166 21.6834 11.6834 21.6834 11.2929 21.2929L8.70711 18.7071C8.31658 18.3166 8.31658 17.6834 8.70711 17.2929L11.2929 14.7071Z" fill="currentColor"></path>
												<path opacity="0.3" d="M5.29289 8.70711C5.68342 8.31658 6.31658 8.31658 6.70711 8.70711L9.29289 11.2929C9.68342 11.6834 9.68342 12.3166 9.29289 12.7071L6.70711 15.2929C6.31658 15.6834 5.68342 15.6834 5.29289 15.2929L2.70711 12.7071C2.31658 12.3166 2.31658 11.6834 2.70711 11.2929L5.29289 8.70711Z" fill="currentColor"></path>
												<path opacity="0.3" d="M17.2929 8.70711C17.6834 8.31658 18.3166 8.31658 18.7071 8.70711L21.2929 11.2929C21.6834 11.6834 21.6834 12.3166 21.2929 12.7071L18.7071 15.2929C18.3166 15.6834 17.6834 15.6834 17.2929 15.2929L14.7071 12.7071C14.3166 12.3166 14.3166 11.6834 14.7071 11.2929L17.2929 8.70711Z" fill="currentColor"></path>
											</svg>
												<!--end::Svg Icon-->
											
HTML;
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

        $count = self::getCountUnseen();
        /*
        $countOptions = array_merge([
            'tag' => 'span',
            'data-count' => $count,
        ], $this->countOptions);
        Html::addCssClass($countOptions, 'label label-warning navbar-badge notifications-count');
        */
        if ($count) {
            // $countOptions['style'] = 'display: none;';
            $html .= Html::beginTag($this->linkTag, array_merge($this->linkOptions, ['class' => 'pulse pulse-primary']));
        } else {
            $html .= Html::beginTag($this->linkTag, array_merge($this->linkOptions));
        }
        $html .= Html::beginTag($this->spanTag, $this->spanOptions);
        // $countTag = ArrayHelper::remove($countOptions, 'tag', 'span');
        // $html .= Html::tag($countTag, $count, $countOptions);
        $html .= $this->spanContent;
        $html .= Html::endTag($this->spanTag);
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
