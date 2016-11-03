<?php

define ('SNAPSHOT_CSV', 'snapshot.csv');
define ('ERRLOG_TXT', 'errlog.txt');

abstract class BasicParser {
    protected $targetFile;

    function __construct($targetFile)
    {
        $this->targetFile = $targetFile;
    }

    abstract function parse($filePath, $tags);
}

class SnapshotParser extends BasicParser {

    public function parse_deprecated($filePath, $tags) {
        $tagValues = array();
        $tagsCount = count($tags);

        $handle = fopen($filePath, "r");
        if (!isset($handle)) {
            return $tagValues;
        }

        while (($line = fgets($handle)) !== false) {

            $csv = str_getcsv($line);
            if (!isset($csv)) continue;

            foreach ($tags as $tag)  {
                if ($csv[0] == $tag) {
                    array_push($tagValues, array(
                        'tag' => $tag,
                        'val' => $csv[1]
                    ));
                }
            }

            if ($tagsCount == count($tagValues)) {
                break;
            }
        }

        fclose($handle);

        return $tagValues;
    }

    public function parse($filePath, $tags) {
        $tagValues = array();
        $codeVersion =  array();

        $jsonObj = $this->toJSON($filePath, $codeVersion);
        foreach ($tags as $tag)  {
            $value = $this->get_opt($jsonObj, $tag);
            if ($value != null) {
                array_push($tagValues, array(
                    'tag' => $tag,
                    'val' => $value
                ));
            }
        }

        foreach ($codeVersion as $key => $value) {
            $value = $this->get_opt($jsonObj, $key);
            if ($value != null) {
                array_push($tagValues, array(
                    'tag' => $key,
                    'val' => implode('.', $value)
                ));
            }
        }

        return $tagValues;
    }

    function set_opt(&$array_ptr, $key, $value) {
        $keys = explode('.', $key);

        $last_key = array_pop($keys);

        while ($arr_key = array_shift($keys)) {
            if (!array_key_exists($arr_key, $array_ptr)) {
                $array_ptr[$arr_key] = array();
            }
            $array_ptr = &$array_ptr[$arr_key];
        }

        $array_ptr[$last_key] = $value;
    }

    function get_opt(&$array_ptr, $key) {
        $keys = explode('.', $key);

        $last_key = array_pop($keys);

        while ($arr_key = array_shift($keys)) {
            if (!array_key_exists($arr_key, $array_ptr)) {
                return null;
            }
            $array_ptr = &$array_ptr[$arr_key];
        }

        return $array_ptr[$last_key];
    }

    public function toJSON($filePath, &$codeVersionTags) {
        $jsonObj = array();

        $handle = fopen($filePath, "r");
        if (!isset($handle)) {
            return $jsonObj;
        }

        while (($line = fgets($handle)) !== false) {

            if (preg_match('(//.*)', $line)) {
                continue;
            }

            $csv = str_getcsv($line);
            if (!isset($csv)) continue;
            $this->set_opt($jsonObj, $csv[0], $csv[1]);

            // get version
            if (preg_match('/(?P<prefix>\w+\.\w+\.\w+\.CodeVersion)\.[A|B|C|D]/', $csv[0], $match)) {
                $prefix = $match['prefix'];
                if (!array_key_exists($prefix, $codeVersionTags)) {
                    $codeVersionTags[$prefix] = '';
                }
            }
        }

        fclose($handle);

        return $jsonObj;
    }
}

class ErrorLogParser extends BasicParser {

    public function parse($filePath, $tags) {
        $tagValues = array();

        $jsonObj = $this->toJSON($filePath);
        foreach ($jsonObj as $key => $value)  {
            array_push($tagValues, array(
                'tag' => $key,
                'val' => sprintf('%s - {Rev}[%s] - SN: %s', $key, $value['Rev'], $value['SN'])
            ));
        }

        return $tagValues;
    }

    public function toJSON($filePath) {
        $tagValues = array();

        $handle = fopen($filePath, "r");
        if (!$handle) {
            return $tagValues;
        }

        while (($line = fgets($handle)) !== false) {

            if (preg_match('/(IOC bus [\d] slot [\d])/', $line, $ioc_bus, PREG_OFFSET_CAPTURE)) {
                $param_array = array();
                $line = fgets($handle);
                if (preg_match_all('/(?P<param>(\{([\w\-]+)\}\[([\w\.\s\-]+)\]\:))/', $line, $matches,
                    PREG_PATTERN_ORDER)) {
                    foreach ($matches['param'] as $param) {
                        if (preg_match('/\{(?P<tag>[\w\-]+)\}\[(?P<value>[\w\.\s\-]+)\]\:/', $param, $match)) {
                            $param_array[$match['tag']] = $match['value'];
                        }
                    }
                    $tagValues[$ioc_bus[0][0]] = $param_array;
                }
            }
        }

        fclose($handle);

        return $tagValues;
    }
}

