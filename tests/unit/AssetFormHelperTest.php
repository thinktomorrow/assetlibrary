<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Thinktomorrow\AssetLibrary\Tests\TestCase;
use Thinktomorrow\AssetLibrary\Utilities\AssetFormHelper;

class AssetFormHelperTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_return_a_type_field_for_uploads()
    {
        $this->assertEquals('<input type="hidden" value="foo" name="type">', AssetFormHelper::typeField('foo'));
        $this->assertEquals('<input type="hidden" value="bar" name="type">', AssetFormHelper::typeField('bar'));
    }

    /**
     * @test
     */
    public function it_can_return_a_locale_field_for_uploads()
    {
        $this->assertEquals('<input type="hidden" value="nl" name="locale">', AssetFormHelper::localeField('nl'));
        $this->assertEquals('<input type="hidden" value="fr" name="locale">', AssetFormHelper::localeField('fr'));
    }

    /**
     * @test
     */
    public function it_can_get_the_typefield_with_locale()
    {
        $this->assertEquals('<input type="hidden" value="foo" name="type">', AssetFormHelper::typeField('foo'));
        $this->assertEquals('<input type="hidden" value="bar" name="trans[fr][files][]">', AssetFormHelper::typeField('bar', 'fr'));
    }
}
