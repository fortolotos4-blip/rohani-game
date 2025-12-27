<?php

namespace App\Services\Tts;

use Illuminate\Support\Facades\DB;

class HybridTtsGenerator
{
    protected int $size;
    protected array $grid = [];
    protected array $entries = [];
    protected array $usedWords = [];
    protected int $currentNumber = 1;
    protected int $number = 1;
    protected array $startCells = [];
    protected array $cellNumbers = [];

    protected function reset(): void
{
    $this->grid = [];
    $this->entries = [];
    $this->usedWords = [];
    $this->startCells = [];
    $this->number = 1;
    $this->currentNumber = 1;

    $this->initGrid();
}



    public function __construct(int $size = 10)
    {
        $this->size = $size;
        $this->initGrid();
    }

    protected function isStartCellFree(int $x, int $y): bool
{
    return !isset($this->startCells["{$y}_{$x}"]);
}

    /* =========================
       INIT GRID
    ========================== */
    protected function initGrid(): void
    {
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $this->grid[$y][$x] = null;
            }
        }
    }

    protected function buildFromCollection($words): array
{
    $this->reset();

    // =====================
    // KATA PERTAMA (WAJIB)
    // =====================
    $first = $words->shift();

    $startX = rand(0, max(0, $this->size - strlen($first->word)));
    $startY = rand(2, $this->size - 3);

    $this->placeAcross(
        strtoupper($first->word),
        $startX,
        $startY,
        $first->clue
    );

    // 🔒 CATAT KATA PERTAMA
    $this->usedWords[] = strtoupper($first->word);

    // =====================
    // KATA BERIKUTNYA
    // =====================
    foreach ($words as $w) {

        $word = strtoupper($w->word);

        // 🚫 SKIP JIKA SUDAH DIPAKAI
        if (in_array($word, $this->usedWords)) {
            continue;
        }

        // ✅ COBA TEMPATKAN
        $placed = $this->tryPlaceCross($word, $w->clue);

        // 🔒 HANYA CATAT JIKA BERHASIL
        if ($placed) {
            $this->usedWords[] = $word;
        }

        // 🔥 BATASI JUMLAH SOAL (AMAN)
        if ($this->currentNumber > 25) {
            break;
        }
    }

    return [
        'grid' => $this->grid,
        'entries' => $this->entries,
    ];
}



    /* =========================
       GENERATE PUZZLE
    ========================== */
    public function generate(string $difficulty = 'easy'): array
    {
        $words = DB::table('tts_words')
            ->where('difficulty', $difficulty)
            ->orderBy('length', 'desc')
            ->get();

        if ($words->isEmpty()) {
            throw new \Exception('Tidak ada soal TTS');
        }

        // 🔹 kata pertama (mendatar acak)
        $first = $words->shift();

        $maxX = $this->size - strlen($first->word);
        $startX = rand(0, max(0, $maxX));
        $startY = rand(2, $this->size - 3);

        $this->placeAcross(
            strtoupper($first->word),
            $startX,
            $startY,
            $first->clue
        );

        $this->usedWords[] = $first->word;

        // 🔹 kata lain disilangkan
        foreach ($words as $w) {
            if (in_array($w->word, $this->usedWords)) {
                continue;
            }

            if ($this->tryPlaceCross(
                strtoupper($w->word),
                $w->clue
            )) {
                $this->usedWords[] = $w->word;
            }

            if ($this->currentNumber > 25) break;
        }

        
        
        return [
            'grid' => $this->grid,
            'entries' => $this->entries,
        ];
    }

    /* =========================
       TRY PLACE CROSS
    ========================== */
    protected function tryPlaceCross(string $word, string $clue): bool
    {
        foreach ($this->entries as $e) {
            $baseWord = $e['word'];

            for ($i = 0; $i < strlen($baseWord); $i++) {
                for ($j = 0; $j < strlen($word); $j++) {

                    if ($baseWord[$i] !== $word[$j]) continue;

                    if ($e['direction'] === 'across') {
                        $x = $e['x'] + $i;
                        $y = $e['y'] - $j;
                        $dir = 'down';
                    } else {
                        $x = $e['x'] - $j;
                        $y = $e['y'] + $i;
                        $dir = 'across';
                    }

                    if ($this->canPlace($word, $x, $y, $dir)) {
                        $dir === 'down'
                            ? $this->placeDown($word, $x, $y, $clue)
                            : $this->placeAcross($word, $x, $y, $clue);

                        return true;
                    }
                }
            }
        }
        return false;
    }

    /* =========================
       VALIDATE PLACEMENT
    ========================== */

    protected function canPlace(string $word, int $x, int $y, string $dir): bool
    {
        // 🚫 START CELL SUDAH DIPAKAI
        if (!$this->isStartCellFree($x, $y)) {
            return false;
        }

        // 🚫 CEK SEBELUM START (aturan crossword)
        if ($dir === 'across') {
            if ($x > 0 && $this->grid[$y][$x - 1] !== null) {
                return false;
            }
        } else {
            if ($y > 0 && $this->grid[$y - 1][$x] !== null) {
                return false;
            }
        }

        for ($i = 0; $i < strlen($word); $i++) {
            $cx = $dir === 'across' ? $x + $i : $x;
            $cy = $dir === 'down'   ? $y + $i : $y;

            if ($cx < 0 || $cy < 0 || $cx >= $this->size || $cy >= $this->size) {
                return false;
            }

            if ($this->grid[$cy][$cx] !== null &&
                $this->grid[$cy][$cx] !== $word[$i]) {
                return false;
            }
        }

        return true;
    }


    /* =========================
       PLACE WORD
    ========================== */

    protected function placeAcross(string $word, int $x, int $y, string $clue): void
{
    for ($i = 0; $i < strlen($word); $i++) {
        $this->grid[$y][$x + $i] = $word[$i];
    }

    // 🔒 tandai start cell
    $this->startCells["{$y}_{$x}"] = true;

    $this->entries[] = [
        'number' => $this->number++,
        'direction' => 'across',
        'x' => $x,
        'y' => $y,
        'length' => strlen($word),
        'clue' => $clue,
        'word' => $word,
    ];
}


    protected function placeDown(string $word, int $x, int $y, string $clue): void
{
    for ($i = 0; $i < strlen($word); $i++) {
        $this->grid[$y + $i][$x] = $word[$i];
    }

    // 🔒 tandai start cell
    $this->startCells["{$y}_{$x}"] = true;

    $this->entries[] = [
        'number' => $this->number++,
        'direction' => 'down',
        'x' => $x,
        'y' => $y,
        'length' => strlen($word),
        'clue' => $clue,
        'word' => $word,
    ];
}

public function generateMixed(): array
{
    $words = DB::table('tts_words')
    ->select('word', 'clue')
    ->groupBy('word', 'clue')
    ->inRandomOrder()
    ->limit(30)
    ->get();

    if ($words->isEmpty()) {
        throw new \Exception('Tidak ada soal');
    }

    return $this->buildFromCollection($words);
}

}
