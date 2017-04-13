<?php
class HomeCest
{
    public function _before(\FunctionalTester $I)
    {
        $I->amOnRoute('/');
    }

    public function LandingPageWork(\FunctionalTester $I){
        $I->see('Self Hosted mozilla Sync 1.5', 'h3');

    }

}
