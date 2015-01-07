<?php

return [

  //testVectors
  'email'=>'andré@example.org',
  'password'=>'pässwörd',

  //client stretch-KDF
  'vect_email'=>'616e6472c3a9406578616d706c652e6f7267',
  'vect_password'=>'70c3a4737377c3b67264',
  'vect_authPW'=>'247b675ffb4c46310bc87e26d712153abe5e1c90ef00a4784594f97ef54f2375',


  //(normallyrandom)
  'vect_authSalt'=>'00f0000000000000000000000000000000000000000000000000000000000000',
  'vect_bigStretchedPW'=>'441509e25c92ee103d5a1a874e6f155df25a44d06e61c894616c9e85181dba97',
  'vect_verifyHash'=>'a4765bf103dc057f4cf4bc2c131ddb6716e8a4333cc55e1d3c449f31f0eec4f1',

  'vect_reqHMACkey'=>'9d8f22998ee7f5798b887042466b72d53e56ab0c094388bf65831f702d2febc0',
  'vect_tokenID'=>'c0a29dcf46174973da1378696e4c82ae10f723cf4f4d9f75e39f4ae3851595ab',
  'vect_wrapwrapKey'=>'3ebea117efa9faf57ce195899b2905058368e7760cc26ea58a2a1be0da7fb287',


  'vect_kA'=>'202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f',
  'vect_warpkB'=>'7effe354abecbcb234a8dfc2d7644b4ad339b525589738f2d27341bb8622ecd8',

  'vect_kB'=>'a095c51c1c6e384e8d5777d97e3c487a4fc2128a00ab395a73d57fedf41631f0',
  'vect_keyFetchToken'=>'808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9f',

];
?>
