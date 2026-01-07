# Chat Interface Setup Guide

## Overview
Your ChatGPT-style chat interface is now fully integrated with OpenRouter API and ready to use!

## Setup Instructions

### 1. Configure OpenRouter API Key

Add your OpenRouter API key to your `.env` file:

```env
OPENROUTER_API_KEY=your_api_key_here
OPENROUTER_API_URL=https://openrouter.ai/api/v1
```

You can get an API key from [OpenRouter](https://openrouter.ai/).

### 2. Run Migrations (Already Done)

The database tables have been created:
- `chats` - Stores chat conversations
- `messages` - Stores individual messages
- `message_attachments` - Stores document attachments

### 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Compile Assets

```bash
npm run dev
# or for production
npm run build
```

## Features Implemented

### Chat Management
- ✅ Create new chats
- ✅ List all user's chats
- ✅ Select and view chat history
- ✅ Rename chats
- ✅ Delete chats
- ✅ Auto-generate chat titles from first message

### Messaging
- ✅ Send messages to OpenRouter AI
- ✅ Display user and AI messages
- ✅ Real-time typing indicators
- ✅ Message timestamps
- ✅ Error handling with fallback

### Document Upload
- ✅ Attach documents to messages
- ✅ Support for text files (.txt, .md, .csv, .json)
- ✅ PDF support (requires `smalot/pdfparser` package)
- ✅ Word document support (requires `phpoffice/phpword` package)
- ✅ Extract and send document content with prompts
- ✅ File preview before sending

### UI/UX
- ✅ Dark theme (ChatGPT-style)
- ✅ Responsive design
- ✅ Collapsible sidebar
- ✅ Mobile-friendly
- ✅ Keyboard shortcuts (Enter to send, Shift+Enter for new line)
- ✅ Auto-expanding textarea
- ✅ Empty state messages

## Available AI Models

The following models are pre-configured in `config/openrouter.php`:

- **GPT-3.5 Turbo** (Default) - Fast and cost-effective
- **GPT-4** - More capable but slower
- **Claude 2** - Anthropic's model with large context window
- **PaLM 2** - Google's chat model

You can change the default model or add more models in the config file.

## API Endpoints

All endpoints require authentication via Sanctum:

- `GET /api/chats` - List user's chats
- `POST /api/chats` - Create new chat
- `GET /api/chats/{id}` - Get chat details with messages
- `PUT /api/chats/{id}` - Update chat (title, settings)
- `DELETE /api/chats/{id}` - Delete chat
- `POST /api/chats/{id}/messages` - Send message with optional attachments
- `POST /api/chats/{id}/upload` - Upload document

## Optional: Install Document Parsing Libraries

For better document support, install these packages:

```bash
# For PDF support
composer require smalot/pdfparser

# For Word document support
composer require phpoffice/phpword
```

## Troubleshooting

### "API key not configured" Error
Make sure you've added `OPENROUTER_API_KEY` to your `.env` file and cleared the config cache.

### Chat not loading
Check browser console for errors. Ensure you're logged in and have run migrations.

### File upload not working
Check that the `storage/app/chat-attachments` directory is writable:
```bash
chmod -R 775 storage/app
```

### OpenRouter API Errors
- Check your API key is valid
- Verify you have credits in your OpenRouter account
- Check the Laravel logs: `tail -f storage/logs/laravel.log`

## Next Steps

1. Add more AI models in `config/openrouter.php`
2. Implement streaming responses for real-time AI output
3. Add conversation export functionality
4. Implement system prompts and chat settings
5. Add support for more file types
6. Implement chat sharing functionality

## Support

For issues or questions about the chat interface, check:
- Laravel logs: `storage/logs/laravel.log`
- Browser console for JavaScript errors
- Network tab for API response details