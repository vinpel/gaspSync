<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;


use app\assets\AppContent;
AppContent::register($this);
?>

<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <div class="well bs-component">
      <h1><div class="title-font">
        <img src="firefox_med.png">gaspSync</div></h1>
      <?= Html::beginForm('/','POST',['id'=>'ident-form'])?>
      <div class="form-group">
        <div class="row">
          <div class="col-lg-2">
            <label for="inputEmail" class="control-label">Email</label>
          </div>
          <div class="col-lg-2">
            <?= Html::input('text','email','test@test.com',['id'=>'inputEmail','class'=>''])?>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-2">
            <label for="inputEmail" class="control-label">Password</label>
          </div>
          <div class="col-lg-2">
            <?= Html::input('password','inputPassword','aaaaaaaa',['id'=>'inputPassword','class'=>''])?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="form-group">
          <div class="col-lg-10 col-lg-offset-2">
            <button id="playbutton" class="btn  btn-success has-spinner">
              <span class="spinner fa fa-spinner fa-spin active"></span>
              Create account
            </button>
          </div>
        </div>
      </div>
      <?= Html::endForm()?>
      <br/><code>Configuration  : <?= Html::a('help page', ['/']) ?></code>

    </div>
  </div>
</div>
<?php
$js = <<<JS
console.log('hero1');
// get the form id and set the event
$('#ident-form').on('submit', function(e) {

  e.preventDefault();
  e.stopImmediatePropagation();


  console.log($('input[name="_csrf"]').val());
  var client = new FxAccountClient('$publicURI');//add 0/v1/account/create?keys=true
  accountData = client.signUp($('#inputEmail').val(), $('#inputPassword').val(),{'keys':true});
  // Sign In
  //client.signIn(email, password);
  if (accountData.state==1) {
    client.signIn($('#inputEmail').val(), $('#inputPassword').val(),true,{'keys':true,service: 'sync'});
  }
  //console.warn(accountData);
  //console.log(accountData->_state);


  return false;
})

;

JS;
$this->registerJs($js);
?>


<!--

<script>
// Sign In for later
$(document).ready(function () {
$('#ident-form').submit(function(e) {
var client = new FxAccountClient('http://10.0.4.59:4000/api/');
// Sign Up
client.signUp('beta@test.com', 'aa');
// Sign In
//client.signIn(email, password);

return false;
});

}
</script>

$.validate({
modules: 'location, date, security, file',
onError: function() {
$("#playbutton ").toggleClass('active');
//alert('Validation failed');
},
onSuccess: function() {
//Spinner
$("#playbutton ").toggleClass('active');
//if ($('#forms').valid()) {
var client = new FxAccountClient("http://10.0.4.59/yii/basic/web/content/");
// Sign Up
accountData = client.signUp($('#inputEmail').val(), $('#inputPassword').val(),{'keys':true});

if (accountData.state==1) {
client.signIn($('#inputEmail').val(), $('#inputPassword').val(),true,{'keys':true,service: 'sync'});
}
console.warn(accountData);
console.log(accountData->_state);

//setTimeout($("#playbutton ").toggleClass('active'), 1000);
return false; // Will stop the submission of the form
}
});-->
