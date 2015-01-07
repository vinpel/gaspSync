<?php

namespace tests\codeception\_pages;

use yii\codeception\BasePage;

/**
 * Represents about page
 * @property \AcceptanceTester|\FunctionalTester $actor
 */
class CreateTokenPage extends BasePage
{
    public $route = 'tokenServer/1.0/sync/1.5';
}
