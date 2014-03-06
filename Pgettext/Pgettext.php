<?php
/*
 * (c) Ruben Nijveld <ruben@gewooniets.nl>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include_once 'po.php';
include_once 'mo.php';
include_once 'Stringset.php';

/**
 * Basic utility functions
 */
class Pgettext
{
    /**
     * Read a po file and store a mo file.
     * If no MO filename is given, one will be generated from the PO filename.
     * @param string $po Filename of the input PO file.
     * @param string $mo Filename of the output MO file.
     * @return void
     */
    public static function msgfmt($po, $mo = null)
    {
        $stringset = Po::fromFile($po);
        if ($mo === null) {
            $mo = substr($po, 0, -3) . '.mo';
        }
        CMo::toFile($stringset, $mo);
    }

    /**
     * Reads a mo file and stores the po file.
     * If no PO file was given, only displays what would be the result.
     * @param string $mo Filename of the input MO file.
     * @param string $po Filename of the output PO file.
     * @return void
     */
    public static function msgunfmt($mo, $po = null)
    {
        $stringset = CMo::fromFile($mo);
        if ($po === null) {
            print Po::toString($stringset);
        } else {
            Po::toFile($stringset, $po);
        }
    }
}