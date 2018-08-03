<?php

namespace Hokan22\LaravelTranslator\Tests;

use Hokan22\LaravelTranslator\Translator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Unit tests for the [Hokan22\LaravelTranslator\Translator] class.
 *
 * @package App\Libraries\Translator\tests
 */
class TranslatorTest extends TestCase
{

    public function test() {
        $this->assertTrue(true);
    }


    public function testCanBeCreatedFromValidLocale() {

        $this->assertInstanceOf(
            'Translator',
            $Translator = new Translator()
        );

        return $Translator;
    }

    /**
     * @depends testCanBeCreatedFromValidLocale
     */
    public function testCannotBeCreatedFromInvalidLocale($Translator) {

        $this->expectException(NotFoundResourceException::class);

        $Translator->setLocale('invalid!');

    }

    /**
     * @depends testCanBeCreatedFromValidLocale
     */
    public function testTranslatorAttributesAreValid($Translator) {

        $this->assertObjectHasAttribute('aHandler', $Translator);

        $this->assertObjectHasAttribute('config', $Translator);

        $this->assertObjectHasAttribute('locale', $Translator);

    }


    /**
     * @depends testCanBeCreatedFromValidLocale
     */
    public function testTranslatorSetLocaleCreatesNewHandler($Translator) {

        $Translator->setLocale('en_US');

        $this->assertAttributeEquals('en_US', 'locale', $Translator);

        $aHandler = $this->getObjectAttribute($Translator, 'aHandler');

        $this->assertCount(2, $aHandler);

        $this->assertArrayHasKey('en_US', $aHandler);

        $Translator->setLocale('de_DE');

        $this->assertAttributeEquals('de_DE', 'locale', $Translator);

        $aHandler = $this->getObjectAttribute($Translator, 'aHandler');

        $this->assertCount(2, $aHandler);

        $this->assertArrayHasKey('en_US', $aHandler);
        $this->assertArrayHasKey('de_DE', $aHandler);

    }

    /**
     * @depends testCanBeCreatedFromValidLocale
     */
    public function testTranslatorLocaleValidation($Translator) {

        $locale = $Translator->validateLocale('en');
        $this->assertEquals('en_US', $locale);

        $locale = $Translator->validateLocale('de');
        $this->assertEquals('de_DE', $locale);

        $this->expectException(NotFoundResourceException::class);
        $Translator->validateLocale('gr');

    }

    /**
     * @depends testCanBeCreatedFromValidLocale
     */
    public function testTranslatorTranslation($Translator) {

        $translation = $Translator->translate('test');
        $this->assertEquals('&lt;test:de_DE&gt;', $translation);
    }
}
