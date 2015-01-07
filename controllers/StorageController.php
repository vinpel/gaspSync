<?php

namespace app\controllers;
/**
* Storage client
*/
use app\models\Token;
use app\models\FxaError;
use app\models\SyncUserCollections;
use app\models\SyncCollections;
use app\models\SyncUser;


use Hawk;
class StorageController extends \yii\web\Controller
{
  /**
  * No Csrf validation, please don't add "form" in this controller
  */
  public $enableCsrfValidation = false;
  /**
  * user_id grabed from the hawk data
  */
  private $user_id= null;


  /**
  * the constructor :
  *  - implement the hawk verification
  *  - check the user_id in url and in Hawk
  */
  public function __construct($id, $module, $config = []){

    $myToken=new Token();
    try{
      $this->user_id=$myToken->verifyHawk();
    }
    catch(\Exception $e) {
      return FxaError::fxaAuthError(110,'construct :'.$e->getMessage());
    }
    //did the user_id in the assertion match the Url ?
    if ($this->user_id<>\Yii::$app->request->get('id')
    //when a DELETE ALL statement is issued we don't have the user_id in the url, so no errors
    && (strcmp(\Yii::$app->request->pathinfo,\Yii::$app->params['endPointUrl'])==0
    && strcmp(\Yii::$app->request->getMethod(),'DELETE')==0)
    ){
      throw new \yii\web\UnauthorizedHttpException('Mismatching Userid '.$this->user_id.'/'.\Yii::$app->request->get('id'));
    }
    return parent::__construct($id, $module,$config);
  }
  /*
  Return in Newline-delimited JSON a BSO collection

  X-Weave-Next-Offset
  This header may be sent back with multi-record responses where the request included a limit parameter.
  Its presence indicates that the number of available records exceeded the given limit.
  The value from this header can be passed back in the offset parameter to retrieve additional records.
  The value of this header will always be a string of characters from the urlsafe-base64 alphabet.
  The specific contents of the string are an implementation detail of the server, so clients should treat it as an opaque token.
  */
  public function actionGetBso($bso){
    //Invalide BSP
    if (!in_array($bso,\Yii::$app->params['bsoList'])){
      \Yii::$app->response->setStatusCode(400);
      $data['status']='failure';
      $data['reason']='Unknow Collection';
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $data;
    }

    //did the user_id in the assertion match the Url ?
    if ($this->user_id<>(int)\Yii::$app->request->get('id')){
      \Yii::$app->response->setStatusCode(401);
      $data['status']='failure';
      $data['reason']='Wrong userId';
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $data;
    }
    $argFull=\Yii::$app->request->get('full');//null si non set
    $arguLimit=\Yii::$app->request->get('limit');
    $arguNewer=\Yii::$app->request->get('newer');
    $data['modified']=decistamp();
    $className='\app\models\Storage'.ucfirst($bso);
    //    $bsoGrp= new $className();
    //We call theright class create based on the name the bso
    if ($arguNewer!==null){
      $bsoGrp=$className::find()
      ->Where([ 'user_id'=>$this->user_id])
      ->andWhere(['>','modified',$arguNewer])
      ->All();
    }
    else{
      $bsoGrp=$className::find()
      ->Where(['user_id'=>$this->user_id])
      ->All();
    }
    if (count($bsoGrp)==0 && strcmp($bso,'meta')==0){
      header('X-Weave-Timestamp: '.$data['modified']);
      \Yii::$app->response->setStatusCode(404);
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return 0;
    }
    // parcours de tout les enregistrements
    $data=array();
    $result='';
    foreach($bsoGrp as $uneLigne){
      $data['id']=$uneLigne->bso_id;
      $data['modified']=$uneLigne->modified;
      $data['payload']=$uneLigne->payload;
      $data['sortindex']=$uneLigne->sortindex;
      $result.=json_encode((array)$data)."\n";
    }
    header('X-Weave-Records: '.count($bsoGrp));
    // we want to have {} on an empty array
    if (count($data)==0){
      $data=new \StdClass();
    }
    $lastModified= $this->_updateLastAccess($this->user_id,$bso,null,false);
    $lastModified=0;
    header('X-Last-Modified: '.$lastModified);
    header('X-Weave-Timestamp: '.decistamp());
    // Newline-delimited JSON content-type
    header('Content-Type: application/newlines; charset=UTF-8');
    return $result;
  }

  /**
  *  Manage one Bso, PUT method
  */
  public function actionPutBso($bso){
    if (\Yii::$app->request->headers->has('X-If-Modified-Since')){
      $epochLastModified =(float) \Yii::$app->request->headers->get('X-If-Modified-Since');
      $needUpdate=SyncUserCollections::Find()->Where(['user_id'=>$this->user_id,'collection'=>$bso])->One()->max('lastUpdate');
      //if the lastUpdate defined & inf. to "modied since"
      if ($needUpdate->lastUpdate!=null ){
        if ($epochLastModified >= $needUpdate->lastUpdate){
          \Yii::$app->response->setStatusCode(304);
          return "";
        }
      }
    }
    $rawBody=\Yii::$app->request->rawBody;
    $jSonObject=json_decode($rawBody);
    //did we have a valid Json object ?
    if (json_last_error()!==JSON_ERROR_NONE && $jSonObject==null){
      throw new \exception ('invalid Json Object');
    }
    $modified=$this->updateOneBSO($bso,$jSonObject);
    if ($modified===false){
      \Yii::$app->response->setStatusCode(400);
    }
    header('X-Last-Modified:'.$modified) ;
    header('X-Weave-Timestamp:'.$modified) ;
    return $modified;
  }

  /*
  * Update all BSO present in the body
  *
  * Two input formats are available for multiple record POST requests, selected by the Content-Type header of the request:
  *
  * application/json: the input is a JSON list of objects, one for for each BSO in the request.
  * application/newlines: each BSO is sent as a separate JSON object followed by a newline.
  *
  * For backwards-compatibility with existing clients, the server will also treat text/plain input as JSON.
  */
  public function actionPostBso($bso){
    $rawBody=\Yii::$app->request->rawBody;
    $listBSO=json_decode($rawBody);
    if (json_last_error()!==JSON_ERROR_NONE && $listBSO==null){
      throw new \exception ('invalid Json Object');
    }
    //Same modified time  for all the bso in the same request
    $modified=decistamp();
    //this order give final order
    $data['failed']=array();
    $data['modified']=$modified;
    $data['success']=array();
    //We will update the cumul in sync_user_collections after all the insert, not necessary
    foreach ($listBSO as $jSon){
      if ($jSon !=null){
        //we don't update the number of bso for the collection, only after all bso manipulation.
        if ($this->updateOneBSO($bso,$jSon,$modified,true)===false){
          $data['failed'][$jSon->id]=array('no need of update');	//BSO have been transmited but don't need an update.
        }
        else {
          $data['success'][]=$jSon->id;
        }
      }
    }
    $this->_updateLastAccess($this->user_id, $bso,$modified);

    header('X-Last-Modified: '.$modified);
    header('X-Weave-Timestamp: '.decistamp());
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $data;
  }
  /**
  * Update one BSO for one user
  */
  private function updateOneBSO($bso,$jSonObject,$modified=null,$noLastAcces=false){
    //if null passed, the current time is used
    // ofr multiple update we want the same modified time so we pass a specifical modified timestamp time
    $modified=decistamp($modified);
    //We call the current Bso if exist, otherwhise we create a new one
    $className='\app\models\Storage'.ucfirst($bso);
    $bsoPut = $className::find()
    ->Where(['user_id'=>$this->user_id])
    ->andWhere(['bso_id'=>$jSonObject->id])
    ->One();
    if ($bsoPut===null){
      $bsoPut = new $className();
      $bsoPut->user_id=$this->user_id;
      $bsoPut->bso_id=$jSonObject->id;
    }

    //need an update ?
    //comparaison of float can't be done with "<" we use bccomp  to have the correct answer
    if (!property_exists($jSonObject,'modified')                  // new BSO
    || bccomp($bsoPut->modified,$jSonObject->modified)==-1)   // deprecated bso in database
    {
      \Yii::info('Updating bso : '.$bso.' id : '.$jSonObject->id);
      //sortindex is defined ?
      if (isset($jSonObject->sortindex)){
        $bsoPut->sortindex=$jSonObject->sortindex;
      }
      else{
        $bsoPut->sortindex=0;
      }
      //ttl is defined ?
      if (isset($jSonObject->ttl)){
        $bsoPut->ttl=$jSonObject->ttl;
      }
      else{
        $bsoPut->ttl=$modified+3600;
      }
      $bsoPut->modified=$modified;
      $bsoPut->payload=$jSonObject->payload;
      $bsoPut->payload_size=strlen($jSonObject->payload);

      //we save the new Bso
      if (!$bsoPut->save()){
        \Yii::warning($bsoPut->getErrors());
      }
      //Update the sync_client table
      //during mass update, we don't update each time the last acces in syncUserCollection
      if ($noLastAcces===false){
        $this->_updateLastAccess($this->user_id, $bso,$modified);
      }
      //and return the modified time stamp (not used in POST multi update/insert)
      return $modified;
    }
    // no modification have been done
    return false;
  }
  /**
  * update the SyncUserCollections for the user_id / collection
  * modified  : timestamp to be inserted in the DB
  * isUpdated : by defautle we update the last Acces & lastUpdate
  */
  private function  _updateLastAccess($user_id,$collection,$modified=null,$isUpdated=true) {
    //last Access time
    if ($modified!==null){
      $modified=decistamp($modified);
    }
    else{
      $modified=decistamp();
    }
    $utilisateur=SyncUserCollections::Find()
    ->Where(['user_id'=>$this->user_id])
    ->andWhere(['collection'=>$collection])
    ->One();
    if ($utilisateur===null){
      $utilisateur=new SyncUserCollections();
      $utilisateur->user_id=$user_id;
    }

    if ($isUpdated===true){
      $utilisateur->lastUpdate=$modified;
      //Get number of record for a set collection
      $className='\app\models\Storage'.ucfirst($collection);
      $utilisateur->nb_records=$className::find()
      ->Where(['user_id'=>$this->user_id ])
      ->count();
    }
    $utilisateur->collection=$collection;
    $utilisateur->lastAccess=$modified;
    //Execute the save option
    if (!$utilisateur->save()){
      \Yii::warning($utilisateur->getErrors());
    }
    return $utilisateur->lastUpdate;
  }

  /**
  * return collection last Update for the user
  * https://docs.services.mozilla.com/sync/index.html
  * X-If-Modified-Since -> response 304
  * https://docs.services.mozilla.com/sync/lifeofasync.html
  * 401 or 404 response, the client should interpret this as credentials failure
  */
  public function actionInfoCollections(){

    \Yii::info("user_Id user for infoCollection:".$this->user_id);
    /*
    If the client has synced before, it should issue a conditional HTTP request by adding an X-If-Modified-Since header to the request.
    If the server responds with a 304, it means that no modifications have been made since the last sync
    */
    if (\Yii::$app->request->headers->has('X-If-Modified-Since')){
      $epochLastModified =(float) \Yii::$app->request->headers->get('X-If-Modified-Since');
      $utilisateur=SyncUserCollections::Find()->Where(['user_id'=>$this->user_id])->max('lastUpdate');
      //create a user if needed, update access time
      if (count($utilisateur)>0 && $epochLastModified >= (float)$utilisateur->lastUpdate){
        \Yii::$app->response->setStatusCode(304);
        return;
      }
    }
    if (!isset($utilisateur) || $utilisateur === null){
      $utilisateur=new SyncUserCollections();
    }
    $utilisateur->user_id=$this->user_id;
    //$utilisateur->touch('lastAccess');

    // Recupération de la dernière date de mise à jour.
    $sync_user_collections=SyncUserCollections::find()->Where(['user_id'=>$this->user_id])->All();
    $data=array();

    $nb_records=0;
    $maxLastModified=0;

    foreach ($sync_user_collections as $coll){
      $data[$coll->collection]=$coll->lastUpdate;
      $nb_records=$coll->nb_records;
      if ($maxLastModified<$coll->lastUpdate){
        $maxLastModified=$coll->lastUpdate;
      }
    }
    header('X-Weave-Records: '.$nb_records);
    header('X-Last-Modified: '.decistamp($maxLastModified));
    header('X-Weave-Timestamp: '.decistamp());
    /*
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Max-Age: 1728000');
    header('Access-Control-Allow-Headers: DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization,X-Conditions-Accepted');
    */
    \Yii::info('Nb data '.count($data));
    \Yii::info('User'.$this->user_id);
    //if (count($data)>0)
    /*foreach ($data  as $key=>$val){
    $data[$key]=(float)$val;
  }*/
  if (count($data)==0){
    $data=new \StdClass();
  }
  \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
  return $data;

}

/*
* Deletes all collecitons for a specified user.
*/
public function actionDeleteAll() {

  if (!\Yii::$app->request->headers->has('x-confirm-delete',1)){
    throw new \exception ('no delete header confirmation');
  }
  $listCollection=\Yii::$app->params['bsoList'];
  foreach ($listCollection as $bso){
    $className='\app\models\Storage'.ucfirst($bso);
    $className::deleteAll([ 'user_id'=>$this->user_id]);
  }
  SyncUserCollections::deleteAll([ 'user_id'=>$this->user_id]);

  SyncUser::deleteAll('user_id=:param',[':param'=>$this->user_id]);

  \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
  return new \StdClass();
}
/**
* delete a single collection
*/
public function actionDelete($bso) {
  if (!in_array($bso,\Yii::$app->params['bsoList'])){
    throw new \exception ('Faked delete bso :'.$bso);
  }
  $className='\app\models\Storage'.ucfirst($bso);
  $className::deleteAll([ 'user_id'=>$this->user_id]);

  SyncUserCollections::deleteAll([ 'user_id'=>$this->user_id,'collection'=>$bso]);
  \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
  $tbl['modified']=decistamp();
  return $tbl;
}

}
