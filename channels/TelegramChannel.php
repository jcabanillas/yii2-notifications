<?php

namespace jcabanillas\notifications\channels;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\httpclient\Client;
use jcabanillas\notifications\Channel;
use jcabanillas\notifications\Notification;


class TelegramChannel extends Channel
{

    /**
     * @var Client|array|string
     */
    public $httpClient;

    /**
     * @var string
     */
    public $apiUrl = "https://api.telegram.org/";

    /**
     * Each bot is given a unique authentication token when it is created.
     * The token looks something like 123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
     * @var string
     */
    public $botToken;

    /**
     * @var string
     */
    public $parseMode = self::PARSE_MODE_MARKDOWN;

    const PARSE_MODE_HTML = "HTML";

    const PARSE_MODE_MARKDOWN = "Markdown";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset($this->botToken)) {
            throw new InvalidConfigException('Bot token is undefined');
        }

        if (!isset($this->httpClient)) {
            $this->httpClient = [
                'class' => Client::className(),
                'baseUrl' => $this->apiUrl,
            ];
        }
        $this->httpClient = Instance::ensure($this->httpClient, Client::className());
    }

    /**
     * Send the given notification.
     *
     * @param Notification $notification
     * @return void
     */
    public function send(Notification $notification)
    {
        $subject = (string)$notification->getTitle(); // Html::a((string)$notification->getTitle(), $notificacion->route);
        // echo "{$subject}<br /><br />";
        // exit;
        $body = (string)$notification->getDescription();
        $text = "*{$subject}*\n{$body}";
        $chatId = $notification->data['chatId'];

        /*
        echo "{$text}<br /><br />";
        var_dump($notification->data);
        echo "<br /><br />{$chatId}";
        exit;
        */

        // $message = $notification->data['message'];
        if (!$chatId) {
            throw new InvalidArgumentException('No chat ID provided');
        }

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            // 'disable_notification' => $message->silentMode,
            // 'disable_web_page_preview' => $message->withoutPagePreview,
        ];
        /*
        if ($message->replyToMessageId) {
            $data['reply_to_message_id'] = $message->replyToMessageId;
        }

        if ($message->replyMarkup) {
            $data['reply_markup'] = Json::encode($message->replyMarkup);
        }

        if (isset($this->parseMode)) {
            $data['parse_mode'] = $this->parseMode;
        }
        */

        // var_dump($data);
        // exit;

        return $this->httpClient->createRequest()
            ->setUrl($this->createUrl())
            ->setData($data)
            ->send();


    }

    private function createUrl()
    {
        return "bot{$this->botToken}/sendMessage";
    }

}