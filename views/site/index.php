<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;



use app\assets\GentelellaAsset;
GentelellaAsset::register($this);

$this->title="Welcome";
?>

<div class="page-title">
  <div class="title_left">
    <h3><?=$this->title?></h3>
  </div>
</div>

<div class="clearfix"></div>
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Self Hosted mozilla Sync 1.5</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="#">Settings 1</a>
              </li>
              <li><a href="#">Settings 2</a>
              </li>
            </ul>
          </li>
          <li><a class="close-link"><i class="fa fa-close"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">


        <h1><div class="title-font">
          <img src="firefox_med.png">gaspSync</div></h1>
          <h3>PHP own Sync-1.5 Server</h3>
          <br/>
          <div class="panel panel-info">
            <div class="panel-heading">
              Configuration
            </div>
            <div class="panel-body">
              <div class="forum-font">
                in command line : <br/>
                <ul>
                  <li><code>yii install</code> : create directory, config file, dsa Key ....</li>
                  <li><code>yii migrate</code> : create the database structure</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="panel panel-info">
            <div class="panel-heading">
              Sync Storage configuration for a desktop Firefox :
            </div>
            <div class="panel-body">
              <div class="forum-font">
                <ol>
                  <li>go to <?= Html::a('about:support','about:support');?></li>
                  <li>click on the "profil folder" button, it will open user directory in windows explorer</li>
                  <li>download and place this file : <?= Html::a('user.js', ['site/userjs']) ?>                </li>
                  <li>restart firefox</li>
                  <li>you can control the "services.sync.tokenServerURI" configuration in <?= Html::a('about:config','about:config');?></li>
                  <li>log into you'r firefox account</li>
                  <li>check <?= Html::a('about:sync-log','about:sync-log');?> for success 5-10 min later</li>
                  <li>login : <?= Html::a('about:accounts','about:accounts');?>
                  </ol>
                </div>
              </div>
            </div>
            <div class="panel panel-info">
              <div class="panel-heading">
                Fxa Server configuration for a desktop Firefox :
              </div>
              <div class="panel-body">
                <div class="forum-font">
                  <code>This part is not ready to be tested, nor finished.</code></br>
                  <br/>
                  We will use the FxA javascript client from <a href="http://www.mozilla.org">Mozilla</a> to secure the password before sending it to the sync server
                  (see <code>https://github.com/mozilla/fxa-js-client</code>)
                </div>
              </div>
            </div>
            <?= Yii::powered() ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
