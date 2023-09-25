<?php

use Dat\Utils\BashUtil;
use Dat\Utils\AbstractTest;
use Dat\Utils\CmnUtil;

require_once(__DIR__ . '/../src/BashUtil.php');
require_once(__DIR__ . '/../src/CmnUtil.php');
class BashUtilTest extends AbstractTest
{
    const TARGET_CLASS = BashUtil::class;

    public function testGetFileCount()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        $tmppath = "z" . CmnUtil::getRandomFilename(19);
        $path = "/tmp/" . $tmppath;
        shell_exec("mkdir $path");
        $count = (int)round(rand(3, 50));
        $files = [];

        // create $count files in /tmp/$tmppath folder
        for ($i = 0; $i < $count; $i++) {
            $file = CmnUtil::getRandomFilename(20);
            array_push($files, $file);
            shell_exec("touch $path/$file");
            if ($i == 0) {
                $firstFile = $file;
            }
        }

        $lsMethodName = self::TARGET_CLASS . '::ls';
        $testCase = [$path];
        $r = call_user_func_array($lsMethodName, $testCase);
        self::assertContains($firstFile, $r);

//        $lsFirstMethodName = self::TARGET_CLASS . '::lsFirst';
//        $testCase = [$path];
//        $r = call_user_func_array($lsFirstMethodName, $testCase);
//        natcasesort($files);
//        $files = array_values($files);
//        $firstFile = $files[0];
//        CmnUtil::debug($files, 'fs');
//        self::assertEquals($firstFile, $r);
//
//        $lsLastMethodName = self::TARGET_CLASS . '::lsLast';
//        $testCase = [$path];
//        $r = call_user_func_array($lsLastMethodName, $testCase);
//        $lastFile = array_pop($files);
//        self::assertEquals($lastFile, $r);

        $testCase = [$path];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals($count, $r);
        shell_exec("rm -rf $path");  // remove files
    }

    /**
     * Test p7z on zip, count, unzip, orginal size, list
     * @throws Exception
     */
    public function testP7zCount()
    {
        $tmppath = "z" . CmnUtil::getRandomFilename(19);
        $path = "/tmp/" . $tmppath;
        shell_exec("mkdir $path");
        $count = (int)round(rand(3, 50));

        // create $count files in /tmp/$tmppath folder
        $files = [];
        $size = 0;
        for ($i = 0; $i < $count; $i++) {
            $file = CmnUtil::getRandomFilename(20);
            shell_exec("touch $path/$file");
            $rand = rand(0, 1024);
            $str = CmnUtil::getRandomFilename($rand);
            shell_exec("echo '$str' > $path/$file");
            $size += $rand + 1;  // bash will add a \n at the end
            array_push($files, "$path/$file");
            if ($i == 0) {
                $firstfile = $file;
            } elseif ($i == $count - 1) {
                $lastfile = $file;
            }
        }

        // Test 7z zip folder
        BashUtil::p7zZip($path, "$path.7z", 50);
        $r = BashUtil::p7zCount("$path.7z");
        self::assertEquals($count, $r);

        // Test 7z zip multiple files
        BashUtil::p7zZip($files, "$path.1.7z");
        $r = BashUtil::p7zCount("$path.1.7z");
        self::assertEquals($count, $r);

        // Test 7z unzip
        BashUtil::p7zUnzip("$path.1.7z", "$path/tmp");
        $testCase = ["$path/tmp"];
        $r = call_user_func_array(self::TARGET_CLASS . '::getFileCount', $testCase);
        self::assertEquals($count, $r);

        // Test 7z original size
        $r = BashUtil::p7zOriginalSize("$path.1.7z");
        self::assertEquals($size, $r);

        // Test non verbose list
        $r = BashUtil::p7zList("$path.1.7z");
        self::assertContains($firstfile, $r);
        self::assertContains($lastfile, $r);
        self::assertEquals($count, count($r));

        // Test verbose list with file size and count
        $r = BashUtil::p7zList("$path.1.7z", true);
        self::assertEquals($count, count($r));
        $sizeInList = 0;
        foreach ($r as $j) {
            $sizeInList += $j['Size'];
        }
        self::assertEquals($size, $sizeInList);

        shell_exec("rm -rf $path");  // remove files
        shell_exec("rm -rf $path.7z");  // remove files
        shell_exec("rm -rf $path.1.7z");  // remove files
    }

    public function testGetCpuUsageArray()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;

        $testCase = [];
        $r = call_user_func_array($fullMethodName, $testCase);
        self::assertEquals(1, $r['user'] + $r['sys'] + $r['idle']);
    }

    public function testGetDfTable()
    {
        $r = call_user_func_array(self::TARGET_CLASS . "::" . self::getTargetMethod(__FUNCTION__), []);
        self::assertStringContainsString("Use", $r);
        self::assertStringContainsString("Mounted", $r);
        self::assertStringContainsString("/dev", $r);
    }

    public function testGetMemoryUsageArray()
    {
        $r = json_encode(call_user_func_array(self::TARGET_CLASS . "::" . self::getTargetMethod(__FUNCTION__), []));
        self::assertStringContainsString("MemTotal", $r);
        self::assertStringContainsString("MemFree", $r);
    }
}