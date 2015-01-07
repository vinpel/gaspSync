<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
user_pref("general.warnOnAboutConfig", true);
user_pref("services.sync.log.appender.file.logOnSuccess", true);
user_pref("services.sync.tokenServerURI", "<?=$publicURI?>/tokenServer/1.0/sync/1.5");
