<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotController extends AbstractController
{
    private $httpClient;
    private $geminiApiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->geminiApiKey = 'AIzaSyDvxZkKfhR91AXdh0U6uUugDjfA8AfwA1E';
    }

    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('front/chatbot.html.twig', [
            'gemini_api_key' => $this->geminiApiKey,
        ]);
    }

    #[Route('/chatbot/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $conversation = $data['conversation'] ?? [];

        if (empty($message)) {
            return new JsonResponse(['error' => 'Message cannot be empty', 'status' => 'error'], 400);
        }

        try {
            // Format the conversation history for Gemini API
            $formattedConversation = [];
            foreach ($conversation as $entry) {
                $formattedConversation[] = [
                    'role' => $entry['role'],
                    'parts' => [['text' => $entry['content']]]
                ];
            }

            // Add the current user message
            $formattedConversation[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            // Prepare the request payload
            $payload = [
                'contents' => $formattedConversation,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ];

            // Send request to Gemini API
            $response = $this->httpClient->request('POST', 
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->geminiApiKey}",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                ]
            );

            $responseData = $response->toArray();
            
            // Extract the response text
            $responseText = '';
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                // For debugging
                return new JsonResponse([
                    'error' => 'Unexpected API response format',
                    'response_data' => $responseData,
                    'status' => 'error'
                ], 500);
            }

            return new JsonResponse([
                'response' => $responseText,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Error communicating with Gemini API: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}