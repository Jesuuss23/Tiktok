<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TikTokApiService
{
    protected $client;
    protected $baseUrl = 'https://open.tiktokapis.com/v2/';
    protected $accessToken;
    protected $cacheTime = 300; // 5 minutos

    public function __construct($accessToken = null)
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 15.0,
            'http_errors' => false
        ]);
        
        $this->accessToken = $accessToken;
    }

    /**
     * Obtener información completa del usuario
     */
    public function getUserInfo()
    {
        // Intentar obtener de caché primero
        $cacheKey = 'tiktok_user_info_' . md5($this->accessToken);
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->get('user/info/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'fields' => 'open_id,union_id,avatar_url,display_name,follower_count,following_count,likes_count,video_count,is_verified'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            Log::info('TikTok API Response', [
                'status' => $statusCode,
                'body' => $body
            ]);

            if ($statusCode === 200 && isset($body['data'])) {
                $result = [
                    'success' => true,
                    'data' => $body['data']
                ];
                
                // Guardar en caché
                Cache::put($cacheKey, $result, $this->cacheTime);
                
                return $result;
            }

            return [
                'success' => false,
                'error' => $body['error']['message'] ?? 'Error desconocido',
                'code' => $statusCode
            ];
            
        } catch (\Exception $e) {
            Log::error('TikTok API Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener videos del usuario
     */
    public function getVideos($cursor = null, $maxCount = 20)
    {
        try {
            $requestBody = [
                'max_count' => $maxCount,
                'fields' => [
                    'id',
                    'title',
                    'video_description',
                    'duration',
                    'cover_image_url',
                    'view_count',
                    'like_count',
                    'comment_count',
                    'share_count',
                    'create_time'
                ]
            ];
            
            if ($cursor) {
                $requestBody['cursor'] = $cursor;
            }

            $response = $this->client->post('video/list/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            
            Log::info('TikTok Videos Response', [
                'status' => $statusCode,
                'count' => count($body['data']['videos'] ?? [])
            ]);

            return [
                'success' => $statusCode === 200,
                'data' => $body['data'] ?? null,
                'error' => $body['error']['message'] ?? null
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching videos: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener seguidores
     */
    public function getFollowers($cursor = null, $maxCount = 50)
    {
        try {
            $query = [
                'max_count' => $maxCount
            ];
            
            if ($cursor) {
                $query['cursor'] = $cursor;
            }

            $response = $this->client->get('user/follower/list/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
                'query' => $query
            ]);

            $body = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'data' => $body['data'] ?? null
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching followers: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener analytics del usuario
     */
    public function getAnalytics($days = 7)
    {
        try {
            $response = $this->client->post('research/user/info/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'fields' => [
                        'follower_count',
                        'following_count',
                        'heart_count',
                        'video_count'
                    ]
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'data' => $body['data'] ?? null
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching analytics: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refrescar token de acceso
     */
    public function refreshToken($refreshToken)
    {
        try {
            $response = $this->client->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'form_params' => [
                    'client_key' => config('services.tiktok.client_id'),
                    'client_secret' => config('services.tiktok.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'data' => $body
            ];
            
        } catch (\Exception $e) {
            Log::error('Error refreshing token: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si el token es válido
     */
    public function verifyToken()
    {
        try {
            $response = $this->client->post('oauth/token/', [
                'form_params' => [
                    'client_key' => config('services.tiktok.client_id'),
                    'client_secret' => config('services.tiktok.client_secret'),
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            
            return [
                'success' => $response->getStatusCode() === 200,
                'data' => $body
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}