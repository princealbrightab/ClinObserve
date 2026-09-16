<?php

namespace App\Contracts;

interface AiSuggestionServiceInterface
{
    public function review(array $input): array;
}
