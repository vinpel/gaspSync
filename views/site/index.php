<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use app\assets\CodemirrorAsset;
CodemirrorAsset::register($this);

use yii\web\View;
use app\assets\GentelellaAsset;
GentelellaAsset::register($this);

$this->title="Self Hosted mozilla Sync 1.5";


$this->registerJs(
  "CodeMirror.fromTextArea(document.getElementById('userjs'), {
    mode:'javascript',
    lineWrapping: true,
    lineNumbers: true
  });
  ",
  View::POS_READY,
  'test'
);

?>
<style>
.CodeMirror {
  border: 1px solid #eee;
  height: auto;
}

</style>

<div class="page-title">
  <div class="title_left">
    <h3><?=$this->title?></h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Sync Storage configuration</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <h2>
          <div class="title-font">
            <img src="firefox_med.png">
          </div>

        </h2>
        based on <a href="https://mozilla-services.readthedocs.io/en/latest/howtos/run-fxa.html#howto-run-fxa">run-fxa</a>



        <ol>
          <li>go to <?= Html::a('about:support','about:support');?></li>
          <li>click on the "profil folder" button, it will open user directory in windows explorer</li>
          <li>use this file : <?= Html::a('user.js', ['site/userjs']) ?> or insert in existing file :</li>
          <textarea id="userjs"><?php print $this->render('userjs.php',['publicURI'=>$publicURI]);?>
          </textarea>
          <li>restart firefox</li>
          <li>you can control the "services.sync.tokenServerURI" configuration in <?= Html::a('about:config','about:config');?></li>
          <li>log with a firefox account</li>
          <li>check <?= Html::a('about:sync-log','about:sync-log');?> for success 5-10 min later</li>
          <li>login : <?= Html::a('about:accounts','about:accounts');?></li>
        </ol>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Installation</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        Edit file : <br/>
        <ul>
          <li><code>yii install</code> : create directory, config file, dsa Key ....</li>
          <li><code>yii migrate</code> : create the database structure</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Fxa Server configuration for a desktop Firefox</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <code>This part is not ready to be tested, nor finished.</code></br>
        <br/>
        We will use the FxA javascript client from <a href="http://www.mozilla.org">Mozilla</a> to secure the password before sending it to the sync server
        (see <code>https://github.com/mozilla/fxa-js-client</code>)
      </div>
    </div>
  </div>
</div>
<?php


?>
<?= Yii::powered() ?>
