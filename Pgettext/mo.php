<?php
/*
 * (c) Ruben Nijveld <ruben@gewooniets.nl>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class for reading from and writing to MO files and strings in MO format.
 */
class CMo
{
    /**
     * Magic number indicating mo files.
     */
    const MAGIC_NUMBER = 0x950412de;

    /**
     * The revision to store mo files in.
     */
    const REVISION = 0;

    /**
     * A NUL byte.
     */
    const NUL = "\0";

    /**
     * An EOT (end of transmission) byte
     */
    const EOT = "\4";

    /**
     * Number of bits used for an integer
     */
    const INT_SIZE = 32;

    /**
     * Write the given Stringset to a mo file.
     * @param Stringset $set The Stringset to transform.
     * @param string $file The file to store the data in.
     * @return void
     */
    public static function toFile(Stringset $set, $file)
    {
        if (!file_exists($file) || is_writable($file)) {
            file_put_contents($file, self::toString($set));
        } else {
            throw new Exception("Could not write output to file.");
        }
    }

    /**
     * Read a mo file and generate a Stringset.
     * @param string $file
     * @return Stringset
     */
    public static function fromFile($file)
    {
        if (is_readable($file)) {
            return self::fromString(file_get_contents($file));
        }
    }

    /**
     * Transform a Stringset to a string in the binary mo format.
     * @param Stringset $set
     * @return string
     */
    public static function toString(Stringset $set, $add_hash_table = true)
    {
        $set->sort();

        if ($add_hash_table) {
            $hash_table_size = self::nextPrime((int)(($set->size() * 4) / 3));
            if ($hash_table_size <= 2) {
                $hash_table_size = 3;
            }
        } else {
            $hash_table_size = 0;
        }

        $ostart = 7 * 4;
        $tstart = $ostart + ($set->size() * 8);
        $hstart = $tstart + ($set->size() * 8);

        $ovstart = $hstart + ($hash_table_size * (self::INT_SIZE / 8));

        $str = '';
        $str .= pack('LL', self::MAGIC_NUMBER, self::REVISION); // magic number and revision
        $str .= pack('L', $set->size()); // number of strings
        $str .= pack('L', $ostart); // start of original strings at 7 words
        $str .= pack('L', $tstart); // start of translated strings, 2 words per entry
        $str .= pack('L', $hash_table_size); // size of hashtable
        $str .= pack('L', $hstart); // start of hashtable

        $ids = '';
        $lengths = array();
        for ($i = 0; $i < $set->size(); $i += 1) {
            $item = $set->item($i);
            $id = $item['id'];
            if ($item['context'] !== null) {
                $id = $item['context'] . self::EOT . $id;
            }

            if ($item['plural'] !== null) {
                $id = $id . self::NUL . $item['plural'];
            }
            $str .= pack('LL', strlen($id), $ovstart + strlen($ids));
            $ids .= $id . self::NUL;
        }

        $tvstart = $ovstart + strlen($ids);

        $values = '';
        for ($i = 0; $i < $set->size(); $i += 1) {
            $item = $set->item($i);
            $value = implode(self::NUL, $item['strings']);
            $str .= pack('LL', strlen($value), $tvstart + strlen($values));
            $values .= $value . self::NUL;
        }

        if ($add_hash_table) {
            $hashtable = self::makeHashTable($set, $hash_table_size);
            foreach ($hashtable as $hash) {
                $str .= pack('L', $hash);
            }
        }
        $str .= $ids;
        $str .= $values;
        return $str;
    }

    /**
     * Read a string in MO format and create a Stringset from it.
     * @param string $str
     * @return Stringset
     */
    public static function fromString($str)
    {
        $set = new Stringset();
        $data = str_split($str, 1);
        $pos = 0;

        list(,$magic) = unpack('L', self::mergechars($data, $pos, 4));

        if ($magic !== self::MAGIC_NUMBER) {
            throw new Exception("This is not a MO string.");
        }

        list(,$version) = unpack('L', self::mergechars($data, $pos, 4));

        if ($version !== self::REVISION) {
            throw new Exception("This file format revision is not supported.");
        }

        list(,$nstrings) = unpack('L', self::mergechars($data, $pos, 4));
        list(,$ostart) = unpack('L', self::mergechars($data, $pos, 4));
        list(,$tstart) = unpack('L', self::mergechars($data, $pos, 4));

        for ($i = 0; $i < $nstrings; $i += 1) {
            $original = $ostart + ($i * 8);
            $translated = $tstart + ($i * 8);
            $original = unpack('Llength/Loffset', self::mergechars($data, $original, 8));
            $translated = unpack('Llength/Loffset', self::mergechars($data, $translated, 8));

            $id = self::mergechars($data, $original['offset'], $original['length'] + 1, false);
            $value = self::mergechars($data, $translated['offset'], $translated['length'] + 1, false);

            if ($id[strlen($id) - 1] !== self::NUL || $value[strlen($value) - 1] !== self::NUL) {
                throw new Exception("String wasn't NUL-terminated");
            }

            $result = array();

            $id = explode(self::NUL, $id);
            if (count($id) === 3) {
                $result['msgid_plural'] = $id[1];
            }
            $id = explode(self::EOT, $id[0]);
            if (count($id) === 2) {
                $result['msgctxt'] = $id[0];
                $result['msgid'] = $id[1];
            } else {
                $result['msgid'] = $id[0];
            }

            $value = explode(self::NUL, $value);
            array_pop($value);
            $result['msgstr'] = $value;
            $set->add($result);
        }
        return $set;
    }

    private static function mergechars($arr, &$start, $length = null, $autoincr = true)
    {
        $res = '';
        if ($length === null || $length === 0) {
            $end = count($arr);
        } else if ($length < 0) {
            $end = count($arr) - $length;
        } else {
            $end = $start + $length;
        }

        for ($i = $start; $i < $end; $i += 1) {
            $res .= $arr[$i];
        }
        $start = $end;
        return $res;
    }

    /**
     * Generate the hashtable.
     * Uses an array which is... a hashtable. Clearly this is a
     * case of hashtableception.
     * @param Stringset $set
     * @param integer $size
     * @return integer[]
     */
    private static function makeHashTable(Stringset $set, $size)
    {
        $table = array();
        // by default everything points to zero
        for ($i = 0; $i < $size; $i += 1) {
            $table[$i] = 0;
        }

        for ($i = 0; $i < $set->size(); $i += 1) {
            $item = $set->item($i);
            $hash = self::hash($item['id']);
            $index = $hash % $size;
            $inc = ($hash % ($size - 2)) + 1;

            // check for collisions
            while ($table[$index] !== 0) {
                if ($index < $size - $inc) {
                    $index += $inc;
                } else {
                    // out of bounds, start at the bottom
                    $index -= $size - $inc;
                }
            }

            // and insert it
            $table[$index] = $i + 1;
        }
        return $table;
    }

    /**
     * Generates a hash from a given string.
     * @param string $str
     * @return integer
     */
    private static function hash($str)
    {
        $hash = 0;
        $str = str_split($str, 1);
        foreach ($str as $char) {
            $hash = ($hash << 4) + ord($char);
            $g = $hash & (0xf << (self::INT_SIZE - 4));
            if ($g !== 0) {
                $hash = $hash ^ ($g >> self::INT_SIZE - 8);
                $hash = $hash ^ $g;
            }
        }
        return $hash;
    }

    /**
     * Retrieves the next prime.
     * @param integer $n
     * @return integer
     */
    private static function nextPrime($n)
    {
        while (!self::isPrime($n)) {
            $n += 1;
        }
        return $n;
    }

    /**
     * Checks if some integer is a prime number.
     * @param integer $n
     * @return boolean
     */
    private static function isPrime($n)
    {
        if ($n === 1) {
            return false;
        }

        if ($n === 2) {
            return true;
        }

        if ($n % 2 === 0) {
            return false;
        }

        for ($i = 3; $i <= ceil(sqrt($n)); $i += 2) {
            if ($n % $i === 0) {
                return false;
            }
        }
        return true;
    }
}