<?php

return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'api_url' => env('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1'),
    
    'default_model' => env('OPENROUTER_DEFAULT_MODEL', 'openai/gpt-3.5-turbo'),
    
    'models' => [
        // OpenAI Models
        'openai/gpt-3.5-turbo' => [
            'name' => 'GPT-3.5 Turbo',
            'provider' => 'OpenAI',
            'max_tokens' => 16385,
            'context_length' => 16385,
            'description' => 'Fast, efficient model for most tasks',
        ],
        'openai/gpt-4' => [
            'name' => 'GPT-4',
            'provider' => 'OpenAI',
            'max_tokens' => 8192,
            'context_length' => 8192,
            'description' => 'Most capable GPT-4 model',
        ],
        'openai/gpt-4-turbo' => [
            'name' => 'GPT-4 Turbo',
            'provider' => 'OpenAI',
            'max_tokens' => 128000,
            'context_length' => 128000,
            'description' => 'Latest GPT-4 Turbo with vision',
        ],
        'openai/gpt-4o' => [
            'name' => 'GPT-4o',
            'provider' => 'OpenAI',
            'max_tokens' => 4096,
            'context_length' => 128000,
            'description' => 'Optimized GPT-4 for speed and efficiency',
        ],
        'openai/gpt-4o-mini' => [
            'name' => 'GPT-4o Mini',
            'provider' => 'OpenAI',
            'max_tokens' => 16384,
            'context_length' => 128000,
            'description' => 'Small, fast, and cost-effective',
        ],
        
        // Anthropic Models
        'anthropic/claude-3-opus' => [
            'name' => 'Claude 3 Opus',
            'provider' => 'Anthropic',
            'max_tokens' => 4096,
            'context_length' => 200000,
            'description' => 'Most capable Claude model',
        ],
        'anthropic/claude-3-sonnet' => [
            'name' => 'Claude 3 Sonnet',
            'provider' => 'Anthropic',
            'max_tokens' => 4096,
            'context_length' => 200000,
            'description' => 'Balanced performance and speed',
        ],
        'anthropic/claude-3-haiku' => [
            'name' => 'Claude 3 Haiku',
            'provider' => 'Anthropic',
            'max_tokens' => 4096,
            'context_length' => 200000,
            'description' => 'Fast and cost-effective',
        ],
        'anthropic/claude-3.5-sonnet' => [
            'name' => 'Claude 3.5 Sonnet',
            'provider' => 'Anthropic',
            'max_tokens' => 8192,
            'context_length' => 200000,
            'description' => 'Latest and most capable Claude',
        ],
        
        // Google Models
        'google/gemini-pro' => [
            'name' => 'Gemini Pro',
            'provider' => 'Google',
            'max_tokens' => 8192,
            'context_length' => 32760,
            'description' => 'Google\'s most capable model',
        ],
        'google/gemini-pro-vision' => [
            'name' => 'Gemini Pro Vision',
            'provider' => 'Google',
            'max_tokens' => 4096,
            'context_length' => 16384,
            'description' => 'Multimodal model with vision',
        ],
        'google/gemini-flash-1.5' => [
            'name' => 'Gemini Flash 1.5',
            'provider' => 'Google',
            'max_tokens' => 8192,
            'context_length' => 1000000,
            'description' => 'Fast model with huge context',
        ],
        
        // Meta Models
        'meta-llama/llama-3.1-8b-instruct' => [
            'name' => 'Llama 3.1 8B',
            'provider' => 'Meta',
            'max_tokens' => 4096,
            'context_length' => 128000,
            'description' => 'Efficient open-source model',
        ],
        'meta-llama/llama-3.1-70b-instruct' => [
            'name' => 'Llama 3.1 70B',
            'provider' => 'Meta',
            'max_tokens' => 4096,
            'context_length' => 128000,
            'description' => 'Powerful open-source model',
        ],
        'meta-llama/llama-3.1-405b-instruct' => [
            'name' => 'Llama 3.1 405B',
            'provider' => 'Meta',
            'max_tokens' => 4096,
            'context_length' => 128000,
            'description' => 'Largest Llama model',
        ],
        
        // Mistral Models
        'mistralai/mistral-7b-instruct' => [
            'name' => 'Mistral 7B',
            'provider' => 'Mistral',
            'max_tokens' => 8192,
            'context_length' => 32768,
            'description' => 'Fast and efficient',
        ],
        'mistralai/mixtral-8x7b-instruct' => [
            'name' => 'Mixtral 8x7B',
            'provider' => 'Mistral',
            'max_tokens' => 4096,
            'context_length' => 32768,
            'description' => 'MoE model with great performance',
        ],
        'mistralai/mixtral-8x22b-instruct' => [
            'name' => 'Mixtral 8x22B',
            'provider' => 'Mistral',
            'max_tokens' => 8192,
            'context_length' => 65536,
            'description' => 'Large MoE model',
        ],
        
        // Perplexity Models
        'perplexity/llama-3.1-sonar-small-128k-online' => [
            'name' => 'Sonar Small (Online)',
            'provider' => 'Perplexity',
            'max_tokens' => 4096,
            'context_length' => 127072,
            'description' => 'With internet access',
        ],
        'perplexity/llama-3.1-sonar-large-128k-online' => [
            'name' => 'Sonar Large (Online)',
            'provider' => 'Perplexity',
            'max_tokens' => 4096,
            'context_length' => 127072,
            'description' => 'Powerful with internet access',
        ],
    ],
    
    'timeout' => 120,
    'max_retries' => 3,
];