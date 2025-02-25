<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ChunkController extends Controller
{
    public function chunkText($text, $chunkSize, $overlap, Command $command = null)
    {
        $chunks = [];
        $length = Str::length($text);
        $steps = ceil($length / ($chunkSize - $overlap));

        if ($command) {
            $command->withProgressBar(range(0, $steps - 1), function ($i) use (&$chunks, $text, $chunkSize, $overlap) {
                $chunks[] = Str::substr($text, $i * ($chunkSize - $overlap), $chunkSize);
            });
        } else {
            for ($i = 0; $i < $length; $i += ($chunkSize - $overlap)) {
                $chunks[] = Str::substr($text, $i, $chunkSize);
            }
        }

        return $chunks;
    }
}
