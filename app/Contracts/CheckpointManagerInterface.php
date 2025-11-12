<?php

namespace App\Contracts;

interface CheckpointManagerInterface
{
    /**
     * Save current page as checkpoint.
     */
    public function save(int $page): void;

    /**
     * Get saved checkpoint page.
     */
    public function get(): ?int;

    /**
     * Clear saved checkpoint.
     */
    public function clear(): void;

    /**
     * Check if there is a saved checkpoint to resume from.
     */
    public function hasCheckpoint(): bool;
}
