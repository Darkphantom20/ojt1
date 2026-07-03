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
 * Numbering Format.
 *
 * @since 0.14.0
 * @see http://www.datypic.com/sc/ooxml/t-w_ST_NumberFormat.html.
 */
final class NumberFormat extends AbstractEnum
{
    
    const DECIMAL = 'decimal';
    
    const UPPER_ROMAN = 'upperRoman';
    
    const LOWER_ROMAN = 'lowerRoman';
    
    const UPPER_LETTER = 'upperLetter';
    
    const LOWER_LETTER = 'lowerLetter';
    
    const ORDINAL = 'ordinal';
    
    const CARDINAL_TEXT = 'cardinalText';
    
    const ORDINAL_TEXT = 'ordinalText';
    
    const HEX = 'hex';
    
    const CHICAGO = 'chicago';
    
    const IDEOGRAPH_DIGITAL = 'ideographDigital';
    
    const JAPANESE_COUNTING = 'japaneseCounting';
    
    const AIUEO = 'aiueo';
    
    const IROHA = 'iroha';
    
    const DECIMAL_FULL_WIDTH = 'decimalFullWidth';
    
    const DECIMAL_HALF_WIDTH = 'decimalHalfWidth';
    
    const JAPANESE_LEGAL = 'japaneseLegal';
    
    const JAPANESE_DIGITAL_TEN_THOUSAND = 'japaneseDigitalTenThousand';
    
    const DECIMAL_ENCLOSED_CIRCLE = 'decimalEnclosedCircle';
    
    const DECIMAL_FULL_WIDTH2 = 'decimalFullWidth2';
    
    const AIUEO_FULL_WIDTH = 'aiueoFullWidth';
    
    const IROHA_FULL_WIDTH = 'irohaFullWidth';
    
    const DECIMAL_ZERO = 'decimalZero';
    
    const BULLET = 'bullet';
    
    const GANADA = 'ganada';
    
    const CHOSUNG = 'chosung';
    
    const DECIMAL_ENCLOSED_FULL_STOP = 'decimalEnclosedFullstop';
    
    const DECIMAL_ENCLOSED_PAREN = 'decimalEnclosedParen';
    
    const DECIMAL_ENCLOSED_CIRCLE_CHINESE = 'decimalEnclosedCircleChinese';
    
    const IDEOGRAPHENCLOSEDCIRCLE = 'ideographEnclosedCircle';
    
    const IDEOGRAPH_TRADITIONAL = 'ideographTraditional';
    
    const IDEOGRAPH_ZODIAC = 'ideographZodiac';
    
    const IDEOGRAPH_ZODIAC_TRADITIONAL = 'ideographZodiacTraditional';
    
    const TAIWANESE_COUNTING = 'taiwaneseCounting';
    
    const IDEOGRAPH_LEGAL_TRADITIONAL = 'ideographLegalTraditional';
    
    const TAIWANESE_COUNTING_THOUSAND = 'taiwaneseCountingThousand';
    
    const TAIWANESE_DIGITAL = 'taiwaneseDigital';
    
    const CHINESE_COUNTING = 'chineseCounting';
    
    const CHINESE_LEGAL_SIMPLIFIED = 'chineseLegalSimplified';
    
    const CHINESE_COUNTING_THOUSAND = 'chineseCountingThousand';
    
    const KOREAN_DIGITAL = 'koreanDigital';
    
    const KOREAN_COUNTING = 'koreanCounting';
    
    const KOREAN_LEGAL = 'koreanLegal';
    
    const KOREAN_DIGITAL2 = 'koreanDigital2';
    
    const VIETNAMESE_COUNTING = 'vietnameseCounting';
    
    const RUSSIAN_LOWER = 'russianLower';
    
    const RUSSIAN_UPPER = 'russianUpper';
    
    const NONE = 'none';
    
    const NUMBER_IN_DASH = 'numberInDash';
    
    const HEBREW1 = 'hebrew1';
    
    const HEBREW2 = 'hebrew2';
    
    const ARABIC_ALPHA = 'arabicAlpha';
    
    const ARABIC_ABJAD = 'arabicAbjad';
    
    const HINDI_VOWELS = 'hindiVowels';
    
    const HINDI_CONSONANTS = 'hindiConsonants';
    
    const HINDI_NUMBERS = 'hindiNumbers';
    
    const HINDI_COUNTING = 'hindiCounting';
    
    const THAI_LETTERS = 'thaiLetters';
    
    const THAI_NUMBERS = 'thaiNumbers';
    
    const THAI_COUNTING = 'thaiCounting';
}
