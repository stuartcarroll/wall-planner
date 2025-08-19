<?php
namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class ColorDescriptionService
{
    public function generateDescription($hexColor)
    {
        $result = OpenAI::completions()->create([
            'model' => 'gpt-3.5-turbo',
            'prompt' => "Describe the color {$hexColor} in a creative, artistic way suitable for paint catalog. Keep it under 50 words.",
            'max_tokens' => 100,
        ]);

        return trim($result['choices'][0]['text']);
    }
}