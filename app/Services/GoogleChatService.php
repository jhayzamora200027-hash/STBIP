<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleChatService
{
    /**
     * Send notification to Google Chat via webhook
     * 
     * @param string $title
     * @param string $content
     * @param string $createdBy
     * @return bool
     */
    public static function sendNoteNotification($title, $content, $createdBy)
    {
        // Google Chat Webhook URL - you'll need to set this up
        $webhookUrl = env('GOOGLE_CHAT_WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('Google Chat webhook URL not configured');
            return false;
        }
        
        try {
            // Format message for Google Chat
            $message = [
                'text' => "🔔 *New Note Created*\n\n" .
                          "*Title:* {$title}\n\n" .
                          "*Content:*\n{$content}\n\n" .
                          "*Created by:* {$createdBy}\n" .
                          "*Date:* " . now()->format('Y-m-d H:i:s')
            ];
            
            // Alternative: Rich card format for better appearance
            $cardMessage = [
                'cards' => [
                    [
                        'header' => [
                            'title' => 'New Note Created',
                            'subtitle' => 'JTMedia Notes System',
                            'imageUrl' => 'https://img.icons8.com/color/96/000000/note.png',
                            'imageStyle' => 'IMAGE'
                        ],
                        'sections' => [
                            [
                                'widgets' => [
                                    [
                                        'keyValue' => [
                                            'topLabel' => 'Title',
                                            'content' => $title,
                                            'contentMultiline' => true
                                        ]
                                    ],
                                    [
                                        'keyValue' => [
                                            'topLabel' => 'Content',
                                            'content' => $content,
                                            'contentMultiline' => true
                                        ]
                                    ],
                                    [
                                        'keyValue' => [
                                            'topLabel' => 'Created by',
                                            'content' => $createdBy
                                        ]
                                    ],
                                    [
                                        'keyValue' => [
                                            'topLabel' => 'Date',
                                            'content' => now()->format('Y-m-d H:i:s')
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            // Send to Google Chat webhook
            $response = Http::post($webhookUrl, $cardMessage);
            
            if ($response->successful()) {
                Log::info('Google Chat notification sent successfully');
                return true;
            } else {
                Log::error('Failed to send Google Chat notification', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Error sending Google Chat notification: ' . $e->getMessage());
            return false;
        }
    }
}
