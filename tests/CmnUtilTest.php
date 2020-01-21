<?php

namespace Dat\Utils;

require_once('../src/BashUtil.php');
require_once('../src/BaseUtil.php');
require_once('../src/CmnUtil.php');

class CmnUtilTest extends AbstractTest
{
    const TARGET_CLASS = CmnUtil::class;

    public function testGetRandomFilename()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [10];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(10, strlen($r));
    }

    public function testLeftTrim()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = ["/absolute/path/Pattern/to/Test", "/Pattern/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("to/Test", $r);

        $testCase = ["/absolute/path/Pattern/to/Test", "/Pattern1/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("/absolute/path/Pattern/to/Test", $r);

        $testCase = ["/absolute/path/Pattern/to/Test", "/absolute/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("path/Pattern/to/Test", $r);
    }

    public function testRightTrim()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = ["/absolute/path/Pattern/to/Test", "/Pattern/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("/absolute/path", $r);

        $testCase = ["/absolute/path/Pattern/to/Test", "/Pattern1/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("/absolute/path/Pattern/to/Test", $r);

        $testCase = ["/absolute/path/Pattern/to/Test", "/absolute/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("", $r);
    }

    public function testGetFileNameFromPath()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = ["/absolute/path/Pattern/to/Test.test.html"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("Test.test.html", $r);

        $testCase = ["/absolute/path/Pattern/to/.html"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(".html", $r);

        $testCase = ["/absolute/path/Pattern/to/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("to", $r);
    }

    public function testGetFileBaseNameFromPath()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = ["/absolute/path/Pattern/to/Test.test.html"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("Test.test", $r);

        $testCase = ["/absolute/path/Pattern/to/.html"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("", $r);

        $testCase = ["/absolute/path/Pattern/to/"];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("to", $r);
    }

    /**
     * @param $path
     * @param $er
     * @dataProvider providerGetFileExtFromPath
     */
    public function testGetFileExtFromPath($path, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, [$path]);
        self::assertEquals($er, $r);
    }

    public function providerGetFileExtFromPath()
    {
        return [
            ['/absolute/path/Pattern/to/Test.test.html', 'html'],
            ['http://absolute/path/Pattern/to/Test.test.jpg', 'jpg'],
            ['/absolute/path/Pattern/to/Test.test.', ''],
            ['/absolute/path/Pattern/to/Test', ''],
        ];
    }

    /**
     * @param $url
     * @param $er
     * @dataProvider providerEncodeUrlOnlySpecial
     */
    public function testEncodeUrlOnlySpecial($url, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, [$url]);
        self::assertEquals($er, $r);
    }

    public function providerEncodeUrlOnlySpecial()
    {
        return [
            ['https://www.xxx.com/something/image/21550122908_ต้นฉบับ2.png', 'https://www.xxx.com/something/image/21550122908_%E0%B8%95%E0%B9%89%E0%B8%99%E0%B8%89%E0%B8%9A%E0%B8%B1%E0%B8%9A2.png'],
            ['http://test.com/abc def', 'http://test.com/abc%20def']
        ];
    }

    public function testDumpVar()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $var = new \DateTime();
        $testCase = [$var, true, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertContains("DateTime", $r);

        $testCase = [$var, true, 0];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertContains("DateTime", $r);

        $testCase = [$var, true, true];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertContains("object(DateTime)", $r);

        $testCase = [$var, true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertContains("createFromFormat", $r);

        $testCase = [true, true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("True (Boolean)" . PHP_EOL, $r);

        $testCase = [['a', 'b'], true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(PHP_EOL . print_r(['a', 'b'], true), $r);
    }

    public function testArrayToTable()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        // Simple array
        $arr = ['abc', 'def', 0123456, 3.1415926535, false, null, true, 'true'];
        $er = "+--------------+" . PHP_EOL . "| abc          |" . PHP_EOL . "| def          |" . PHP_EOL . "|        42798 |" . PHP_EOL . "| 3.1415926535 |" . PHP_EOL . "|        FALSE |" . PHP_EOL . "|         NULL |" . PHP_EOL . "|         TRUE |" . PHP_EOL . "| true         |" . PHP_EOL . "+--------------+";
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertEquals($er, $r);

        // Associate array
        $arr = ["name" => "value 1", "name1" => "value 2....", "name2" => 123];
        $er = "+-------+-------------+" . PHP_EOL . "| key   | value       |" . PHP_EOL . "+-------+-------------+" . PHP_EOL . "| name  | value 1     |" . PHP_EOL . "| name1 | value 2.... |" . PHP_EOL . "| name2 |         123 |" . PHP_EOL . "+-------+-------------+";
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertEquals($er, $r);

        // MultiDimension array
        $arr = [
            "key0" => "abc"
            , "key1" => ["name" => "value 11234", "name1" => "value 2....", "name2" => 123]
            , "key2" => ["name" => "value 16758", "name1" => "value 2....", "name2" => 4658]
            , "key3" => ["name" => "value 1", "name1" => "value 2....", "name2" => 12223]
            , "key4" => ["name" => "value 1fsaf", "name3" => true, "name2" => 127983]
            , "key5" => ["name" => "value 1fsaf", "name4" => ['a' => 'b'], "name567890" => 127983]
        ];
        $er = '+------+-------+-------------+-------------+--------+-------+-----------+------------+
| key  | value | name        | name1       | name2  | name3 | name4     | name567890 |
+------+-------+-------------+-------------+--------+-------+-----------+------------+
| key0 | abc   |             |             |        |       |           |            |
| key1 |       | value 11234 | value 2.... |    123 |       |           |            |
| key2 |       | value 16758 | value 2.... |   4658 |       |           |            |
| key3 |       | value 1     | value 2.... |  12223 |       |           |            |
| key4 |       | value 1fsaf |             | 127983 |  TRUE |           |            |
| key5 |       | value 1fsaf |             |        |       | {"a":"b"} |     127983 |
+------+-------+-------------+-------------+--------+-------+-----------+------------+';
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertEquals($er, $r);

        $arr = [
            "key1" => ["name" => "value 11234", "name1" => "value 2....", "name2" => 123]
        ];
        $er = '+------+-------------+-------------+-------+
| key  | name        | name1       | name2 |
+------+-------------+-------------+-------+
| key1 | value 11234 | value 2.... |   123 |
+------+-------------+-------------+-------+';
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertEquals($er, $r);


        $er = '┌──────┬─────────────┬─────────────┬───────┐
│ key  │ name        │ name1       │ name2 │
├──────┼─────────────┼─────────────┼───────┤
│ key1 │ value 11234 │ value 2.... │   123 │
└──────┴─────────────┴─────────────┴───────┘';
        $r = call_user_func_array($fullMethodName, [$arr, 1]);
        self::assertEquals($er, $r);

        $er = '                                            
  key    name          name1         name2  
                                            
  key1   value 11234   value 2....     123  
                                            ';
        $r = call_user_func_array($fullMethodName, [$arr, 2]);
        self::assertEquals($er, $r);

        $arr = [
            "a" => ["b" => ["c" => ["d" => "e", "f" => "g"]]],
            "b" => ["b" => ["c" => ["d" => "asdf", "h" => "i"], "j" => 'k']]
        ];
        $er = '+-----+-------+-------+-----+
| key | b.c.d | b.c.h | b.j |
+-----+-------+-------+-----+
| a   | e     |       |     |
| b   | asdf  | i     | k   |
+-----+-------+-------+-----+';
        $r = call_user_func_array($fullMethodName, [$arr, 0, 3]);
        self::assertEquals($er, $r);

        $er = '+-----+----------------------+-----+
| key | b.c                  | b.j |
+-----+----------------------+-----+
| a   | {"d":"e","f":"g"}    |     |
| b   | {"d":"asdf","h":"i"} | k   |
+-----+----------------------+-----+';
        $r = call_user_func_array($fullMethodName, [$arr, 0, 2]);
        self::assertEquals($er, $r);

        $er = '+-----+------------------------------------+
| key | b                                  |
+-----+------------------------------------+
| a   | {"c":{"d":"e","f":"g"}}            |
| b   | {"c":{"d":"asdf","h":"i"},"j":"k"} |
+-----+------------------------------------+';
        $r = call_user_func_array($fullMethodName, [$arr, 0, 1]);
        self::assertEquals($er, $r);

        $arr = json_decode('{"Test":{"a":{"b":1565181040,"c":0,"d":"2019-08-07 12:30:40"},"e":[]}}', true);
        $er = '+------+------------+-----+---------------------+----+
| key  | a.b        | a.c | a.d                 | e  |
+------+------------+-----+---------------------+----+
| Test | 1565181040 |   0 | 2019-08-07 12:30:40 | [] |
+------+------------+-----+---------------------+----+';
        $r = call_user_func_array($fullMethodName, [$arr, 0, 2]);
        self::assertEquals($er, $r);

        $arr = [['a' => 123, 'b' => 456], ['a' => 78, 'b' => 9012]];
        $er = '+-----+-----+------+
| key | a   | b    |
+-----+-----+------+
|   0 | 123 |  456 |
|   1 |  78 | 9012 |
+-----+-----+------+';
        $r = call_user_func_array($fullMethodName, [$arr, 0, 2]);
        self::assertEquals($er, $r);
    }

    public function testIsArrayAssoc()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $arr = ['abc', 'def', 0123456, 3.1415926535, false, null, true, 'true'];
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertFalse($r);

        $arr = ['a' => 'b'];
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertTrue($r);

        $arr = [1 => 'b', 0 => 'a'];
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertTrue($r);

        $arr = [['a' => 123, 'b' => 456], ['a' => 78, 'b' => 9012]];
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertTrue($r);
    }

    public function testGetArrayDepth()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        $arr = [
            "a" => ["b" => ["c" => ["d" => "e", "f" => "g"]]],
            "b" => ["b" => ["c" => ["d" => "asdf", "h" => "i"], "j" => 'k']]
        ];
        $r = call_user_func_array($fullMethodName, [$arr]);
        self::assertEquals(4, $r);
    }

    public function testGetParam()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        $arr = ["a" => true, "b" => ["c" => "d"], "e" => "true", "f" => null];

        $r = call_user_func_array($fullMethodName, ['a', $arr]);
        self::assertTrue($r);

        $r = call_user_func_array($fullMethodName, ['b', $arr]);
        self::assertEquals(["c" => "d"], $r);

        $r = call_user_func_array($fullMethodName, ['e', $arr]);
        self::assertEquals("true", $r);

        $r = call_user_func_array($fullMethodName, ['f', $arr]);
        self::assertEquals(null, $r);

        $r = call_user_func_array($fullMethodName, ['g', $arr]);
        self::assertFalse($r);
    }

    public function testSetParam()
    {

        $arr = ["a" => true, "b" => ["c" => "d"], "e" => "true", "f" => null];

        CmnUtil::setParam($arr, 'a', false);
        $r = CmnUtil::getParam('a', $arr);
        self::assertFalse($r);

        $r = CmnUtil::getParam('f', $arr);
        self::assertEquals(null, $r);
    }

    public function testIncrementParam()
    {

        $arr = ["a" => 2, "b" => ["c" => "d"], "e" => "4", "f" => null];

        CmnUtil::incrementParam($arr, 'f');
        $r = CmnUtil::getParam('f', $arr);
        self::assertEquals(1, $r);

        CmnUtil::incrementParam($arr, 'a', 2);
        $r = CmnUtil::getParam('a', $arr);
        self::assertEquals(4, $r);

        CmnUtil::incrementParam($arr, 'e', 3);
        $r = CmnUtil::getParam('e', $arr);
        self::assertEquals(7, $r);

        CmnUtil::incrementParam($arr, 'g');
        $r = CmnUtil::getParam('g', $arr);
        self::assertEquals(1, $r);
    }

    public function testBase32Encode()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        $hex = '4ed5fc85d4690f5b82c0137ed1c215cb0b5b8516';
        $r = call_user_func_array($fullMethodName, [$hex]);
        self::assertEquals('J3K7ZBOUNEHVXAWACN7NDQQVZMFVXBIW', $r);

        $hex = '';
        $r = call_user_func_array($fullMethodName, [$hex]);
        self::assertEquals('', $r);

        $str = '';
        $r = call_user_func_array($fullMethodName, [$str, true]);
        self::assertEquals('', $r);
    }

    public function testBase32Decode()
    {
        $i = 999;
        while ($i > 0) {
            $i--;
            $str = CmnUtil::getRandomFilename(rand(0, 100));
            $r = CmnUtil::base32Decode(CmnUtil::base32Encode($str, true), true);
            self::assertEquals($r, $str);
        }

        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $str = '';
        $r = call_user_func_array($fullMethodName, [$str, true]);
        self::assertEquals('', $r);

        $code = 'J3K7ZBOUNEHVXAWACN7NDQQVZMFVXBIW';
        $hex = '4ed5fc85d4690f5b82c0137ed1c215cb0b5b8516';
        $r = call_user_func_array($fullMethodName, [$code]);
        self::assertEquals($hex, $r);
    }

    public function testBin2Hex()
    {

        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $str = '';
        $r = call_user_func_array($fullMethodName, [$str]);
        self::assertEquals('', $r);

        $str = 'J';
        $r = call_user_func_array($fullMethodName, [$str]);
        self::assertEquals('4a', $r);
    }

    public function testHex2Bin()
    {
        $i = 999;
        while ($i > 0) {
            $i--;
            $str = CmnUtil::getRandomFilename(rand(0, 100));
            $r = CmnUtil::hex2Bin(CmnUtil::bin2Hex($str));
            self::assertEquals($r, $str);
        }
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $str = '';
        $r = call_user_func_array($fullMethodName, [$str]);
        self::assertEquals('', $r);

        $str = '4a';
        $r = call_user_func_array($fullMethodName, [$str]);
        self::assertEquals('J', $r);
    }

    public function testGetConstantNameByValue()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = ['\Dat\Utils\CmnUtil', 'LOGTYPE', 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        $er = 'LOGTYPE_DEBUG';
        self::assertEquals($er, $r);

        $testCase = ['\Dat\Utils\CmnUtil', 'LOGTYPE', 0];
        $r = call_user_func_array($fullMethodName, $testCase);
        $er = 'LOGTYPE_NOTICE';
        self::assertEquals($er, $r);

        $testCase = ['\Dat\Utils\CmnUtil', 'LOGTYPE', 0, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        $er = 'NOTICE';
        self::assertEquals($er, $r);

        $testCase = ['\NonExistClass', 'RAND', 0, true, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals('', $r);
    }

    /**
     * @param $class
     * @param $name
     * @param $er
     * @dataProvider providerGetConstantValueByName
     */
    public function testGetConstantValueByName($class, $name, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [$class, $name, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetConstantValueByName()
    {
        return [
            ['\Dat\Utils\CmnUtil', 'LOGTYPE_NOTICE', CmnUtil::LOGTYPE_NOTICE],
            ['\Dat\Utils\CmnUtil', 'LOGTYPE_ERROR', CmnUtil::LOGTYPE_ERROR],
            ['\NonExistClass', 'RAND', null]
        ];
    }

    /**
     * @param $class
     * @param $values
     * @dataProvider providerGetConstantValues
     */
    public function testGetConstantValues($class, $prefix, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [$class, $prefix, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetConstantValues()
    {
        return [
            ['\Dat\Utils\CmnUtil', 'LOGTYPE', [CmnUtil::LOGTYPE_NOTICE, CmnUtil::LOGTYPE_WARNING, CmnUtil::LOGTYPE_DEBUG, CmnUtil::LOGTYPE_ERROR]],
            ['\Dat\Utils\CmnUtil', '', [CmnUtil::LOGTYPE_NOTICE, CmnUtil::LOGTYPE_WARNING, CmnUtil::LOGTYPE_DEBUG, CmnUtil::LOGTYPE_ERROR, CmnUtil::TEST]],
        ];
    }

    /**
     * @param $str
     * @param $er
     * @dataProvider providerGetFloatFrStr
     */
    public function testGetFloatFrStr($str, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [$str];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetFloatFrStr()
    {
        return [
            ["$1234", 1234],
            ["$12.34", 12.34],
            ["$123.4", 123.4],
            ["$1.234", 1.234],
            ["$1.234", 1.234],
            ["$1.23.4", 1.23],
            ["٦٧", 0],
            ["abcde$123.4.5xyz6", 123.4],
            ["฿800", 800],
        ];
    }

    /**
     * 
     * @param type $url
     * @param type $er
     * @dataProvider providerGetGetParamFrUrl

     */
    public function testGetGetParamFrUrl($url, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [$url];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetGetParamFrUrl()
    {
        return [
            ["//maps.googleapis.com/maps/api/staticmap?center=13.77148527637,100.49234518194&zoom=16&size=378x250&maptype=roadmap&markers=color:red%7Clabel:C%7C13.77148527637,100.49234518194&key=AIzaSyD61JfgDpdztsN0hj6Ykg0jM1n3x5zFS-A", [
                    'center' => '13.77148527637,100.49234518194',
                    'zoom' => '16',
                    'size' => '378x250',
                    'maptype' => 'roadmap',
                    'markers' => 'color:red%7Clabel:C%7C13.77148527637,100.49234518194',
                    'key' => 'AIzaSyD61JfgDpdztsN0hj6Ykg0jM1n3x5zFS-A'
                ]],
            ["http://g.cn/?a=1&b=2&c", ['a' => 1, 'b' => 2, 'c' => true]],
            ["http://g.cn/?a=1&b=2=3&c", ['a' => 1, 'c' => true]],
            ['?a=b', ['a' => 'b']],
        ];
    }
}
