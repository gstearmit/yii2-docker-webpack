<?php

namespace app\tests\functional\admin;

use yii\helpers\Url;
use app\tests\fixtures\UserFixture;

class LoginCest
{
    protected $formName = 'LoginForm';
    protected $formId = '#login-form';

    // @codingStandardsIgnoreFile
    public function _before($I)
    {
        $I->haveFixtures([
             'user' => UserFixture::class,
        ]);
        $I->amOnRoute('/admin');
    }

    public function openPage($I)
    {
        $I->see('Login');
    }

    public function testEmptyFields($I)
    {
        $I->submitForm($this->formId, []);
        $I->expectTo('see validations errors');
        $I->see('Password cannot be blank', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testEmptyUsername($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[password]' => 'test_password'
        ]);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testEmptyPassword($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'test_username'
        ]);
        $I->expectTo('see validations errors');
        $I->see('Password cannot be blank', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testTooShortPassword($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'test_username',
            $this->formName . '[password]' => 'test',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testBlockedUser($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'user-3',
            $this->formName . '[password]' => '123123',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Your account has been suspended', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testDeletedUser($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'user-4',
            $this->formName . '[password]' => '123123',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Your account has been deleted', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testWrongFields($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'test@example.com',
            $this->formName . '[password]' => 'testpassword',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testWrongUsername($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'test_username',
            $this->formName . '[password]' => 'fghfgh',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testWrongPassword($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'superuser',
            $this->formName . '[password]' => 'test_password',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password', '.help-block-error');
        $I->dontSee('logout');
    }

    public function testSuccess($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'superuser',
            $this->formName . '[password]' => 'fghfgh',
        ]);
        $I->see('Exit');
        $I->see('Welcome! / Control Panel');
        $I->dontSee('login');
        $I->dontSeeElement($this->formId);
    }

    public function testLogout($I)
    {
        $I->submitForm($this->formId, [
            $this->formName . '[username]' => 'superuser',
            $this->formName . '[password]' => 'fghfgh',
        ]);
        $I->sendAjaxPostRequest(Url::toRoute('/admin/index/logout'));
        $I->seeResponseCodeIs(302);

        $I->amOnRoute('/admin');
        $I->seeElement($this->formId);
    }

    public function testEnterToPageIndexWithNoPermission($I)
    {
        $I->amLoggedInAs($I->grabFixture('user', 'user-2'));
        $I->amOnRoute('/admin');
        $I->seeResponseCodeIs(403);
    }

    public function testEnterToPageWithNoPermission($I)
    {
        $I->amLoggedInAs($I->grabFixture('user', 'user-2'));
        $I->amOnRoute('/admin/settings');
        $I->seeResponseCodeIs(403);
    }
}
