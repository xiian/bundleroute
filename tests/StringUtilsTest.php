<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Test;

use PHPUnit\Framework\TestCase;
use Xiian\BundleRoute\StringUtils;

/**
 * @coversDefaultClass \Xiian\BundleRoute\StringUtils
 */
class StringUtilsTest extends TestCase
{
    public function prefixForRoutesProvider(): array
    {
        return [
            'tom_'                         =>['tom_',     ['tom_resume_pdf', 'tom_resume_html', 'tom_bob']],
            'tom'                          =>['tom',      ['tomresume_pdf', 'tomresume_html', 'tombob']],
            'tomresume'                    =>['tomresume',['tomresume_pdf', 'tomresume_html', 'tomresumebob']],
            'single entry should be empty' =>['',         ['bob']],
            'no common prefix'             =>['',         ['aomresume_pdf', 'bomresume_html', 'comresumebob']],
            'empty array should be empty'  =>['',         []],
            'duplicate edge case'          =>['abc',      ['abc', 'abc', 'abc']],
        ];
    }

    /**
     * @dataProvider prefixForRoutesProvider
     */
    public function testGetPrefixForRoutes(string $expect, array $routes): void
    {
        $this->assertEquals($expect, StringUtils::getPrefixForStrings($routes));
    }
}
