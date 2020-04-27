<?php
/**
 * @group WikidataShow
 * @covers WikidataShow
 */
use Wikimedia\TestingAccessWrapper;

class WikidatashowTest extends MediaWikiTestCase {
  private $apple;

  protected function setUp(): void
  {
    parent::setUp();
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  public function testIsEdible(): void
  {
    echo WikidataShowHooks::getWikipediaLink('Q1558299', 'dewiki');
  }
}
