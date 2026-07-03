<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 *
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\SimpleType;

use PhpOffice\PhpWord\Shared\AbstractEnum;

/**
 * Border Styles.
 *
 * @since 0.18.0
 * @see  http://www.datypic.com/sc/ooxml/t-w_ST_Border.html
 */
final class Border extends AbstractEnum
{
    const SINGLE = 'single'; 
    const DASH_DOT_STROKED = 'dashDotStroked'; 
    const DASHED = 'dashed'; 
    const DASH_SMALL_GAP = 'dashSmallGap'; 
    const DOT_DASH = 'dotDash'; 
    const DOT_DOT_DASH = 'dotDotDash'; 
    const DOTTED = 'dotted'; 
    const DOUBLE = 'double'; 
    const DOUBLE_WAVE = 'doubleWave'; 
    const INSET = 'inset'; 
    const NIL = 'nil'; 
    const NONE = 'none'; 
    const OUTSET = 'outset'; 
    const THICK = 'thick'; 
    const THICK_THIN_LARGE_GAP = 'thickThinLargeGap'; 
    const THICK_THIN_MEDIUM_GAP = 'thickThinMediumGap'; 
    const THICK_THIN_SMALL_GAP = 'thickThinSmallGap'; 
    const THIN_THICK_LARGE_GAP = 'thinThickLargeGap'; 
    const THIN_THICK_MEDIUM_GAP = 'thinThickMediumGap'; 
    const THIN_THICK_SMALL_GAP = 'thinThickSmallGap'; 
    const THIN_THICK_THINLARGE_GAP = 'thinThickThinLargeGap'; 
    const THIN_THICK_THIN_MEDIUM_GAP = 'thinThickThinMediumGap'; 
    const THIN_THICK_THIN_SMALL_GAP = 'thinThickThinSmallGap'; 
    const THREE_D_EMBOSS = 'threeDEmboss'; 
    const THREE_D_ENGRAVE = 'threeDEngrave'; 
    const TRIPLE = 'triple'; 
    const WAVE = 'wave'; 
}
