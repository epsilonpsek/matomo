<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Cors
 */
class CorsTest extends IntegrationTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2014-02-04');
    }

    public function test_corsHandler()
    {
        $heads = $this->responseHeader();
        $this->assertStringContainsString('200 OK', $heads);
        $this->assertStringContainsString('Access-Control-Allow-Origin: *', $heads);
        $this->assertStringContainsString('Access-Control-Allow-Credentials: true', $heads);
    }

    public function test_corsOrigin()
    {
        $origin = "https://exmaple.com";
        $heads = $this->responseHeader($origin);
        $this->assertStringContainsString('200 OK', $heads);
        $this->assertStringContainsString('Access-Control-Allow-Origin: ' . $origin, $heads);

    }

    public function test_configCorsDomains()
    {
        self::$fixture->getTestEnvironment()->overrideConfig('General', 'cors_domains', ['https://example.com']);
        self::$fixture->getTestEnvironment()->save();
        $origin = 'https://exmaple2.com';
        $heads = $this->responseHeader($origin);
        $this->assertStringContainsString('https://example.com', $heads);

        $origin = 'https://example.com';
        $heads = $this->responseHeader($origin);
        $this->assertStringContainsString('200 OK', $heads);

    }

    private function responseHeader($origin = null)
    {

        $url = Fixture::getRootUrl() . "tests/PHPUnit/proxy/matomo.php?idsite=1&rec=1&url=" . urlencode('http://quellehorreur.com/movies') . "&action_name=Movies";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($origin) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Origin: ' . $origin));

        }

        $response = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        return substr($response, 0, $header_size);
    }
}