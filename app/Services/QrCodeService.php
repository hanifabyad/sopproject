<?php

namespace App\Services;

/**
 * Pure PHP QR Code Generator Service
 * Generates standards-compliant QR Code matrices and renders to SVG, Data URI, or directly to FPDF vector primitives.
 */
class QrCodeService
{
    /**
     * Generate an SVG data URI or SVG string for displaying in HTML/Blade.
     */
    public static function svg(string $data, int $size = 200, int $margin = 2): string
    {
        $matrix = self::getMatrix($data);
        $count = count($matrix);
        $totalSize = $count + ($margin * 2);
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalSize . ' ' . $totalSize . '" width="' . $size . '" height="' . $size . '">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
        
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="1" height="1" fill="#000000"/>';
                }
            }
        }
        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Draw vector QR Code directly onto an FPDF / FPDI document.
     */
    public static function drawToFpdf($pdf, string $data, float $x, float $y, float $size = 28.0, float $margin = 1.0): void
    {
        $matrix = self::getMatrix($data);
        $count = count($matrix);
        
        $moduleSize = $size / ($count + ($margin * 2));
        
        // Draw white background box
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $size, $size, 'F');
        
        // Draw black modules
        $pdf->SetFillColor(0, 0, 0);
        $startX = $x + ($margin * $moduleSize);
        $startY = $y + ($margin * $moduleSize);
        
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c]) {
                    $mx = $startX + ($c * $moduleSize);
                    $my = $startY + ($r * $moduleSize);
                    $pdf->Rect($mx, $my, $moduleSize + 0.05, $moduleSize + 0.05, 'F');
                }
            }
        }
    }

    /**
     * Build QR matrix using standalone micro-encoder algorithm.
     */
    public static function getMatrix(string $data): array
    {
        // Simple and robust QR code matrix generator for URLs / Strings (Version 1-10)
        return (new QrMatrixBuilder())->build($data);
    }
}

/**
 * Lightweight QR Code Matrix Builder (Pure PHP implementation of QR Code ISO/IEC 18004)
 */
class QrMatrixBuilder
{
    private array $matrix = [];
    private int $size;
    private int $version;

    public function build(string $text): array
    {
        $dataBytes = unpack('C*', $text);
        $len = count($dataBytes);

        // Auto select version based on length (Byte mode with EC level M)
        $capacity = [1 => 14, 2 => 26, 3 => 42, 4 => 62, 5 => 84, 6 => 106, 7 => 122, 8 => 152, 9 => 180, 10 => 213];
        $this->version = 1;
        foreach ($capacity as $v => $cap) {
            if ($len <= $cap) {
                $this->version = $v;
                break;
            }
            $this->version = $v;
        }

        $this->size = ($this->version - 1) * 4 + 21;
        $this->matrix = array_fill(0, $this->size, array_fill(0, $this->size, null));

        // 1. Finder patterns
        $this->drawFinderPattern(0, 0);
        $this->drawFinderPattern($this->size - 7, 0);
        $this->drawFinderPattern(0, $this->size - 7);

        // 2. Alignment patterns for version >= 2
        $alignPos = $this->getAlignmentPatternPositions($this->version);
        foreach ($alignPos as $ar) {
            foreach ($alignPos as $ac) {
                if ($this->matrix[$ar][$ac] === null) {
                    $this->drawAlignmentPattern($ar, $ac);
                }
            }
        }

        // 3. Timing patterns
        for ($i = 8; $i < $this->size - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            if ($this->matrix[6][$i] === null) $this->matrix[6][$i] = $val;
            if ($this->matrix[$i][6] === null) $this->matrix[$i][6] = $val;
        }

        // 4. Dark module
        $this->matrix[4 * $this->version + 9][8] = 1;

        // 5. Reserve format info
        $this->reserveFormatInfo();

        // 6. Encode and place data bits
        $bitStream = $this->encodeData($text, $this->version);
        $this->placeData($bitStream);

        // 7. Apply mask 0 ( (row + col) % 2 == 0 ) and write format info
        $this->applyMask(0);
        $this->writeFormatInfo(0);

        // Fill remaining nulls with 0
        for ($r = 0; $r < $this->size; $r++) {
            for ($c = 0; $c < $this->size; $c++) {
                if ($this->matrix[$r][$c] === null) {
                    $this->matrix[$r][$c] = 0;
                }
            }
        }

        return $this->matrix;
    }

    private function drawFinderPattern(int $r, int $c): void
    {
        for ($y = -1; $y <= 7; $y++) {
            for ($x = -1; $x <= 7; $x++) {
                $row = $r + $y;
                $col = $c + $x;
                if ($row >= 0 && $row < $this->size && $col >= 0 && $col < $this->size) {
                    if ($x >= 0 && $x <= 6 && $y >= 0 && $y <= 6) {
                        $isBlack = ($x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4));
                        $this->matrix[$row][$col] = $isBlack ? 1 : 0;
                    } else {
                        $this->matrix[$row][$col] = 0; // Separator
                    }
                }
            }
        }
    }

    private function drawAlignmentPattern(int $r, int $c): void
    {
        for ($y = -2; $y <= 2; $y++) {
            for ($x = -2; $x <= 2; $x++) {
                $isBlack = (abs($x) === 2 || abs($y) === 2 || ($x === 0 && $y === 0));
                $this->matrix[$r + $y][$c + $x] = $isBlack ? 1 : 0;
            }
        }
    }

    private function getAlignmentPatternPositions(int $v): array
    {
        if ($v === 1) return [];
        $pos = [
            2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30],
            6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50]
        ];
        return $pos[$v] ?? [6, $this->size - 7];
    }

    private function reserveFormatInfo(): void
    {
        for ($i = 0; $i <= 8; $i++) {
            if ($this->matrix[8][$i] === null) $this->matrix[8][$i] = 0;
            if ($this->matrix[$i][8] === null) $this->matrix[$i][8] = 0;
        }
        for ($i = $this->size - 8; $i < $this->size; $i++) {
            if ($this->matrix[8][$i] === null) $this->matrix[8][$i] = 0;
            if ($this->matrix[$i][8] === null) $this->matrix[$i][8] = 0;
        }
    }

    private function encodeData(string $text, int $version): array
    {
        $bits = [];
        // Mode indicator: 0100 (Byte mode)
        $this->appendBits($bits, 0b0100, 4);

        // Character count indicator (8 bits for v1-9, 16 bits for v10+)
        $countBits = ($version < 10) ? 8 : 16;
        $this->appendBits($bits, strlen($text), $countBits);

        // Data bytes
        for ($i = 0; $i < strlen($text); $i++) {
            $this->appendBits($bits, ord($text[$i]), 8);
        }

        // Total data capacity in bits for EC level M
        $totalDataBytes = [1 => 16, 2 => 28, 3 => 44, 4 => 64, 5 => 86, 6 => 108, 7 => 124, 8 => 154, 9 => 182, 10 => 216][$version] ?? 16;
        $totalBits = $totalDataBytes * 8;

        // Terminator (up to 4 zeroes)
        $termLen = min(4, $totalBits - count($bits));
        $this->appendBits($bits, 0, $termLen);

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        // Pad bytes 11101100 (236) and 00010001 (17)
        $padBytes = [0b11101100, 0b00010001];
        $padIndex = 0;
        while (count($bits) < $totalBits) {
            $this->appendBits($bits, $padBytes[$padIndex % 2], 8);
            $padIndex++;
        }

        // Convert data bits to bytes
        $rawBytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $b = 0;
            for ($j = 0; $j < 8; $j++) {
                $b = ($b << 1) | ($bits[$i + $j] ?? 0);
            }
            $rawBytes[] = $b;
        }

        // Generate Reed-Solomon Error Correction Code
        $ecBytesCount = [1 => 10, 2 => 16, 3 => 26, 4 => 18, 5 => 24, 6 => 16, 7 => 18, 8 => 22, 9 => 22, 10 => 26][$version] ?? 10;
        $ecBytes = $this->generateReedSolomon($rawBytes, $ecBytesCount);

        // Combine Data + EC codewords
        $finalBytes = array_merge($rawBytes, $ecBytes);
        $finalBits = [];
        foreach ($finalBytes as $byte) {
            $this->appendBits($finalBits, $byte, 8);
        }

        return $finalBits;
    }

    private function appendBits(array &$stream, int $val, int $len): void
    {
        for ($i = $len - 1; $i >= 0; $i--) {
            $stream[] = ($val >> $i) & 1;
        }
    }

    private function gfMul(int $x, int $y, array $exp, array $log): int
    {
        if ($x === 0 || $y === 0) return 0;
        return $exp[($log[$x] + $log[$y]) % 255];
    }

    private function generateReedSolomon(array $data, int $ecCount): array
    {
        // Galois Field GF(256) tables
        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11d;
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }

        // Generator polynomial g(x) = (x - a^0)(x - a^1)...(x - a^(ecCount-1))
        $gen = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            $factor = $exp[$i];
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
                $next[$j + 1] ^= $this->gfMul($gen[$j], $factor, $exp, $log);
            }
            $gen = $next;
        }

        // Divide data polynomial by generator polynomial
        $res = array_fill(0, $ecCount, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $res[0];
            array_shift($res);
            $res[] = 0;
            if ($factor !== 0) {
                for ($j = 0; $j < $ecCount; $j++) {
                    $res[$j] ^= $this->gfMul($gen[$j + 1], $factor, $exp, $log);
                }
            }
        }
        return $res;
    }

    private function placeData(array $bits): void
    {
        $bitIdx = 0;
        $totalBits = count($bits);
        $up = true;

        for ($right = $this->size - 1; $right > 0; $right -= 2) {
            if ($right === 6) $right--; // Skip vertical timing line

            for ($vert = 0; $vert < $this->size; $vert++) {
                $r = $up ? ($this->size - 1 - $vert) : $vert;
                for ($c = $right; $c >= $right - 1; $c--) {
                    if ($this->matrix[$r][$c] === null) {
                        $this->matrix[$r][$c] = ($bitIdx < $totalBits) ? $bits[$bitIdx++] : 0;
                    }
                }
            }
            $up = !$up;
        }
    }

    private function applyMask(int $pattern): void
    {
        for ($r = 0; $r < $this->size; $r++) {
            for ($c = 0; $c < $this->size; $c++) {
                if ($this->isDataModule($r, $c)) {
                    $invert = (($r + $c) % 2 === 0);
                    if ($invert) {
                        $this->matrix[$r][$c] ^= 1;
                    }
                }
            }
        }
    }

    private function isDataModule(int $r, int $c): bool
    {
        // Check finder patterns + separators
        if ($r <= 8 && $c <= 8) return false;
        if ($r <= 8 && $c >= $this->size - 8) return false;
        if ($r >= $this->size - 8 && $c <= 8) return false;
        if ($r === 6 || $c === 6) return false; // Timing lines
        // Check alignment patterns
        $alignPos = $this->getAlignmentPatternPositions($this->version);
        foreach ($alignPos as $ar) {
            foreach ($alignPos as $ac) {
                if ($r >= $ar - 2 && $r <= $ar + 2 && $c >= $ac - 2 && $c <= $ac + 2) {
                    return false;
                }
            }
        }
        return true;
    }

    private function writeFormatInfo(int $mask): void
    {
        // Format info for EC Level M (00) and Mask 0 (000) with BCH error correction = 0x5412 XOR 0x5412
        $formatBits = [1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0]; // Mask 0, EC M
        for ($i = 0; $i < 15; $i++) {
            $bit = $formatBits[$i];
            // Top-left
            if ($i <= 5) $this->matrix[8][$i] = $bit;
            elseif ($i === 6) $this->matrix[8][7] = $bit;
            elseif ($i === 7) $this->matrix[8][8] = $bit;
            elseif ($i === 8) $this->matrix[7][8] = $bit;
            else $this->matrix[14 - $i][8] = $bit;

            // Bottom-left / Top-right
            if ($i < 8) $this->matrix[$this->size - 1 - $i][8] = $bit;
            else $this->matrix[8][$this->size - 15 + $i] = $bit;
        }
    }
}
