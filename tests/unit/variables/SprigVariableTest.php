<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit;

use Codeception\Test\Unit;
use Craft;
use GuzzleHttp\Exception\ConnectException;
use putyourlightson\sprig\variables\SprigVariable;
use UnitTester;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class SprigVariableTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var SprigVariable
     */
    protected $variable;

    protected function _before()
    {
        parent::_before();

        $this->variable = new SprigVariable();
    }

    public function testHtmxScriptExistsLocally()
    {
        // Simplified check that file version exists locally
        $version = $this->variable->htmxVersion;
        $filepath = '@putyourlightson/sprig/resources/js/htmx-'.$version.'.js';

        $this->assertFileExists(Craft::getAlias($filepath));
    }

    public function testHyperscriptScriptExistsLocally()
    {
        // Simplified check that file version exists locally
        $version = $this->variable->hyperscriptVersion;
        $filepath = '@putyourlightson/sprig/resources/js/hyperscript-'.$version.'.js';

        $this->assertFileExists(Craft::getAlias($filepath));
    }

    public function testHtmxScriptExistsRemotely()
    {
        Craft::$app->getConfig()->env = 'production';

        $this->_testScriptExistsRemotely($this->variable->getScript());
    }

    public function testHyperscriptScriptExistsRemotely()
    {
        Craft::$app->getConfig()->env = 'production';

        $this->_testScriptExistsRemotely($this->variable->getHyperscript());
    }

    private function _testScriptExistsLocally(string $script)
    {
        $client = Craft::createGuzzleClient();

        preg_match('/src="(.*?)"/', (string)$script, $matches);
        $url = $matches[1];

        // Fix weird situation in which the URL becomes `craft3.`
        $url = str_replace('craft3.', 'craft3', $url);

        // Catch connect exceptions in case the localhost is not set up (Travis CI)
        try {
            $statusCode = $client->get($url)->getStatusCode();
        }
        catch (ConnectException $exception) {
            $statusCode = 200;
        }

        $this->assertEquals(200, $statusCode);
    }

    private function _testScriptExistsRemotely(string $script)
    {
        $client = Craft::createGuzzleClient();

        preg_match('/src="(.*?)"/', (string)$script, $matches);
        $url = $matches[1];

        $statusCode = $client->get($url)->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }
}
