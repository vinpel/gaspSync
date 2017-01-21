<?php

//\Codeception\Util\Debug::debug($hawk);
$email=time().'test@exemple.com';
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure that the sync server works');

//get valid hawk / browserid for testing
$I->wantTo('grab an assertion');
$I->sendGET('/index-test.php/token/test-get-assertion',['email'=>$email]);
$assertion=$I->grabDataFromJsonResponse("assertion");


$I->wantTo('get an authToken');
$I->haveHttpHeader('authorization',$assertion);
$I->sendGET('/index-test.php/tokenServer/1.0/sync/1.5',array());
$I->seeResponseCodeIs(200);
$I->seeResponseContainsJson(['status' => 'okay']);

$uid=$I->grabDataFromJsonResponse("uid");
$api_endpoint=$I->grabDataFromJsonResponse("api_endpoint");
$id=$I->grabDataFromJsonResponse("id");
$key=$I->grabDataFromJsonResponse("key");




//Remove the user
$url=$api_endpoint.'/storage';
$I->wantTo('delete All user');
$I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'DELETE']);
$hawk=$I->grabDataFromJsonResponse("hawk");
$I->haveHttpHeader('x-confirm-delete',1);
$I->haveHttpHeader('authorization',$hawk);
$I->sendDELETE($url);
$I->seeResponseCodeIs(200);
$I->see('{}');
  //get infocollection

  $I->wantTo('get a valid hawk token for the new request');
  $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$api_endpoint.'/info/collections']);
  $I->seeResponseIsJson();
  $hawk=$I->grabDataFromJsonResponse("hawk");
  $I->wantTo('get info/collections and validate hawk');
  $I->haveHttpHeader('authorization',$hawk);
  $I->sendGET($api_endpoint.'/info/collections');
  $I->seeResponseCodeIs(200);
  $I->seeResponseIsJson();
  $I->see('{}');



    //test if meta/globals is empty
    $I->wantTo('get meta/globals');
    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$api_endpoint.'/storage/meta/globals']);
    $hawk=$I->grabDataFromJsonResponse("hawk");
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendGET($api_endpoint.'/storage/meta/globals');
    $I->seeResponseCodeIs(404); //response is not a true Json Response ...
    $I->see('0');


    //delete  1 bso(bookmarks)
    $url=$api_endpoint.'/storage/clients';
    $I->wantTo('delete storage/clients');
    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'DELETE']);
    $hawk=$I->grabDataFromJsonResponse("hawk");
    $I->haveHttpHeader('x-confirm-delete',1);
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendDELETE($url);
    $I->seeResponseCodeIs(200);
    $I->see('modified');





    //put data
    $putData='{"payload":"{\"syncID\":\"bytcsWxenUlh\",\"storageVersion\":5,\"declined\":[]}","id":"global"}';
    $url=$api_endpoint.'/storage/clients';
    $I->wantTo('Put storage/clients data');

    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'PUT']);
    $hawk=$I->grabDataFromJsonResponse("hawk");

    $I->haveHttpHeader('Content-Type', 'application/json');
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendPUT($url, $putData);
    $I->seeResponseCodeIs(200);




    //post data
    $PostData='[{"payload":"{\"ciphertext\":\"SzhUOiH13HelQQTzFIfZ+zOga0C72lPu3BNbrJR4dn2uVU2TjefO3qldM+9LmEJDEUjnt9khho+o4f3MWAdtMFMAzNe90zkMCaGGmYh+FACWoFj32tngwpN+1x0JoZyndmwjRwOBmsi6NeXtvhEiZp87PCkr6v+vbdYFwmeUE4S1/nTOnFY2JHeDAU9M9gZqbfS5USNQxyHYhTeZUWFQGQ/0Em3IHRY/6/x4o5g1D1umtUhv9+ON/HtuJKdKOudcGuZ8mRg/kfV2bTRFa8VlZWQYRxSQo4V3l6j2IoyotZ1x0JGFXYIgSPzBspVURMTI\",\"IV\":\"7oFLTJG0CSFGL6PriQskNw==\",\"hmac\":\"af983d882a609c7f470867cab97e1858efdb1ff885a375ccc1898ce3488632ee\"}","id":"menu","sortindex":1000000},{"payload":"{\"ciphertext\":\"ZRPh3SUDfP7g2kfY7ub4iANsLm5OXotLAPXPI/9kAGVSgsrHRIikIwpqPHY2Vq9CN5GOXczErBSd2acGmltd/d/SEtnUnDpDhyw5FshcXxxDxgJ4J+ROOuwO0t9GuIRHyYoiBloOXcRLrxu0+6NCvcamHu+rPxlK8PlbbXQbG8xUdU9vzdmlg+O+Z18pDArMJaxFFN88yTfJ0jnPaTfe3AzQbVClidRiEt1y//ZK46csfGz/hPxG7osDnnxv6btla5nCruDsK0g613/dyvuyMbHWsyJ2slsZOjSPwySMfE1jmCWhhTPfJ3+Dr/s9ckxwHV363RDqkYhp4NcRYjEF3w==\",\"IV\":\"fDCJD4Nmd9UkQalbdp+BNQ==\",\"hmac\":\"1cf12d9f5a6bb038a5ec41f54ccdbd840c0623d9826d2f83b723ad0c8810e674\"}","id":"toolbar","sortindex":1000000},{"payload":"{\"ciphertext\":\"7vY1S83kY1KiaIXX3aRpzdyjJbGj6a20fo99PYYMLnxC6pgBk+enFxWSxJiaGI/ZnoH5Uq34nLQ7gEKMvyuDe/lxrpRRRPc7v3D51PHHMq1dZOgNfBUtrLEnlWYANUntqbMgo42Q+pge8HL8VNii3H4KVF1QUSFiMJ0L66ULS/bD8gyXSdItQ3EKz8ZBn9vKU4jCFv0fJbW6AX61MM8t5bo2zSTF3aR64MK6lTv8mXwYUqEhj7IroYf1I93KHw/DJS+SeLHzfPhj2AsfjNCpmq5draFHeaY+TlX3Avb60s7QWBCkNXRIgcwr7SDCrjwcor2SIi8Fi/4fG1z1nJLzgB76C+TYlUrGcIw+E+RgPX9OBIA1/LJBfv1/B1vpJpZvyJGgcRmSp8gHktHpBHUMGPWg1mpmWwcNnlED9HHz8dDTJNZ4hNA5cUs9zJBFVn/9SWhOdtzh4JHBHBPfXYG0fg==\",\"IV\":\"d0TzpD++IH3cHxS85hbb+A==\",\"hmac\":\"5f4c1cacb75b55ba50256e87e0c22899ee7146394edd8068ab757864e515ef03\"}","id":"2D04FS4b1x5p","sortindex":0},{"payload":"{\"ciphertext\":\"IpZ964pwjylVDmjEUD2JFGDBT2PS6D12ll+TLOQqbzB5FzFRG5COfQBCxoC19cQKX7w0Z7t5KKHmFNC4S7DHfbwnObcTjLovGso1YA61h3CUUj2Y+Ka6OBF63HtPHjMv8Fk+ze2W0s5kSToMu13buBs4Bdv9Rkks4F78ar3iuzi+vh88vt/xw4k+c09BQK/UQrv74sL+bt4aH1sh+ndL8VGkyfT6qUjGyDyhRQ84Voa5ag/OP8VEcg5XW1hwg3ZAIWEXY/za7+C7LLQPmtGCnxaCB5aYtgE/w4dxl51tep6LMVbhQmwPTDVNMFxRLt7pXpXJ8haAET0mx3BSePeN73bdwSWETptuns4epADX6qA=\",\"IV\":\"uw2KGl7P6bxZt1yIPwaLYA==\",\"hmac\":\"607194ca6cc461ee3b02406af64e19badfef47870e6a94f9fba4dca2c41bfad9\"}","id":"AUtF0QUdokZO","sortindex":0},{"payload":"{\"ciphertext\":\"646UchJrb8xn8BgZ5mcvdoUaxzuD6Ts6Xi/9Q/sHhxLTYZAlcRFYdCNGflemN/DP04FaJR7dVfrUWLYpwGwANR5VR2KQcRl/8HAhavny3oxjoG/3XC6bFcdqCEO5GJYXJ3XP98DcK/BY8lQveJMR7g==\",\"IV\":\"xAQGIlQZ380fRUunwf/NRA==\",\"hmac\":\"68dd214fdd0b5321838a7f1ab0473af596d7f14c80f48f9da34c55b21358d7aa\"}","id":"-FfQJhtNZZAD","sortindex":0},{"payload":"{\"ciphertext\":\"//9DaRunMdujssnVkGiNig6LcovqCOj5K9+eQwYiVNKfZs2p5Q8/PrTCCn6INIeJco8w+uPR1/52NMmnNzqDNK/scSYdXPiTgEtks4wEcuiqu2RH3eyRBCmjT13LI/9lkn92NOuRZe/7kVV8B5NKcSgVBwCXwZgOTkysMXOMUg/xuAMKuTAZF975Hnwpq1vSne41tgiChpvQLSZPoIPGR5HwE54jdT7s+YbxrgdB7etTpe5jTxx1B2uA4POFm0L30mv9dHv0iDr0tumC5ZgXf7Yzl7bLnrPIxMhJfWOZJU8=\",\"IV\":\"E2gQQbqb1+BJNUylpj/uSw==\",\"hmac\":\"01632b42ce5f69e3db091faacbdb94de972bdc7c52d862dad637ab91fecd0b1e\"}","id":"YimXToZb0Ooq","sortindex":1000000},{"payload":"{\"ciphertext\":\"dRKpkYP/aXaRFoE6P9eRTxoolL2tuGOawWY8IchnoGY71vHKYvD93i+GdxtrX0ah0a3Lb9q2X1SX20q/BIiD5cOObZMc5xqS14/hQnC9Q6bcQnpa1QxbntiY/5G6v0+QH7OGR9jAXp0bwl/j8ZTunHyp11CWsFdFV+jNT0e7BdJoc6zu9FOJAddzoS0YizDyup8P4ht2stOvc9UwLR7PDmAzBH4gF1Vx4fYzIVXm8laV4hOt9TEYeDFnmzTXoqdF4sAvt6KhGdK8ZoUSgzzhCk586lkVoHoBW9egfOzRAg0L4xYktmJZH/sVdpOU55QQCEE/4WKPQ4u8bBeREYKjEQ==\",\"IV\":\"tzNytbslnuGFwRxnhwA3aA==\",\"hmac\":\"deacbdd70df8ac3e8bb7e461d4889f085b0601e9c22115b2504d272a6516ac06\"}","id":"0FqMoV6-B2iv","sortindex":122},{"payload":"{\"ciphertext\":\"MT7vIPWlHNUnwbEx4rCCqCwrZwp3crcFjDO5EpPJX+O8IP+7pvc8vf7sA6wRvsimT12buYrdFwT87gPtV/obelyF1SgFkV010p4vtjKNdyW/vTyxVziaTwuE5FIZcMh4jGSKYYd3iWh7bKVBtHzuDFBF2rG7BYu9xFRrbyo5DRJSZPIQ06dI8m9lCLyrgeU9JRE7/yGBmIi8f8DpShr0acHKEACjgwj+LLCgXKnRu1XzncvvGMr8AqyfhvsHIZChy9cwnetsyrGutzdqUk5krjGdmi8AL/rwuup5LBYcirNpETanu3EBg2qr4vWlegbggZdTJD+LUWp+GzIBltdyOA==\",\"IV\":\"HCue6wKAmD/I6DpC9dMT1Q==\",\"hmac\":\"325f9da5dee99b0c9c30b56eef4d96a878a67505aff165ba15b5aac41f21d3c8\"}","id":"_FtBn7cHHvNJ","sortindex":122},{"payload":"{\"ciphertext\":\"lD2ep5wQQ4x9sZtYYKJqu4v7JOuzOLUkFxDg3aIkU7yy4FF9N6s3y0/9TPHPBeSK7ITKA5lwWXSfPU6D551spZF7lj0Wki3Z7E5SzLkp8oEGEj3qO0VnaNBbuzgktHggO5W23ghD/gOPQh5faMb7bEa2yNUI3l9mwgNhZmQi8l93nqB9hUMEVuoJU8hosJHPM9s///s4J4gOF+k5MmCdFGBCfb8Puz1QXd7F3sxPwI9n2VGmNEw0ufxtF/1BfOIwP15Eg4KyuonCt5maKrg2R/2TRXbM1G5sx1cZfxi5hxxePMT+s2dP3wL3d0esIdyb\",\"IV\":\"7z0S6e+aaQOaJnySjTFqhg==\",\"hmac\":\"87dcdd89abce31e431c19f8224e8e984c8a01fd517053f04c87db9284670358e\"}","id":"9-W3GX1i74GL","sortindex":122},{"payload":"{\"ciphertext\":\"hchdNwB/+4hGznS50EceeXMd0yPgDAx/0WBzgaLoIPpTDu323mrdc+TSTc2IjN61JNlFic6Jo3n5G3u8UKOsEaTXeitT9dom+jc4CWeqKDfhvTbQHnlYHYuT4Pb5FW+1HkqwuB5QQO1YetZ0mcnwrnC/6pSpy58Qcac0tB3BiIl3zXIliMoG9n/QgOikHdHmmuzzLU9R8pQ+nYwKWldAH89gGOTMmSLYLrUjhgazeVq6DKHsX6ax7QedBAtl/3pxNgeYQJzCX5Sgu5EGUZSjTp7o2DAuR6aHSqXq+CMWkOYGsVBAD3xR6hMD7ijL9vub\",\"IV\":\"nJ12OmKXNvNTP1nCn/s0AQ==\",\"hmac\":\"6409d92146b870edc2bf73abb292705df2b65f0008a20357ac6bc1f7fdf2b097\"}","id":"_T_2DV3XRRvJ","sortindex":122},{"payload":"{\"ciphertext\":\"5Lbcl93KYGFlXsrZ/vbVFZQLsMRTmIuK78IkrpN6Qvf/rbo4odo98j4d08TzR8op4qD6MU8RMloYKsZzVXh9d3DwnrotO4SsNmiZfD6mcQkUwnLCFd4TmEJNd7ue7CEUkSrHGq0Fh69wJqIaWO6fQImSKE8mv/rD3IAGxXiwE+Vb0kUjiJOoczPFlGtBbjh+9loqO3hiPknawbq4PkhtA8BdWBgnAbaV3Xyqn3AAC9aUXycj+zvc7hGrr8D2LTm53FMeFApadZPvDj6pOF7aFUHQxH6/uuBz/c7L9S7G3Ydc39AbJwHSOGsf4uIjV307DUHzI5Lv223sabbZ54UzZtUtZu2BfY2t1CCvFtIp7ytN1JRTN9L/PKLgoMCayS7A\",\"IV\":\"2YZdEiX0cACC6egqxCjQnA==\",\"hmac\":\"2c08145a1658498e99f9ccb48f49c4d22dd235ad95846b49be6c29a8aa3a8ef5\"}","id":"XLTlhQ52l-Xx","sortindex":1000000},{"payload":"{\"ciphertext\":\"N1077VncABy3UaQEraBd8MJFkhUSyMBP/aAaVOUQZ+mePX0UDTJ0bQ7GAc4RHRjt47oR8UajEQ2h/EjmViOIQElk5k1+M/mbD0xQExjcDy8rQCqKGpe1KTrq/O3/yYRHAWieXgNc6AlPMP3XJx9Z7QL8XjiVJVYvYjJ8kugd1E6X0QdbZm+WDZP8SKEaN804zo38gdhlAX+r7z2bX0ep79xiUUOgrErJ23QutXnhJtJVf1l10PT2/ifL0bMUPS0WZhFq5Z5I0mDMGXvU/JRDKA==\",\"IV\":\"eB/UOm9V2lXaygPaBr/36Q==\",\"hmac\":\"1351371fb3145a2ebdf3dadf52050fac3e992dc640dff4baec59f81e8148d8cc\"}","id":"LPcggO4M3JGH","sortindex":0},{"payload":"{\"ciphertext\":\"fmFJN8kUii4vGGeYbXOQs5dKKfqH9YURAMk+h8CVDB7llSXHKn/DkxwTigZehB92qdczR5AwpU3nMOnT5iKA4gVGW+Xn3sE/ahff8wekW+s4mSLuvZVORym5Y7KGwMCPwa/F0ZkqsgRIIQni1tTg8Kevy3mHIhnZmU1Whz6/OITs/C2OTtSQ62wOlPSfxpAv/wmbxzy5PeDDXaLSD0oviPy9QEIqz/djBfABbL6Z4uJUZLFzTTrNCoUv8h081rFpj3O94KvGIiHtAe1oGqW/8/STYPWri4Tc0mR9vPBNmkmXVt+//nTj5ey2qmIQvx/W\",\"IV\":\"E30qPScHrqpHglcZ8AY+Jg==\",\"hmac\":\"9aedef04b426bf9a984f2fc01dac9fd8f333bec06496e1bbf2124c45a6a4530c\"}","id":"Ne6biVNSjFyq","sortindex":0},{"payload":"{\"ciphertext\":\"k5PlGpSxBQXnnsG045q24h4qFBoE0Zghh9WNbGPF2FjAamBlCru2LJdnGjA6ulE9GeVS0zilhAjwRgyKNhz5+0lKlnRNOFM6v4tu/L5kguViHPx9Ts0ftV39orFCTlVf1/CR1V1hWWAGeB7xkbND2JRWMYIAYlsSBdaaii3EtNQLc6+VmNTOGi3FcZNNT3zkDMAGGUwsMw2AF/sFHGatglJuhSFK09r5iOWWm3CkeiSBAx4uFpD+sv19VQ1mH88FdvNMQbDlyFXsmJH/sl/rC+SjlTQtFBWzpVEKOCZp76H5GoWawDoRSubpnAk2bciiadrWx+SAYl0RyACC93oqyQ==\",\"IV\":\"0Tl989ltYWEWFClccMpEzw==\",\"hmac\":\"889cdee5718a21938e506edf5c03a980e5d7d395c372d6e195c7a35d61d24a3d\"}","id":"g6tu-B3BF9CI","sortindex":0},{"payload":"{\"ciphertext\":\"1z7ZvCZi5Ojz7QUQVtvEk1GvzwWntgitj/Ze0MIdVH7RIMH8ATRNZjkLP6tiv08JA58SVnruCQ7Oe+vCn7Gr/dV72Zna/HjacX/AuMBEyHyDiNopBUGnt6NoxCAZADnYs4H8YhyBURL/m5x70r8wM/+fyRBtD8bmv8N4Pt2x4txhZwLvRUcefroG1J4JsFsub9PkoxtUsmaRNIXHWXDpBKRZL9WauNvxVGt07yViUc4Nhlcq077Cjm37+DGzafYXLDr41KBFxk/9nOBDUMZfWw==\",\"IV\":\"sMpFAqjfdm8UjlXMlC42Zg==\",\"hmac\":\"d697744d99ec023a12975c35504731ced73a7b7427fa3e887a6cac46a9161c8c\"}","id":"Lw2bkqcmWbSm","sortindex":0},{"payload":"{\"ciphertext\":\"+ZHpe5ynlW4j/5yQcoc104E0ACnrpPP2s934kTxdSdGkJkeNGXlFDf9nuvhbCfvxVuu6otTh44im5cnH3hGsh7urZnWNncrCOBk6ufUBNmDFIvNEE/bKkKcnK1wLLT/e7Etewz5KrXhviNl/LBuR0n5hvi1BXtfLXqCn97p4tQYyGCvhsQ68yWCXH8LuVXp7hcSmNY4JowJNUQrpArEEDgv7BcNGBWXa8OJq1ZtMkVzi4WfUrKJBaoppmKQSO8/zH3oN35M61DtY95/2DTImkyegaT9qKm8wqmv5QZx+p9I=\",\"IV\":\"tEzM5NHSH4Yg2RRRbyM3Yw==\",\"hmac\":\"a2ff5a0547658509f703a40e61f262a57e83e1ba1b65a74792fb0ee126bc03dc\"}","id":"nWeEXL24AIP8","sortindex":0},{"payload":"{\"ciphertext\":\"6qa+kRhox3wonrjh5kUkKubcm+23dZB7nsKLIeR7Fdusexdde34Pno1Qvp26HbnTLsRzoIkklVfJgQfwZoGyTI/EysP+sLug56KQ4FfrFZxqkODtsAmfuRLT8RwBWmu5sI1lbOQsA7BO5rWKzQmLfCVkK0ioVOLJzhCw68SwULrNwgpSHd81SgW2km6q6kiBIbjywnYcCeSk8qjgwNh+4/4BQ9psQysvQwagaE7iARlNE02jeTVsisFCowS2JbBZ4SloRAoAbYGZOcaXu3HmtplbjGyvLmXS1uU1JX1Tvis=\",\"IV\":\"b9kikt5QnfYnt/kbhbfg7A==\",\"hmac\":\"efd99ee57471fce940fc8add43e3a38ddbfe67b3ba078c11312e56fdf9cef9f5\"}","id":"iRF-L_JT2W7Y","sortindex":0},{"payload":"{\"ciphertext\":\"PJYHSq6bBH3yRdO4kYuORtQHPjjbqQBws1Xts2fqJ027nFFOgka9CQgF4Wp3qN0TNSbSIeQ7TIBAcMe7vw/rOvOEIR9daGpzmfMk+CVlxQfFYfKTF2PuYvKG+ZRiW+a2oWx3w+/9xc+unsrxvn3PJ09EqblPyDOw/tgUkXumFXJC+nkMNH8aB0oJWqyG63PPwy5V4+4zuJgoXBqAABZ+FUoaqcJaxm0mONTXcR24awLumXH3aKOPdXG7ihQ67jqKmuslnpMCWS0LGanNLSiH1WTnIZt3o03JX2S44fW3U6o=\",\"IV\":\"q+xKGi0NcepLpneeWTH94w==\",\"hmac\":\"da5af94a5a25d60fced2429f1eb5519a748de9e681e0023c83f0e093ad81f93e\"}","id":"nRGWj3n6RgpR","sortindex":0},{"payload":"{\"ciphertext\":\"IavOMEGVjwbh/Tb11M8JcYM+G5MoncqeEJjHawoWuPPdwu6FgTrME5Bas234IXkH9/UC2d3utH2ph5rw8Z9/E+Jyqrz9Vw9bblgYAib3JgQ/jwLRei8Ax7EfLP5NB2m+dwwSxmuLIqe/K4wMzffdt4OMZre5dLxFcsJcIC5r7E5yqPzPSTovbJ6w4g5yTdF06yp2MLehhRGQf3vVQKXZEoUpUFRnuAWlIb+9zdOpsTQaNwmfNzgB+dK9ZX47u2k6\",\"IV\":\"wloQS2gSv4Z0u3RU9wYWsA==\",\"hmac\":\"f99b52975e7a439c0e58f4b3481313b6ce8a6cb4be5e940477bfb3aed7dee0da\"}","id":"7OnowJVBByRl","sortindex":0},{"payload":"{\"ciphertext\":\"HNtzsjOGg6ly6jGkK9G7rD9jr3Lkb1uIRDcKq0FrYRw2+/0kQYrN285zUMqmljmoetQep8TjUHlHZ1rJN5pJ+38Al22BDBqY7++H6ETOto6EPqnA+1MTALHuh+Z6sNTuYxWt9ZNnKTCaYOofqFIBncVndiNyZfZe6XaI0iTPDuYSg/Ed3JZGJJmwpWTGIOjgjPRVyHy3WVgD7Hik9EfWrPDcljG/lCFYUtOIGwroy2hnizsKP1cMlj6v8U7HYQkA\",\"IV\":\"O1pdaAsyJX6WKJbn6okRkg==\",\"hmac\":\"01ba35a003536d7fcbd504544bcbf5967b4de3ee6336f320ae830748b0657135\"}","id":"CBI1HlVrjDw3","sortindex":0},{"payload":"{\"ciphertext\":\"JJwcXFwzfSqK4nWG/LvE74BBLOq2asFwFBqT/8JdiJaYChJHeX4ccrcvEehD9kFXT5yIqN7oY0BN+9Cj1ajQUXijXzMKA/wcMsRdl7huJE5WpHsOV8c789kR48y68Er/Veujm4RrndhZ9uZtw4U3xreXpMnbbJyaxuXNtCHTfx/EhA39yy6Mqf1Nf+nIBBv/l/LH6ZccDaMq/cEuxPev8SQ4dKk9kuU5C/MJ+0kHmM1Xrv4G8ZMbvUCMIFEC6uIa7Se4Miri2aUOyHU5b/SB9FlYasIURr+xDMJcvnI1QXWiv+pEbj8EXDJfWpuJlD2pndXhpWmeSoAHXL+9iS9GQVXOPmtgc3ToeQFmPXxBsX54YfzMZS9rJyRXl3psDU0As+1LO3BHs11iuP8McVRKJY+QErqccWW6WXOIPiz0nN4SIgpky9mDoOl+H0NZytgAx6xziiAxmFDA4VaODgj+H13/RWSq0cbm3GjzpVpI/d8=\",\"IV\":\"vxzdCbkyzY2Y8+bdlyuYPQ==\",\"hmac\":\"d61b75d7ba68cacd94b0842b1e2ccd04fe54102d501e0ce69b4e363692db69f7\"}","id":"pJnnj06QM-SA","sortindex":122},{"payload":"{\"ciphertext\":\"4ccf78f2Koc6gZuSUFetMAuwa7YcSPsHSOQS3spj3hI74Bum4hU4uyGu94h4xP/t5jsHOqtNVN64RJD0KPdaG4gnXiehJHh+S4tZvIrLMJ3ZTHaMuNOdCmg56DPSysYJLWSFT6NYHHWv13zSYsA/AJfkXeiIjF/U6jY4LtUC6WTNdG+Uhyx1j/12jFApNtqZHB/yI6G/XqTDdPw1IRncCkI9oua4CwP2Vkjj3Dy/mS7aHpUIeiQTUgPo/b1QIACCegSgX0U2Z4+UZ6b3zAQ2qFI8TNw9ww1R87v3TNK50Z8=\",\"IV\":\"SkfJVN2Dy6Mj7xyz9u7lZg==\",\"hmac\":\"84cd90846456ce58264ed57de2f5e2828f5fdc4b553b8d450e3f5dbe0b6304ba\"}","id":"PpTZ-WXRT_Wm","sortindex":340},{"payload":"{\"ciphertext\":\"gbktjBNgi6AU9eJ8J8jZ0XDhvv2GyGQQS0zWJoF7pK/qt8F8pRX+9o1wG69MQUY7F2hgATUa5F7DatBS95VAp399u7Z84pwvH9x0ohUKNZbTudPgZ8SjmMgePlSSf7e4Jg7JD7dbBuoKFtXEmMrMlmSSm2tBuxMMsVt1+Oz1G9Ck0ItyZoF9FMcp4WvvHcpRMKaCGnIeqJgKjYUlre3AQPd7cn8bIzteVl+o68dJ6E0xEtFBxrQ2Zs81ViKBU9Uw+zEa/7LUPpYa24AotZLAyCZFLkfJREz4fIVn0/0ZTGiSN9yYvU2CF4i1NoZoIrtwYFAsy1gGk+dNzlRRWBIr6w==\",\"IV\":\"41IpWKBWOzI2gKNUVl5Pjw==\",\"hmac\":\"bea82436574fdf8a1cba315e8bb89c69f86c5ac8388c900edeeec9ec545a6222\"}","id":"1pAXT4Ssg4z6","sortindex":150},{"payload":"{\"ciphertext\":\"w3ACB5hRzCOAatkvewheqzw/+nwFUzqScIbHaytev2ljO7tR0e/+SWqFt3iW19p5h54XkoL7YCjRUroqIxa8oK/aeqttwf5UXwe3TBl3oVBL7A2fDkzUVMg/voR3pFB/OdDPIuOLk0ktpeBxNoVr/DotFUBAumiICBiZLRsWYG58IwiuGGX5g26G1NEItim6OPGp6ZBNgc38D/zpC5xCspDchDTb1x7Fmn+0TMKGH51HABKXAceOp8Yd92KyOfnNQik1blg+Ara4zpVGH1oni2LwIevXBfAI5QLOzR+AksCjg1+z74NGTyM6xrpnL6mMq1HYlgRcRfQ2d1ho0hp6UQ==\",\"IV\":\"x+6Dn18oZOG+8ZNZpJsy1g==\",\"hmac\":\"b881f0bf1e8dc8ad7f810ca65787f46a688ee014f0b3877937e0f9e15b485c67\"}","id":"4dSD_xjmalpV","sortindex":272}]';
    $url=$api_endpoint.'/storage/clients';
    $I->wantTo('Post storage/clients data');
    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'POST']);
    $hawk=$I->grabDataFromJsonResponse("hawk");
    $I->haveHttpHeader('Content-Type', 'application/json');
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendPOST($url, $PostData);
    $I->seeResponseCodeIs(200);
    $I->see('"failed":[]');


    //get data, we test the new line Json delimited format
    $url=$api_endpoint.'/storage/clients';
    $I->wantTo('Post storage/clients data');
    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'GET']);
    $hawk=$I->grabDataFromJsonResponse("hawk");
    $I->haveHttpHeader('Content-Type', 'application/json');
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendGET($url);
    $I->seeResponseCodeIs(200);
    $NewlineDelimitedJSON=$I->grabResponse();
    $res=explode("\n",$NewlineDelimitedJSON);
    array_pop($res);  //dernière ligne vide
    $noerror=true;
    foreach ($res as $uneligne){
      $jSonObject=json_decode($uneligne);
      if (json_last_error()!==JSON_ERROR_NONE || $jSonObject===null || !property_exists($jSonObject,'payload')){
        $noerror=false;
      }
    }
    $I->assertTrue($noerror);

    //Remove the user completly
    $url=$api_endpoint.'/storage';
    $I->wantTo('delete All user');
    $I->sendGET('/index-test.php/token/test-get-hawk',['id'=>$id,'key'=>$key,'url'=>$url,'verb'=>'DELETE']);
    $hawk=$I->grabDataFromJsonResponse("hawk");
    $I->haveHttpHeader('x-confirm-delete',1);
    $I->haveHttpHeader('authorization',$hawk);
    $I->sendDELETE($url);
    $I->seeResponseCodeIs(200);
    $I->see('{}');
      ?>
