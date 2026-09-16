<?php

return [
    'timezone' => env('INSTITUTION_TIMEZONE', 'Asia/Kolkata'),
    'ai_enabled' => (bool) env('AI_ENABLED', false), 'ai_provider' => env('AI_PROVIDER', 'openai'),
    'ai_key' => env('OPENAI_API_KEY', ''), 'ai_model' => env('OPENAI_MODEL', ''),
    'password_recovery' => (bool) env('PASSWORD_RECOVERY_ENABLED', false),
    'disclaimer' => 'AI-generated educational feedback. This content is intended only to support medical education and must be reviewed by qualified faculty. It is not a diagnosis or treatment recommendation.',
];
