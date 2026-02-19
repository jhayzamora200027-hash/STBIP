# Google Chat/Hangouts Notification Setup Guide

## Overview
This system sends automated notifications to Google Chat (formerly Hangouts) whenever a new note is created, specifically to jhayzamaora200027@gmail.com.

## Setup Instructions

### Step 1: Create a Google Chat Space (if needed)
1. Open **Google Chat** at [chat.google.com](https://chat.google.com)
2. Sign in with **jhayzamaora200027@gmail.com**
3. Create a new space or use an existing one for notifications

### Step 2: Add a Webhook to the Space
1. In your Google Chat space, click on the **space name** at the top
2. Select **Apps & integrations**
3. Click **Add webhooks**
4. Create a new webhook:
   - **Name**: JTMedia Notes Notifications
   - **Avatar URL** (optional): You can add an icon URL
5. Click **Save**
6. **Copy the Webhook URL** - it will look like:
   ```
   https://chat.googleapis.com/v1/spaces/XXXXX/messages?key=XXXXX&token=XXXXX
   ```

### Step 3: Configure Your Laravel Application
1. Open your `.env` file
2. Find the line `GOOGLE_CHAT_WEBHOOK_URL=`
3. Paste your webhook URL:
   ```env
   GOOGLE_CHAT_WEBHOOK_URL=https://chat.googleapis.com/v1/spaces/XXXXX/messages?key=XXXXX&token=XXXXX
   ```
4. Save the file

### Step 4: Clear Configuration Cache (if in production)
```bash
php artisan config:clear
php artisan cache:clear
```

## Testing
1. Create a new note in your application
2. Check the Google Chat space for the notification
3. The notification will include:
   - 🔔 Note title
   - Note content
   - Creator's name
   - Creation date/time

## Notification Format
The notification appears as a rich card with:
- **Header**: "New Note Created" with JTMedia branding
- **Title**: The note's title
- **Content**: The note's content
- **Created by**: Username of the creator
- **Date**: Timestamp of creation

## Troubleshooting

### Not receiving notifications?
1. **Check webhook URL**: Make sure it's correctly copied in `.env`
2. **Check logs**: Look at `storage/logs/laravel.log` for error messages
3. **Verify space access**: Ensure jhayzamaora200027@gmail.com has access to the space
4. **Test webhook manually**: Use a tool like Postman to send a test message:
   ```bash
   curl -X POST 'YOUR_WEBHOOK_URL' \
   -H 'Content-Type: application/json' \
   -d '{"text": "Test message"}'
   ```

### Webhook expired?
- Webhooks can expire or be revoked
- Simply create a new webhook and update the `.env` file

### Want to change the notification format?
- Edit `app/Services/GoogleChatService.php`
- Modify the `$cardMessage` array structure
- See [Google Chat API Card Formatting](https://developers.google.com/chat/api/guides/message-formats/cards)

## Features
✅ Automatic notification on note creation  
✅ Rich card format with icons  
✅ Includes all note details  
✅ Email notification continues to work  
✅ Error logging for debugging  
✅ Graceful failure (won't break note creation if webhook fails)

## Security Notes
- Keep your webhook URL private (it's in `.env`, not in git)
- The webhook URL grants access to post to that space
- Anyone with the URL can send messages to the space
- Regenerate the webhook if compromised

## Additional Configuration

### To send to multiple spaces:
Edit `app/Services/GoogleChatService.php` and add multiple webhook URLs:
```php
$webhookUrls = [
    env('GOOGLE_CHAT_WEBHOOK_URL'),
    env('GOOGLE_CHAT_WEBHOOK_URL_2'),
    // Add more as needed
];
```

### To customize messages per user:
Add logic in the service to format messages differently based on the creator or note content.

## Support
If you encounter issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Google Chat webhook status in space settings
3. Network connectivity to Google APIs
