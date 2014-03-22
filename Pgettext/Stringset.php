<?php
/*
 * (c) Ruben Nijveld <ruben@gewooniets.nl>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stringset is a simple class for storing a gettext catalog.
 */
class Stringset
{
    /**
     * The collection of strings and their translations
     * @var array[]
     */
    private $set;

    public function __construct()
    {
        $this->set = array();
        $this->_fuzzy = array();
    }

    /**
     * Size of the collection
     * @return integer
     */
    public function size()
    {
        return count($this->set);
    }

    /**
     * Add an entry to the catalog.
     * @param array $entry
     * @return void
     */
    public function add(array $entry)
    {
        if (!isset($entry['msgid'])) {
            throw new Exception("Invalid entry: missing msgid");
        }
        $id = $entry['msgid'];
        $plural_id = isset($entry['msgid_plural']) ? $entry['msgid_plural'] : null;
        $context = isset($entry['msgctxt']) ? $entry['msgctxt'] : null;
        $flags = isset($entry['flags']) ? $entry['flags'] : array();

        $strings = array();
        foreach ($entry as $key => $value) {
            // if( $key == "#, fuzzy" ) {
            //   $this->_fuzzy[] = $key;
            // }
            if (substr($key, 0, 6) === 'msgstr') {
                if (is_array($value)) {
                    $strings = array_merge($strings, $value);
                } else {
                    $strings[] = $value;
                }
            }
        }
        if (count($strings) === 0) {
            throw new Exception("Invalid entry: missing msgstr");
        }
        $this->set[] = array(
            'id' => $id,
            'plural' => $plural_id,
            'context' => $context,
            'flags' => $flags,
            'strings' => $strings
        );
    }

    private function usortfn( $first, $second ) {
            $ids = strcmp($first['id'], $second['id']);
            if ($ids === 0) {
                if ($first['context'] === null && $second['context'] === null) {
                    return 0;
                } else if ($first['context'] === null) {
                    return -1;
                } else if ($second['context'] === null) {
                    return 1;
                } else {
                    return strcmp($first['context'], $second['context']);
                }
            } else {
                return $ids;
            }
    }

    /**
     * Sort the entries in lexical order.
     * @return void
     */
    public function sort()
    {
      usort( $this->set, array( & $this, 'usortfn' ) );
/*        usort($this->set, function ($first, $second) {
            $ids = strcmp($first['id'], $second['id']);
            if ($ids === 0) {
                if ($first['context'] === null && $second['context'] === null) {
                    return 0;
                } else if ($first['context'] === null) {
                    return -1;
                } else if ($second['context'] === null) {
                    return 1;
                } else {
                    return strcmp($first['context'], $second['context']);
                }
            } else {
                return $ids;
            }
        });
*/
    }

    /**
     * Retrieve the entire catalog.
     * @return array[]
     */
    public function catalog()
    {
        return $this->set;
    }

    /**
     * Retrieve an item at the given index.
     * @param integer $index
     * @return array
     */
    public function item($index)
    {
        return $this->set[$index];
    }
    
    public function search( $string ) {
      foreach( $this->set as $item ) {
        if( $item[ 'id' ] == $string ) {
          return $item[ 'strings' ][0];
        }
      }

      return "";
    }

    public function is_fuzzy( $string ) {
      return in_array( $string, $this->_fuzzy );
    }
}