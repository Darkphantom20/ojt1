<?php
/**
 * TCPDF-like PDF Generator for Daily Reports
 * A lightweight PDF generation class compatible with TCPDF syntax
 */

class TCPDF {
    protected $page;
    protected $drawColor = [0, 0, 0];
    protected $links = [];
    protected $angle = 0;
    protected $fonts = [];
    protected $pageWidth = 210;
    protected $pageHeight = 297;
    protected $marginLeft = 20;
    protected $marginTop = 20;
    protected $marginRight = 20;
    protected $marginBottom = 20;
    protected $x = 0;
    protected $y = 0;
    protected $headerHeight = 0;
    protected $footerHeight = 0;
    protected $title = '';
    protected $subject = '';
    protected $author = '';
    protected $creator = 'OJT Daily Report Generator';
    protected $keywords = '';
    protected $fontFamily = 'helvetica';
    protected $fontStyle = '';
    protected $fontSize = 10;
    protected $textColor = [0, 0, 0];
    protected $fillColor = [255, 255, 255];
    protected $lineWidth = 0.2;
    protected $currentFont = null;
    protected $buffer = '';
    protected $pages = [];
    protected $state = 0;
    protected $compress = false;
    
    
    const COLOR_BLACK = [0, 0, 0];
    const COLOR_WHITE = [255, 255, 255];
    const COLOR_BLUE = [0, 0, 255];
    const COLOR_RED = [255, 0, 0];
    const COLOR_GREEN = [0, 128, 0];
    const COLOR_GRAY = [128, 128, 128];
    const COLOR_LIGHT_GRAY = [240, 240, 240];
    
    /**
     * Constructor
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8') {
        $this->SetFont($this->fontFamily, $this->fontStyle, $this->fontSize);
        $this->setPageFormat($format, $orientation);
    }
    
    /**
     * Set page format
     */
    protected function setPageFormat($format, $orientation) {
        $formats = [
            'A4' => [210, 297],
            'A5' => [148, 210],
            'Letter' => [215.9, 279.4],
            'Legal' => [215.9, 355.6]
        ];
        
        $size = $formats[$format] ?? $formats['A4'];
        
        if ($orientation === 'L') {
            $this->pageWidth = $size[1];
            $this->pageHeight = $size[0];
        } else {
            $this->pageWidth = $size[0];
            $this->pageHeight = $size[1];
        }
    }
    
    /**
     * Set font family, style, and size
     */
    public function SetFont($family, $style = '', $size = null) {
        $this->fontFamily = strtolower($family);
        $this->fontStyle = strtoupper($style);
        
        if ($size !== null) {
            $this->fontSize = $size;
        }
        
        $this->currentFont = [
            'family' => $this->fontFamily,
            'style' => $this->fontStyle,
            'size' => $this->fontSize
        ];
        
        return $this;
    }
    
    /**
     * Set text color
     */
    public function SetTextColor($r, $g = -1, $b = -1) {
        if ($g == -1) {
            $this->textColor = [$r, $r, $r];
        } else {
            $this->textColor = [$r, $g, $b];
        }
        return $this;
    }
    
    /**
     * Set fill color
     */
    public function SetFillColor($r, $g = -1, $b = -1) {
        if ($g == -1) {
            $this->fillColor = [$r, $r, $r];
        } else {
            $this->fillColor = [$r, $g, $b];
        }
        return $this;
    }
    
    /**
     * Set draw color (for lines and borders)
     */
    public function SetDrawColor($r, $g = -1, $b = -1) {
        if ($g == -1) {
            $this->drawColor = [$r, $r, $r];
        } else {
            $this->drawColor = [$r, $g, $b];
        }
        return $this;
    }
    
    /**
     * Set line width
     */
    public function SetLineWidth($width) {
        $this->lineWidth = $width;
        return $this;
    }
    
    /**
     * Create a new page
     */
    public function AddPage($orientation = '', $format = '', $keepMargins = false) {
        if (!empty($orientation)) {
            $this->setPageFormat($format ?? 'A4', $orientation);
        }
        
        $this->pages[] = [
            'width' => $this->pageWidth,
            'height' => $this->pageHeight,
            'content' => ''
        ];
        
        $this->page = count($this->pages);
        $this->x = $this->marginLeft;
        $this->y = $this->marginTop;
        
        return $this;
    }
    
    /**
     * Get current page number
     */
    public function getPage() {
        return $this->page;
    }
    
    /**
     * Set X position
     */
    public function SetX($x) {
        $this->x = $x;
        return $this;
    }
    
    /**
     * Set Y position
     */
    public function SetY($y, $moveToNextLine = false) {
        $this->y = $y;
        if ($moveToNextLine) {
            $this->x = $this->marginLeft;
        }
        return $this;
    }
    
    /**
     * Set XY position
     */
    public function SetXY($x, $y) {
        $this->x = $x;
        $this->y = $y;
        return $this;
    }
    
    /**
     * Get X position
     */
    public function GetX() {
        return $this->x;
    }
    
    /**
     * Get Y position
     */
    public function GetY() {
        return $this->y;
    }
    
    /**
     * Get page width
     */
    public function getPageWidth() {
        return $this->pageWidth;
    }
    
    /**
     * Get page height
     */
    public function getPageHeight() {
        return $this->pageHeight;
    }
    
    /**
     * Get margins
     */
    public function getMargins() {
        return [
            'left' => $this->marginLeft,
            'top' => $this->marginTop,
            'right' => $this->marginRight,
            'bottom' => $this->marginBottom
        ];
    }
    
    /**
     * Cell - Output a cell (rectangular area) with optional borders, background and text
     */
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = 'L', $fill = false, $link = '') {
        $k = 72 / 25.4; 
        $w = $w * $k;
        $h = $h * $k;
        
        $x = $this->x * $k;
        $y = $this->y * $k;
        
        $txt = $this->utf8ToLatin1($txt);
        
        $currentPage = &$this->pages[$this->page - 1];
        
        
        $borderStr = '';
        if ($border == '1' || $border === 1) {
            $borderStr = 'stroke';
        } elseif ($border === 'LTR') {
            $borderStr = 'stroke';
        }
        
        $bg = '';
        if ($fill) {
            $bg = sprintf('fill="%s"', $this->colorToHex($this->fillColor));
        }
        
        $textColor = sprintf('#%02x%02x%02x', $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        $fontSize = $this->fontSize * 0.75;
        
        $align = strtolower($align);
        $textAnchor = 'start';
        if ($align === 'c' || $align === 'center') {
            $textAnchor = 'middle';
            $x += $w / 2;
        } elseif ($align === 'r' || $align === 'right') {
            $textAnchor = 'end';
            $x += $w;
        }
        
        $currentPage['content'] .= sprintf(
            '<text x="%.2f" y="%.2f" font-family="%s" font-size="%.1f" fill="%s" text-anchor="%s">%s</text>',
            $x + 2, $y + $fontSize, $this->fontFamily, $fontSize, $textColor, $textAnchor, htmlspecialchars($txt)
        );
        
        if ($ln == 1 || $ln === true) {
            $this->y += $h / $k;
            $this->x = $this->marginLeft;
        } elseif ($ln == 0) {
            $this->x += $w / $k;
        }
        
        return $this;
    }
    
    /**
     * MultiCell - Output a multi-line cell
     */
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'L', $fill = false, $maxline = 0) {
        $txt = $this->utf8ToLatin1($txt);
        $w = $w / 0.3527; 
        
        $words = explode(' ', $txt);
        $line = '';
        $lines = [];
        foreach ($words as $word) {
            $testLine = empty($line) ? $word : $line . ' ' . $word;
            $testWidth = $this->GetStringWidth($testLine);
            if ($testWidth > $w && !empty($line)) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $testLine;
            }
        }
        if (!empty($line)) {
            $lines[] = $line;
        }
        foreach ($lines as $lineText) {
            $this->Cell($w * 0.3527, $h, $lineText, $border, 1, $align, $fill);
        }
        return $this;
    }
    
    /**
     * Get string width
     */
    public function GetStringWidth($s) {
        $s = $this->utf8ToLatin1($s);
        return strlen($s) * $this->fontSize * 0.4;
    }
    
    /**
     * Line - Draw a line
     */
    public function Line($x1, $y1, $x2, $y2) {
        $k = 72 / 25.4;
        $x1 *= $k;
        $y1 *= $k;
        $x2 *= $k;
        $y2 *= $k;
        
        $currentPage = &$this->pages[$this->page - 1];
        $color = sprintf('#%02x%02x%02x', $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        
        $currentPage['content'] .= sprintf(
            '<line x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f" stroke="%s" stroke-width="%.1f"/>',
            $x1, $y1, $x2, $y2, $color, $this->lineWidth
        );
        
        return $this;
    }
    
    /**
     * Rect - Draw a rectangle
     */
    public function Rect($x, $y, $w, $h, $style = '') {
        $k = 72 / 25.4;
        $x *= $k;
        $y *= $k;
        $w *= $k;
        $h *= $k;
        
        $currentPage = &$this->pages[$this->page - 1];
        $color = sprintf('#%02x%02x%02x', $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        
        $fill = '';
        if ($style === 'F') {
            $fill = sprintf('fill="%s"', $color);
        }
        
        $stroke = '';
        if ($style === 'FD' || $style === 'DF' || $style === 'D') {
            $stroke = sprintf('stroke="%s" stroke-width="%.1f"', $color, $this->lineWidth);
        }
        
        $currentPage['content'] .= sprintf(
            '<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" %s %s/>',
            $x, $y, $w, $h, $fill, $stroke
        );
        
        return $this;
    }
    
    /**
     * Image - Output an image
     */
    public function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $link = '') {
        if (!file_exists($file)) {
            return $this;
        }
        
        $k = 72 / 25.4;
        
        if ($x === null) {
            $x = $this->x;
        }
        if ($y === null) {
            $y = $this->y;
        }
        
        $x *= $k;
        $y *= $k;
        
        
        $info = getimagesize($file);
        if (!$info) {
            return $this;
        }
        
        $imgWidth = $info[0];
        $imgHeight = $info[1];
        $ratio = $imgWidth / $imgHeight;
        
        
        if ($w == 0 && $h == 0) {
            $w = 50;
            $h = $w / $ratio;
        } elseif ($w == 0) {
            $w = $h * $ratio;
        } elseif ($h == 0) {
            $h = $w / $ratio;
        }
        
        $w *= $k;
        $h *= $k;
        
        
        $data = file_get_contents($file);
        $base64 = base64_encode($data);
        
        $mimeType = $info['mime'];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        $mimeType = $mimeMap[$ext] ?? $mimeType;
        
        $currentPage = &$this->pages[$this->page - 1];
        $currentPage['content'] .= sprintf(
            '<image x="%.2f" y="%.2f" width="%.2f" height="%.2f" preserveAspectRatio="xMidYMid meet"><image href="data:%s;base64,%s"/></image>',
            $x, $y, $w, $h, $mimeType, $base64
        );
        
        $this->x += $w / $k;
        
        return $this;
    }
    
    /**
     * Ln - Move to next line
     */
    public function Ln($h = '') {
        if ($h === '') {
            $h = $this->fontSize * 1.5 / 10;
        }
        
        $this->y += $h;
        $this->x = $this->marginLeft;
        
        return $this;
    }
    
    /**
     * Get remaining width
     */
    public function GetRemainingWidth() {
        return $this->pageWidth - $this->marginRight - $this->x;
    }
    
    /**
     * Set title
     */
    public function SetTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Set subject
     */
    public function SetSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Set author
     */
    public function SetAuthor($author) {
        $this->author = $author;
        return $this;
    }
    
    /**
     * Set creator
     */
    public function SetCreator($creator) {
        $this->creator = $creator;
        return $this;
    }
    
    /**
     * Set keywords
     */
    public function SetKeywords($keywords) {
        $this->keywords = $keywords;
        return $this;
    }
    
    /**
     * Output PDF to file or browser
     */
    public function Output($name = '', $dest = 'I') {
        $pdf = $this->makePDF();
        
        if ($dest === 'F') {
            return file_put_contents($name, $pdf);
        }
        
        
        if ($dest === 'S') {
            return $pdf;
        }
        
        
        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($pdf));
        header('Content-Disposition: inline; filename="' . $name . '"');
        
        echo $pdf;
        return '';
    }
    
    /**
     * Save PDF to file
     */
    public function Save($filepath) {
        $pdf = $this->makePDF();
        
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        return file_put_contents($filepath, $pdf);
    }
    
    /**
     * Convert UTF-8 to Latin1
     */
    protected function utf8ToLatin1($txt) {
        if (mb_detect_encoding($txt, 'UTF-8', true) === 'UTF-8') {
            $txt = mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
        }
        return $txt;
    }
    
    /**
     * Convert color array to hex
     */
    protected function colorToHex($color) {
        return sprintf('#%02x%02x%02x', $color[0], $color[1], $color[2]);
    }
    
    /**
     * Generate PDF content
     */
    protected function makePDF() {
        $pdf = "%PDF-1.4\n";
        
        
        
        
        $output = $this->generateSimplePDF();
        
        return $output;
    }
    
    /**
     * Generate a simple PDF using FPDF-compatible approach
     */
    protected function generateSimplePDF() {
        $k = 72 / 25.4;
        
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        
        $objectCount = 2;
        $pageRefs = [];
        
        foreach ($this->pages as $idx => $page) {
            $objectCount++;
            $pageRefs[] = $objectCount . " 0 R";
            
            $width = $page['width'] * $k;
            $height = $page['height'] * $k;
            
            $pdf .= $objectCount . " 0 obj\n";
            $pdf .= "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . sprintf("%.2f", $width) . " " . sprintf("%.2f", $height) . "] /Contents " . ($objectCount + 1) . " 0 R /Resources << >> >>\n";
            $pdf .= "endobj\n";
            
            $objectCount++;
            
            $content = $this->generatePageContent($page, $width, $height);
            $pdf .= $objectCount . " 0 obj\n";
            $pdf .= "<< /Length " . strlen($content) . " >>\n";
            $pdf .= "stream\n";
            $pdf .= $content;
            $pdf .= "endstream\n";
            $pdf .= "endobj\n";
        }
        
        $pdf .= "2 0 obj\n";
        $pdf .= "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($this->pages) . " >>\n";
        $pdf .= "endobj\n";
        
        
        $objectCount++;
        $pdf .= $objectCount . " 0 obj\n";
        $pdf .= "<< /Title (" . $this->escapePDF($this->title) . ") /Subject (" . $this->escapePDF($this->subject) . ") /Author (" . $this->escapePDF($this->author) . ") /Creator (" . $this->escapePDF($this->creator) . ") >>\n";
        $pdf .= "endobj\n";
        
        $pdf .= "xref\n";
        $pdf .= "0 " . ($objectCount + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        
        for ($i = 1; $i <= $objectCount; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", ($i - 1) * 100);
        }
        
        $pdf .= "trailer\n";
        $pdf .= "<< /Size " . ($objectCount + 1) . " /Root 1 0 R /Info " . $objectCount . " 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= strlen($pdf) . "\n";
        $pdf .= "%%EOF\n";
        
        return $pdf;
    }
    
    /**
     * Generate page content
     */
    protected function generatePageContent($page, $width, $height) {
        
        return "";
    }
    
    /**
     * Escape special PDF characters
     */
    protected function escapePDF($txt) {
        $txt = str_replace('\\', '\\\\', $txt);
        $txt = str_replace('(', '\\(', $txt);
        $txt = str_replace(')', '\\)', $txt);
        return $txt;
    }
    
    /**
     * Write HTML (simplified)
     */
    public function WriteHTML($html, $link = '') {
        
        $txt = strip_tags($html);
        $this->Write(5, $txt, $link);
    }
    
    /**
     * Write text
     */
    public function Write($h, $txt, $link = '') {
        $txt = $this->utf8ToLatin1($txt);
        
        $currentPage = &$this->pages[$this->page - 1];
        $k = 72 / 25.4;
        
        $x = $this->x * $k;
        $y = $this->y * $k;
        $fontSize = $this->fontSize * 0.75;
        
        $textColor = sprintf('#%02x%02x%02x', $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        
        $currentPage['content'] .= sprintf(
            '<text x="%.2f" y="%.2f" font-family="%s" font-size="%.1f" fill="%s">%s</text>',
            $x, $y + $fontSize, $this->fontFamily, $fontSize, $textColor, htmlspecialchars($txt)
        );
        
        $this->Ln($h);
    }
    
    /**
     * Set auto page break
     */
    public function SetAutoPageBreak($auto, $margin = 0) {
        
    }
    
    /**
     * Add link
     */
    public function AddLink() {
        return count($this->links) + 1;
    }
    
    /**
     * Set link
     */
    public function SetLink($link, $y = 0, $page = -1) {
        
    }
}

/**
 * Create TCPDF instance
 */
function createTCPDF($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8') {
    return new TCPDF($orientation, $unit, $format, $unicode, $encoding);
}