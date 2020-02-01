<?php

namespace Dat\Utils;

class BashUtil
{

    /**
     * GetNumber of files by shell
     * @param $path
     * @return int
     */
    public static function getFileCount($path)
    {
        return (int)trim(shell_exec("ls $path | wc -l"));
    }

    public static function rmFilesInPath($path, $force = false)
    {
        if ($force) {
            $cmd = "rm -rf $path/*";
        } else {
            $cmd = "rm $path/*";
        }
        return shell_exec($cmd);
    }
    /*
      public static function rm($fileOrDir, $force = false)
      {

      if ($force) {
      $cmd = "rm -rf $fileOrDir";
      } else {
      if (is_dir($fileOrDir)) {
      $cmd = "rmdir $fileOrDir";
      } else {
      $cmd = "rm $fileOrDir";
      }
      }
      shell_exec($cmd);
      }
      // */

    public static function p7z()
    {
        
    }

    /**
     * Count number of original files in zip file
     * @param $file
     * @return int
     */
    public static function p7zCount($file)
    {
        return (int)trim(shell_exec("7z l $file | tail -n1 | awk '{print $5}'"));
    }

    /**
     * Show the uncompressed size of file
     * @param $file
     * @return int
     */
    public static function p7zOriginalSize($file)
    {
        return (int)trim(shell_exec("7z l $file | tail -n1 | awk '{print $3}'"));
    }

    /**
     * Compress with 7z
     * @param $fileOrDir
     * @param $zipfile
     * @param int $cpulimit default 100
     * @return string|null
     * @throws \Exception
     */
    public static function p7zZip($fileOrDir, $zipfile, int $cpulimit = 100)
    {
        if (is_array($fileOrDir)) {
            $cmd = "7z a $zipfile";
            foreach ($fileOrDir as $file) {
                $cmd .= " $file";
            }
        } else if (is_dir($fileOrDir)) {
            $cmd = "7z a $zipfile $fileOrDir";
        } else if (file_exists($fileOrDir)) {
            $cmd = "7z a $zipfile $fileOrDir";
        } else {
            return false;
        }
        if ($cpulimit > 0 && $cpulimit < 100) {
            return self::cpuLimit($cmd, $cpulimit);
        } else {
            return shell_exec($cmd);
        }
    }

    /**
     * Unzip with p7z
     * @param $file
     * @param string $targetPath
     * @return string|null
     */
    public static function p7zUnzip($file, $targetPath = '', $forceYes = false)
    {
        if ($targetPath && !file_exists($targetPath)) {
            mkdir($targetPath);
        }
        $cmd = $targetPath ? ("7z x $file -o$targetPath") : ("7z x $file ");
        if ($forceYes) {
            $cmd .= ' -y';
        }
        return shell_exec($cmd);
    }

    /**
     * Get p7z list
     * @param string $file
     * @param bool $verbose
     * @return array
     */
    public static function p7zList(string $file, bool $verbose = false)
    {
        $count = self::p7zCount($file);
        $tail = $count + 2;
        $listStr = explode(PHP_EOL, shell_exec("7z l $file | tail -n$tail | head -n$count"));
        array_pop($listStr);
        $rFiles = [];
        foreach ($listStr as $line) {
            $arr = array_filter(explode(' ', $line));
            array_push($rFiles, $arr);
        }
        $r = [];
        foreach ($rFiles as $rFile) {
            if ($verbose && count($rFile) >= 5) {
                array_push($r, ['Name' => array_pop($rFile), 'Date' => current($rFile), 'Time' => next($rFile), 'Attr' => next($rFile), 'Size' => next($rFile)]);
            } else {
                array_push($r, array_pop($rFile));
            }
        }
        return $r;
    }

    public static function ls($path)
    {
        $listStr = explode(PHP_EOL, shell_exec("ulimit -s 65536; ls $path"));
        array_pop($listStr);
        return $listStr;
    }

    public static function lsFirst($path)
    {
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        $process = proc_open("ulimit -s 65536; ls $path", $descriptorspec, $pipes);
        $r = "";
        if (is_resource($process)) {
            $test = explode(PHP_EOL, stream_get_contents($pipes[1]));
            proc_close($process);
            $r = $test[0];
        }
        return $r;
    }

    public static function lsLast($path)
    {
        return trim(shell_exec("ulimit -s 65536; ls $path | tail -n1"));
    }

    public static function mkdir($path)
    {
        return shell_exec("mkdir -p $path");
    }

    /**
     * Run command with limited cpu. need to install cpulimit
     * @param string $cmd
     * @param int $percentage
     * @param bool $strict
     * @return string|null
     * @throws \Exception
     */
    public static function cpuLimit(string $cmd, int $percentage, bool $strict = true)
    {
        $a = shell_exec("cpulimit 2>&1");
        if (strpos($a, 'CPUlimit version') !== false) {
            $cmd = "cpulimit -l $percentage $cmd 2>&1";
            return shell_exec($cmd);
        } else if ($strict) {
            throw new \Exception(self::class . ": " . __FUNCTION__ . " cpulimit does not exist");
        } else {
            return shell_exec($cmd);
        }
    }

    /**
     * @return int
     */
    public static function getCpuCount(): int
    {
        $command = "cat /proc/cpuinfo | grep processor | wc -l";
        return (int)shell_exec($command);
    }

    /**
     * Warning: this function takes 0.1 second
     * Get cpu usage return array with keys [user,nice,sys,idle]
     * @return array
     */
    public static function getCpuUsageArray(): array
    {
        $stat1 = file('/proc/stat');
        usleep(100000);
        $stat2 = file('/proc/stat');
        if (!is_array($stat1) || !is_array($stat2)) return [];
        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
        $dif = array();
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys'] = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = array_sum($dif);
        $total = $total > 0 ? $total : PHP_INT_MAX;
        $cpu = [];
        foreach ($dif as $x => $y)
            $cpu[$x] = $y / $total;
        return $cpu;
    }

    /**
     * Warning: this function takes 0.1 second
     * Get current cpu usage
     * @return int|mixed|null
     */
    public static function getCpuUsage()
    {
        $cpuArr = self::getCpuUsageArray();
        return isset($cpuArr['idle']) ? (1 - $cpuArr['idle']) : null;
    }
}
