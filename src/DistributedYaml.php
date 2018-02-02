<?php declare(strict_types=1);

namespace Darsyn\Yaml;

use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

class DistributedYaml extends Yaml
{
    protected const IMPORT_TAG = 'import';

    public static function parseFile(string $filename, int $flags = 0): mixed
    {
        $yaml = new Parser;
        $data = $yaml->parseFile($filename, $flags | BaseYaml::PARSE_CUSTOM_TAGS);
        array_walk_recursive($data, function (&$value) use ($flags, $filename) {
            if ($value instanceof TaggedValue && $value->getTag() === static::IMPORT_TAG) {
                $value = static::importFile($value->getValue(), $flags, $filename);
            } elseif ($flags & BaseYaml::PARSE_CUSTOM_TAGS === 0) {
                throw new ParseException(sprintf(
                    'Tags support is not enabled. You must use the flag `Yaml::PARSE_CUSTOM_TAGS` to use "%s".',
                    $value->getTag()
                ));
            }
        });
        return $data;
    }

    private static function importFile(string $value, int $flags, $callingFile): mixed
    {
        static $fileLocator;
        if ($fileLocator === null) {
            $fileLocator = new FileLocator;
        }
        try {
            $filename = $fileLocator->locate($value, dirname(realpath($callingFile)));
            return static::parseFile($filename, $flags);
        } catch (FileLocatorFileNotFoundException $e) {
            throw new RuntimeException(sprintf(
                'File "%s" not found (defined in "%s").',
                $value,
                $callingFile
            ));
        }
    }
}
