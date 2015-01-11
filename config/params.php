<?php
if(!function_exists('decistamp')) {
  function decistamp($time=null){
    if ($time===null){
      $time=microtime(true) ;
    }
    $time=number_format((float)$time, 2, '.', '')		;
    return $time;
  }
}

return [
  'adminEmail' => 'admin@example.com',
  'bsoList'  => ['keys','collections','clients','crypto','forms','history','meta','bookmarks','prefs','tabs','passwords','addons'],
  // without trailing slash
  'publicURI' => 'https://localhost:4000',
  'endPointUrl'=>'syncServer',
  'fxaVersions'=> [
    'SyncVersion'=>'1.0',
    'ProtocoleVersion'=>'1.5',
    'ContentVersion'=>'1',
  ],
    // Authorized issuer
  'assertionIssuer'=>['localhost','api.accounts.firefox.com','localhost'],
  'storagePath' =>[
    'wellKnowKey' => '@storage/well-known',
    'storageKey'=> '@storage/SSL/',
    'storageToken' => '@storage/secretToken',
    ]

  ];
