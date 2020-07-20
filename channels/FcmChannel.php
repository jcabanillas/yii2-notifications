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


class FcmChannel extends Channel
{

    /**
     * @var Client|array|string
     */
    public $httpClient;

    /**
     * @var string
     */
    public $apiUrl = "https://fcm.googleapis.com/fcm";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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
        $body = (string)$notification->getDescription();

        $to = $notification->data['deviceId'];

        if (!$to) {
            throw new InvalidArgumentException('No To provided');
        }

        $data = [
            "to" => $to,
            "notification" => [
                "title" => $subject,
                "body" => $body,
            ],
            "data" => [
                "id" => $notification->data['id'],
                "tipo" => $notification->data['tipo'],
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            ]
        ];

        return $this->httpClient->createRequest()
            ->setUrl($this->createUrl())
            ->setData($data)
            ->send();


    }

    private function createUrl()
    {
        return "{$this->apiUrl}/send";
    }

}