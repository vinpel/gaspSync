<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
//user_pref("services.sync.tokenServerURI", "<?=$publicURI/tokenServer/1.0/sync/1.5");
?>
user_pref("general.warnOnAboutConfig", true);
user_pref("services.sync.log.appender.file.logOnSuccess", true);
user_pref("identity.fxaccounts.remote.signin.uri","<?=$publicURI?>signin?service=sync&context=fx_desktop_v2");
user_pref("identity.fxaccounts.remote.signup.uri","<?=$publicURI?>signup?service=sync&context=fx_desktop_v2");
user_pref("identity.fxaccounts.remote.force_auth.uri","<?=$publicURI?>force_auth?service=sync&context=fx_desktop_v2"
user_pref("identity.fxaccounts.settings.uri","<?=$publicURI?>settings");
