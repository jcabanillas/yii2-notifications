<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = Yii::t('modules/notifications', 'Notifications');
$this->params['breadcrumbs'][] = $this->title;

// if (array_key_exists('toolbar', $this->params)):
$toolbar = Html::a(Yii::t('modules/notifications', 'Delete all'), Url::toRoute(['/notifications/default/delete-all']), ['class' => "btn btn-danger"]);
$toolbar .= Html::a(Yii::t('modules/notifications', 'Mark all as read'), Url::toRoute(['/notifications/default/read-all']), ['class' => "btn btn-secondary"]);
$this->params['toolbar'] = $toolbar;
// endif;

?>
<div class="card card-custom">
    <div class="card-body">

        <ul id="notifications-items">
            <?php if ($notifications): ?>
                <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item<?php if ($notif['read']): ?> read<?php endif; ?>"
                        data-id="<?= $notif['id']; ?>" data-key="<?= $notif['key']; ?>">
                        <a href="<?= $notif['url'] ?>">
                            <i class="fa fa-comment"></i>
                            <span class="message"><?= Html::encode($notif['message']); ?></span>
                        </a>
                        <small class="timeago"><?= $notif['timeago']; ?></small>
                        <span class="mark-read" data-toggle="tooltip"
                              title="<?php if ($notif['read']): ?><?= Yii::t('modules/notifications', 'Read') ?><?php else: ?><?= Yii::t('modules/notifications', 'Mark as read') ?><?php endif; ?>"></span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="empty-row"><?= Yii::t('modules/notifications', 'There are no notifications to show') ?></li>
            <?php endif; ?>
        </ul>

        <?= LinkPager::widget(['pagination' => $pagination]); ?>

    </div>
</div>
