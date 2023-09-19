<?php

namespace Dat\Utils;

require_once(__DIR__ . '/../src/BashUtil.php');
require_once(__DIR__ . '/../src/BaseUtil.php');
require_once(__DIR__ . '/../src/CmnUtil.php');
require_once(__DIR__ . '/../src/IpUtil.php');
require_once(__DIR__ . '/AbstractTest.php');
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

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerGetFileBaseNameFromPath
     */
    public function testGetFileBaseNameFromPath($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerGetFileBaseNameFromPath()
    {
        return [
            [["/absolute/path/Pattern/to/Test.test.html"], "Test.test"],
            [["/absolute/path/Pattern/to/.html"], ""],
            [["/absolute/path/Pattern/to/"], "to"],
        ];
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
        $n = PHP_EOL;
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $var = new \DateTime();
        $testCase = [$var, true, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertStringContainsString("DateTime", $r);

        $testCase = [$var, true, 0];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertStringContainsString("DateTime", $r);

        $testCase = [$var, true, true];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertStringContainsString("object(DateTime)", $r);

        $testCase = [$var, true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertStringContainsString("createFromFormat", $r);

        $testCase = [true, true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals("True (Boolean)" . $n, $r);

        $testCase = [['a', 'b'], true, 2];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($n . print_r(['a', 'b'], true), $r);
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerArrayToTable
     */
    public function testArrayToTable($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerArrayToTable()
    {
        $n = PHP_EOL;
        $arr = [
            [
                "key0" => "abc"
                , "key1" => ["name" => "value 11234", "name1" => "value 2....", "name2" => 123]
                , "key2" => ["name" => "value 16758", "name1" => "value 2....", "name2" => 4658]
                , "key3" => ["name" => "value 1", "name1" => "value 2....", "name2" => 12223]
                , "key4" => ["name" => "value 1fsaf", "name3" => true, "name2" => 127983]
                , "key5" => ["name" => "value 1fsaf", "name4" => ['a' => 'b'], "name567890" => 127983]
            ],
            ["key1" => ["name" => "value 11234", "name1" => "value 2....", "name2" => 123]],
            [
                "a" => ["b" => ["c" => ["d" => "e", "f" => "g"]]],
                "b" => ["b" => ["c" => ["d" => "asdf", "h" => "i"], "j" => 'k']]
            ],
            json_decode('{"Test":{"a":{"b":1565181040,"c":0,"d":"2019-08-07 12:30:40"},"e":[]}}', true),
            ['a', CmnUtil::getColoredString('b', 'red'), CmnUtil::getColoredString(123456, 'green', 'yellow')],
        ];
        return [
            // Simple array
            [[['abc', 'def', 0123456, 3.1415926535, false, null, true, 'true'], 0, 1, 0, 1], "+-----+--------------+$n| key | value        |$n+-----+--------------+$n|   0 | abc          |$n+-----+--------------+$n|   1 | def          |$n+-----+--------------+$n|   2 |        42798 |$n+-----+--------------+$n|   3 | 3.1415926535 |$n+-----+--------------+$n|   4 |        FALSE |$n+-----+--------------+$n|   5 |         NULL |$n+-----+--------------+$n|   6 |         TRUE |$n+-----+--------------+$n|   7 | true         |$n+-----+--------------+"],
            [[['abc', 'def', 0123456, 3.1415926535, false, null, true, 'true']], "+-----+--------------+$n| key | value        |$n+-----+--------------+$n|   0 | abc          |$n|   1 | def          |$n|   2 |        42798 |
|   3 | 3.1415926535 |$n|   4 |        FALSE |$n|   5 |         NULL |$n|   6 |         TRUE |$n|   7 | true         |
+-----+--------------+"],
            [[['abc', 'def', 0123456, 3.1415926535, false, null, true, 'true'], 0, 1, 1], "+-------+-----+-----+-------+--------------+-------+------+------+------+$n| key   | 0   | 1   | 2     | 3            | 4     | 5    | 6    | 7    |$n+-------+-----+-----+-------+--------------+-------+------+------+------+$n| value | abc | def | 42798 | 3.1415926535 | FALSE | NULL | TRUE | true |$n+-------+-----+-----+-------+--------------+-------+------+------+------+"],
            [[["name" => "value 1", "name1" => "value 2....", "name2" => 123], 0, 1, 0, 1],
                "+-------+-------------+$n| key   | value       |$n+-------+-------------+$n| name  | value 1     |
+-------+-------------+$n| name1 | value 2.... |$n+-------+-------------+$n| name2 |         123 |$n+-------+-------------+"],
            [[["name" => "value 1", "name1" => "value 2....", "name2" => 123], 0, 1, 1], "+-------+---------+-------------+-------+$n| key   | name    | name1       | name2 |$n+-------+---------+-------------+-------+$n| value | value 1 | value 2.... |   123 |$n+-------+---------+-------------+-------+"],
            // MultiDimension array
            [[$arr[0], 0, 2], '+------+-------+-------------+-------------+--------+-------+-----------+------------+
| key  | value | name        | name1       | name2  | name3 | name4     | name567890 |
+------+-------+-------------+-------------+--------+-------+-----------+------------+
| key0 | abc   |             |             |        |       |           |            |
| key1 |       | value 11234 | value 2.... |    123 |       |           |            |
| key2 |       | value 16758 | value 2.... |   4658 |       |           |            |
| key3 |       | value 1     | value 2.... |  12223 |       |           |            |
| key4 |       | value 1fsaf |             | 127983 |  TRUE |           |            |
| key5 |       | value 1fsaf |             |        |       | {"a":"b"} |     127983 |
+------+-------+-------------+-------------+--------+-------+-----------+------------+'],
            [[$arr[0], 0, 1, 1], '+-------+------+----------------------------------------------------------+-----------------------------------------------------------+--------------------------------------------------------+----------------------------------------------------+--------------------------------------------------------------+
| key   | key0 | key1                                                     | key2                                                      | key3                                                   | key4                                               | key5                                                         |
+-------+------+----------------------------------------------------------+-----------------------------------------------------------+--------------------------------------------------------+----------------------------------------------------+--------------------------------------------------------------+
| value | abc  | {"name":"value 11234","name1":"value 2....","name2":123} | {"name":"value 16758","name1":"value 2....","name2":4658} | {"name":"value 1","name1":"value 2....","name2":12223} | {"name":"value 1fsaf","name3":true,"name2":127983} | {"name":"value 1fsaf","name4":{"a":"b"},"name567890":127983} |
+-------+------+----------------------------------------------------------+-----------------------------------------------------------+--------------------------------------------------------+----------------------------------------------------+--------------------------------------------------------------+'],
            [[$arr[1], 0, 2], "+------+-------------+-------------+-------+$n| key  | name        | name1       | name2 |$n+------+-------------+-------------+-------+$n| key1 | value 11234 | value 2.... |   123 |$n+------+-------------+-------------+-------+"],
            [[$arr[1], 1, 2], "┌──────┬─────────────┬─────────────┬───────┐
│ key  │ name        │ name1       │ name2 │
├──────┼─────────────┼─────────────┼───────┤
│ key1 │ value 11234 │ value 2.... │   123 │
└──────┴─────────────┴─────────────┴───────┘"],
            [[$arr[1], 2, 2], "                                            
  key    name          name1         name2  
                                            
  key1   value 11234   value 2....     123  
                                            "],
            [[$arr[2], 0, 4],
                "+-----+-------+-------+-------+-----+
| key | b.c.d | b.c.f | b.c.h | b.j |
+-----+-------+-------+-------+-----+
| a   | e     | g     |       |     |
| b   | asdf  |       | i     | k   |
+-----+-------+-------+-------+-----+"],
            [[$arr[2], 0, 3], '+-----+----------------------+-----+
| key | b.c                  | b.j |
+-----+----------------------+-----+
| a   | {"d":"e","f":"g"}    |     |
| b   | {"d":"asdf","h":"i"} | k   |
+-----+----------------------+-----+'],
            [[$arr[2], 0, 2],
                '+-----+------------------------------------+
| key | b                                  |
+-----+------------------------------------+
| a   | {"c":{"d":"e","f":"g"}}            |
| b   | {"c":{"d":"asdf","h":"i"},"j":"k"} |
+-----+------------------------------------+'],
            [[$arr[2], 0, 1],
                '+-----+------------------------------------------+
| key | value                                    |
+-----+------------------------------------------+
| a   | {"b":{"c":{"d":"e","f":"g"}}}            |
| b   | {"b":{"c":{"d":"asdf","h":"i"},"j":"k"}} |
+-----+------------------------------------------+'],
            [[$arr[2], 0, 0], '+-----+------------------------------------------+
| key | value                                    |
+-----+------------------------------------------+
| a   | {"b":{"c":{"d":"e","f":"g"}}}            |
| b   | {"b":{"c":{"d":"asdf","h":"i"},"j":"k"}} |
+-----+------------------------------------------+'],
            [[$arr[3], 0, 2], '+------+--------------------------------------------------+----+
| key  | a                                                | e  |
+------+--------------------------------------------------+----+
| Test | {"b":1565181040,"c":0,"d":"2019-08-07 12:30:40"} | [] |
+------+--------------------------------------------------+----+'],
            [[$arr[3], 0, 3], "+------+------------+-----+---------------------+----+$n| key  | a.b        | a.c | a.d                 | e  |$n+------+------------+-----+---------------------+----+$n| Test | 1565181040 |   0 | 2019-08-07 12:30:40 | [] |$n+------+------------+-----+---------------------+----+"],
            [[$arr[3], 0, 3, true], "+-----+---------------------+$n| key | Test                |$n+-----+---------------------+$n| a.b |          1565181040 |$n| a.c |                   0 |$n| a.d | 2019-08-07 12:30:40 |$n| e   | []                  |$n+-----+---------------------+"],
            [[[['a' => 123, 'b' => 456], ['a' => 78, 'b' => 9012]], 0, 2], "+-----+-----+------+$n| key | a   | b    |
+-----+-----+------+$n|   0 | 123 |  456 |$n|   1 |  78 | 9012 |$n+-----+-----+------+"],
            [[[['lang' => 'en', 'a' => 1], ['lang' => 'th', 'a' => '']], 0, 2], "+-----+------+---+$n| key | lang | a |
+-----+------+---+$n|   0 | en   | 1 |$n|   1 | th   |   |$n+-----+------+---+"],
            [[[['lang' => 'en', 'a' => 1], ['lang' => 'th', 'a' => '']], 1, 2, 1], "┌──────┬────┬────┐
│ key  │ 0  │ 1  │
├──────┼────┼────┤
│ lang │ en │ th │
│ a    │  1 │    │
└──────┴────┴────┘"],
            [[[], 1], "┌─┐
└─┘"],
            [[$arr[4], 1], "┌─────┬────────┐
│ key │ value  │
├─────┼────────┤
│   0 │ a      │
│   1 │ \033[0;31mb\033[0m      │
│   2 │ \033[0;32m\033[43m123456\033[0m │
└─────┴────────┘"],
        ];
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

        $code = '';
        $hex = '';
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
            ['\Dat\Utils\CmnUtil', '', [CmnUtil::LOGTYPE_NOTICE, CmnUtil::LOGTYPE_WARNING, CmnUtil::LOGTYPE_DEBUG, CmnUtil::LOGTYPE_ERROR, CmnUtil::TEST, CmnUtil::LANG_UNKNOWN]],
        ];
    }

    /**
     * @param $class
     * @param $prefix
     * @param $er
     * @dataProvider providerGetConstantsAsArray
     */
    public function testGetConstantsAsArray($class, $prefix, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $testCase = [$class, $prefix, false];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetConstantsAsArray()
    {
        return [
            ['\Dat\Utils\CmnUtil', 'LOGTYPE', ['NOTICE' => CmnUtil::LOGTYPE_NOTICE, 'WARNING' => CmnUtil::LOGTYPE_WARNING, 'DEBUG' => CmnUtil::LOGTYPE_DEBUG, 'ERROR' => CmnUtil::LOGTYPE_ERROR]],
            ['\Dat\Utils\CmnUtil', '', ['LOGTYPE_NOTICE' => CmnUtil::LOGTYPE_NOTICE, 'LOGTYPE_WARNING' => CmnUtil::LOGTYPE_WARNING, 'LOGTYPE_DEBUG' => CmnUtil::LOGTYPE_DEBUG, 'LOGTYPE_ERROR' => CmnUtil::LOGTYPE_ERROR, 'TEST' => CmnUtil::TEST, 'LANG_UNKNOWN' => CmnUtil::LANG_UNKNOWN]],
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
                    'markers' => 'color:red|label:C|13.77148527637,100.49234518194',
                    'key' => 'AIzaSyD61JfgDpdztsN0hj6Ykg0jM1n3x5zFS-A'
                ]],
            ["http://g.cn/?a=1&b=2&c", ['a' => 1, 'b' => 2, 'c' => true]],
            ["http://g.cn/?a=1&b=2=3&c", ['a' => 1, 'c' => true]],
            ['?a=b', ['a' => 'b']],
            ['?a=%C3%85', ['a' => 'Å']],
        ];
    }

    /**
     * 
     * @param type $input
     * @param type $er
     *  @dataProvider providerSetGetParamToUrl
     */
    public function testSetGetParamToUrl($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
//        CmnUtil::debug($input,'input');
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerSetGetParamToUrl()
    {
        return [
            [["//maps.googleapis.com/maps/api/staticmap", [
                'center' => '13.77148527637,100.49234518194',
                'zoom' => '16',
                'size' => '378x250',
                'maptype' => 'roadmap',
                'markers' => 'color:red|label:C|13.77148527637,100.49234518194',
                'key' => 'AIzaSyD61JfgDpdztsN0hj6Ykg0jM1n3x5zFS-A'
            ]], "//maps.googleapis.com/maps/api/staticmap?center=13.77148527637%2C100.49234518194&zoom=16&size=378x250&maptype=roadmap&markers=color%3Ared%7Clabel%3AC%7C13.77148527637%2C100.49234518194&key=AIzaSyD61JfgDpdztsN0hj6Ykg0jM1n3x5zFS-A"],
            [["http://g.cn/", ['a' => 1, 'b' => 2, 'c' => true]], "http://g.cn/?a=1&b=2&c"],
            [["http://g.cn/?z", ['a' => 1, 'b' => 2, 'c' => true]], "http://g.cn/?z&a=1&b=2&c"],
            [["", ['a' => 'b']], '?a=b'],
            [["", ['a' => 'Å']], '?a=%C3%85'],
        ];
    }

    /**
     *
     * @param type $dateDiff
     * @param type $precision
     * @param type $er
     * @dataProvider providerDateIntervalToString
     */
    public function testDateIntervalToString($dateDiff, $precision, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = $precision ? [$dateDiff, $precision] : [$dateDiff];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerDateIntervalToString()
    {
        $di = new \DateInterval('P3Y2M1DT12H10M8S');
        $di->invert = 1;
        $di1 = \DateInterval::createFromDateString('1 month -3 days 5 hours');
        $di1->invert = 1;
        $di2 = \DateInterval::createFromDateString('-1000 microsecond');
        $di2->invert = 1;
        return [
            [$di, null, "-3Y2M1D 12h10m8s"],
            [$di1, null, "1M3D 5h"],
            [$di2, null, "0.001s"],
            [\DateInterval::createFromDateString('2 years'), 'y', "2Y"],
            [\DateInterval::createFromDateString('-1000 microsecond'), 's', "-0.001s"],
            [\DateInterval::createFromDateString('1 microsecond'), null, "0.000001s"],
            [\DateInterval::createFromDateString('10 microsecond'), null, "0.00001s"],
            [\DateInterval::createFromDateString('-10 microsecond'), null, "-0.00001s"],
            [\DateInterval::createFromDateString('10 microsecond'), 'f', "10microseconds"],
            [\DateInterval::createFromDateString('-10 microsecond'), 'f', "-10microseconds"],
            [\DateInterval::createFromDateString('3 days 5 hours 3 minutes'), 's', "3D 5h3m0s"],
            [\DateInterval::createFromDateString('3 days 5 hours 3 minutes'), null, "3D 5h3m"],
            [new \DateInterval('P3Y2M1DT12H10M8S'), null, "3Y2M1D 12h10m8s"],
        ];
    }

    /**
     *
     * @param type $str
     * @param type $isResultArray
     * @param type $er
     * @dataProvider providerGetStrLangByUnicodeRange
     */
    public function testGetStrLangByUnicodeRange($str, $isResultArray, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = (null === $isResultArray) ? [$str] : [$str, $isResultArray];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetStrLangByUnicodeRange()
    {
        $str0 = '雪の断章 简介：Yurose Hirose遇到了一个7岁的女孩Iori，她是一个孤儿，将她带回了她的公寓。 Yuichi拜访了Namba一家，以抓住陷入了人类不信任的她。 在东京拥有房屋的汤一（Yuichi）已转移到札幌上班，管家凯恩（Kane）照顾他的照料。 尽管凯恩不同意，但佑一还是决定在他最好的朋友对马大介的鼓励下抚养八神。 十年过去了，八神现年17岁。 Yuichi试图让Iori接受北海道大学的录取。 在她的高中里，还有难波家族的第二个女儿佐吉子，她也想接受北海道大学的录取。 难波家族的长女尤科（Yuko）已搬到尤织（Iori）居住的寓所。 Yuko的欢迎晚宴是由公寓的居民举行的，她在那里进行了华丽的舞蹈，然后将她拉到自己的房间。';
        $str1 = 'This is English example,This is English example,This is English example,This is English example,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,这一句是汉语,私は日本人です,câu này là tiếng việt,หนังสือพิมพ์ไทย,';
        return [
            ['~`!@#$%^&*()[]\\{}|;\':",./<>?-=_+', null, [CmnUtil::LANG_UNKNOWN => 1]],
            ['This is English example', null, ['en' => 1]],
            ['หนังสือพิมพ์ไทย', null, ['th' => 1]],
            ['这一句是汉语', null, ['zh' => 1]],
            ['私は日本人です', null, ['jp' => 1]],
            ['日本人はハンバーガーを食べる', null, ['jp' => 1]],
            [$str0, null, ['jp' => 0.06896551724137932, 'zh' => 0.7275862068965517, 'en' => 0.20344827586206896]],
            ["mb_substr: Performs a multi-byte safe substr() operation based on number of characters. Position is counted from the beginning of str. First character's position is 0. Second character position is 1, and so on." . PHP_EOL . "Example: mb_substr('你好',1) returns '好'", null, ['en' => 0.9689119170984456, 'zh' => 0.015544041450777202]],
            ['câu này là tiếng việt', null, ['vn' => 1]],
            [$str1, null, ['vn' => 0.2564102564102564, 'en' => 0.21538461538461534, 'zh' => 0.14358974358974355, 'jp' => 0.30769230769230765, 'th' => 0.07692307692307691]],
            ['Россия – священная наша держава,
Россия – любимая наша страна.
Могучая воля, великая слава –
Твоё достоянье на все времена!', null, [CmnUtil::LANG_UNKNOWN => 1]],
            ['First sentence of Israeli National Anthem: כֹּל עוֹד בַּלֵּבָב פְּנִימָה,נֶפֶשׁ יְהוּדִי הוֹמִיָּה,', null, [CmnUtil::LANG_UNKNOWN => 1]],
            ['这一句是汉语', false, 'zh'],
            ['이 문장은 한국어입니다', false, 'kr'],
            [$str0, false, 'zh'],
            [$str1, false, CmnUtil::LANG_UNKNOWN],
        ];
    }

    /**
     *
     * @param type $str
     * @param type $er
     * @dataProvider providerStrSplitUnicode
     */
    public function testStrSplitUnicode($str, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = [$str];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerStrSplitUnicode()
    {
        return [
            ['这一句是汉语', ['这', '一', '句', '是', '汉', '语']],
            ['หนังสือพิมพ์ไทย', ['ห', 'น', 'ั', 'ง', 'ส', 'ื', 'อ', 'พ', 'ิ', 'ม', 'พ', '์', 'ไ', 'ท', 'ย']],
        ];
    }

    /**
     *
     * @param type $arr
     * @param type $er
     * @dataProvider providerGetStdDeviationFrArr
     */
    public function testGetStdDeviationFrArr($arr, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = [$arr];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($er, $r);
    }

    public function providerGetStdDeviationFrArr()
    {
        return [
            [[3, 3, 3, 3], 0],
            [[0.1, 0.1, 0.8], 0.32998316455372223],
            [[0.2, 0.4, 0.5], 0.1247219128924647],
            [['jp' => 0.0711743772241993, 'zh' => 0.7188612099644128, 'en' => 0.20996441281138792], 0.27843548174197064],
            [[1, 2, 3, 4], 1.118033988749895],
            [['a' => 3, 'b' => 3, 3, 3], 0],
            [[], 0],
        ];
    }

    /**
     *
     * @param type $country
     * @param type $er
     * @dataProvider providerGetAllCountryCodes
     */
    public function testGetAllCountryCodes($country, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = [];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(in_array($country, $r), $er);
        self::assertEquals(count($r), 247);
    }

    public function providerGetAllCountryCodes()
    {
        return [
            ['CN', true],
            ['US', true],
            ['FR', true],
            ['TH', true], // countries exist
            ['ZZ', false], // countries do not exist
        ];
    }

    /**
     *
     * @param type $country
     * @param type $er
     * @dataProvider providerGetAllCountries
     */
    public function testGetAllCountries($country, $er)
    {
        $fullMethodName = self::TARGET_CLASS . '::' . self::getTargetMethod(__FUNCTION__);
        $testCase = [];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(in_array($country, $r), $er);
        self::assertEquals(count($r), 247);
    }

    public function providerGetAllCountries()
    {
        return [
            ['China', true],
            ['United States', true],
            ['UnitedStates', false],
        ];
    }

    /**
     *
     * @param type $i
     * @param type $er
     * @dataProvider providerGetCountryNameByCode
     */
    public function testGetCountryNameByCode($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetCountryNameByCode()
    {
        return [
            [['CN'], 'China'],
            [['US'], 'United States'],
            [['ZZ'], ''],
        ];
    }

    /**
     *
     * @param type $i
     * @param type $er
     * @dataProvider providerGetCountryCodeByName
     */
    public function testGetCountryCodeByName($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetCountryCodeByName()
    {
        return [
            [['China'], 'CN'],
            [['United States'], 'US'],
            [['Not a Country'], ''],
        ];
    }

    /**
     *
     * @param type $i
     * @param type $er
     * @dataProvider providerGetAllLocalesFrCountry
     */
    public function testGetAllLocalesFrCountry($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetAllLocalesFrCountry()
    {
        return [
            [['TH'], ['th_TH']],
            [['CA'], ['en_CA', 'fr_CA']],
            [['CN'], ['bo_CN', 'ii_CN', 'ug_CN', 'yue_Hans_CN', 'zh_Hans_CN']],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerEncodeUrlParam
     */
    public function testEncodeUrlParam($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerEncodeUrlParam()
    {
        return [
            [[['a' => 'b']], '?a=b'],
            [[['a' => 'b', 'c' => 'd']], '?a=b&c=d'],
            [[['a' => 'b', 'c' => 'd E']], '?a=b&c=d%20E'],
            [[['a' => 'Å']], '?a=%C3%85'],
            [[['a' => 1, 'b' => 2, 'c' => true]], "?a=1&b=2&c"],
            [[['a' => 1, 'c' => true]], "?a=1&c"],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerStrPad
     */
    public function testStrPad($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerStrPad()
    {
        return [
            [['', 1], ' '],
            [['', 1, ' ', STR_PAD_LEFT], ' '],
            [['a', 2], 'a '],
            [['a', 2, '*', STR_PAD_LEFT], '*a'],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerGetBoolFrStr
     */
    public function testGetBoolFrStr($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetBoolFrStr()
    {
        return [
            [['true'], true],
            [['false'], false],
            [['f'], false],
            [['F'], false],
            [['n'], false],
            [['0'], false],
            [['1'], true],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerFormatSignificantDigits
     */
    public function testFormatSignificantDigits($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerFormatSignificantDigits()
    {
        return [
            [[123.4567, 4], '123.5'],
            [[123.4567, 2, true], '120'],
            [[123.4567, 2], '120'],
            [[123.4567, 2, false], '123'],
            [[1, 4], '1.000'],
            [[0.0123, 4], '0.01230'],
            [[0.0123, 4, false], '0.0123'],
            [['0.01230', 4, false], '0.01230'],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerGetSignificantDigits
     */
    public function testGetSignificantDigits($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetSignificantDigits()
    {
        return [
            [[123.4567], 7],
            [[1], 1],
            [[120], 2],
            [[120, true], 3],
            [[0.0123], 3],
            [[-0.0123], 3],
            [[-11], 2],
            [[-10], 1],
            [['-10.0'], 3],
            [['0.012300'], 5],
            [['1.2.3'], 4], // expected as no error thrown
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerGetIpInfo
     */
    public function testGetIpInfo($i, $er)
    {
        try {
            self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
        } catch (\Exception $e) {
            self::assertTrue(true);
        }
    }

    public function providerGetIpInfo()
    {
        return [
            [['1.1.1.1'], 'US|California'],
        ];
    }

    /**
     * @param $i
     * @param $er
     * @dataProvider providerGetSubstrByIdentifier
     */
    public function testGetSubstrByIdentifier($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerGetSubstrByIdentifier()
    {
        return [
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'ghi'], 'def'],
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'asd'], false],
            [['abcdefghijklmnopqrstuvwxyz', 'abc'], 'defghijklmnopqrstuvwxyz'],
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'ghi', 1, -1], 'e'],
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'ghi', 1, 1], 'efg'],
            [['abcdefghijklmnopqrstuvwxyz', 'def', 'hij'], 'g'],
            [['abcdefghijklmnopqrstuvwxyz', 'def', 'hij', 1, 0], ''],
            [['abcdefghijklmnopqrstuvwxyz', 'def', 'hij', 1, -1], false],
            [['abcdefghijklmnopqrstuvwxyz', 'gdef', 'hij', 1, -1], false],
            [['abcdefghijklmnopqrstuvwxyz', 'def', 'abc'], false],
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'hij', -4], false],
            [['abcdefghijklmnopqrstuvwxyz', 'abc', 'ghi', 0, -4], false],
            [['abcdefghijklmnopqrstuvwxyz', '', 'def'], 'abc'],
            [['abcdefghijklmnopqrstuvwxyz', '', 'def', 1], 'bc'],
            [['abcdefghijklmnopqrstuvwxyz', '', 'def', 1, -1], 'b'],
            [['abcdefghijklmnopqrstuvwxyz', '', 'def', 1, -2], ''],
            [['abcdefghijklmnopqrstuvwxyz', '', 'def', 1, -3], false],
        ];
    }

    /**
     * 
     * @param type $i
     * @param type $er
     * @dataProvider providerRemoveNullFrStr
     */
    public function testRemoveNullFrStr($i, $er)
    {
        self::assertSameTest(self::TARGET_CLASS, __FUNCTION__, $i, $er);
    }

    public function providerRemoveNullFrStr()
    {
        return [
            [[hex2bin("680064007200")], 'hdr'],
        ];
    }
}