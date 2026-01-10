<?php

namespace App\Services;

use Exception;

class AgoraService
{
    private string $appId;
    private string $appCertificate;

    public function __construct()
    {
        $this->appId = config('services.agora.app_id');
        $this->appCertificate = config('services.agora.app_certificate');
    }

    /**
     * Generate RTC token for voice/video calls
     */
    public function generateRtcToken(
        string $channelName,
        int $uid = 0,
        string $role = 'publisher',
        int $expirationTimeInSeconds = 3600
    ): array {
        if (empty($this->appId) || empty($this->appCertificate)) {
            throw new Exception('Agora credentials not configured');
        }

        // Token expiration timestamp
        $privilegeExpireTime = time() + $expirationTimeInSeconds;

        // Generate token using Agora algorithm
        $token = $this->buildToken(
            $this->appId,
            $this->appCertificate,
            $channelName,
            $uid,
            $role,
            $privilegeExpireTime
        );

        return [
            'token' => $token,
            'app_id' => $this->appId,
            'channel_name' => $channelName,
            'uid' => $uid,
            'expire_time' => $privilegeExpireTime,
        ];
    }

    /**
     * Generate RTM token for messaging
     */
    public function generateRtmToken(
        string $userId,
        int $expirationTimeInSeconds = 3600
    ): array {
        if (empty($this->appId) || empty($this->appCertificate)) {
            throw new Exception('Agora credentials not configured');
        }

        $privilegeExpireTime = time() + $expirationTimeInSeconds;

        $token = $this->buildRtmToken(
            $this->appId,
            $this->appCertificate,
            $userId,
            $privilegeExpireTime
        );

        return [
            'token' => $token,
            'app_id' => $this->appId,
            'user_id' => $userId,
            'expire_time' => $privilegeExpireTime,
        ];
    }

    /**
     * Build RTC token
     *
     * Note: This is a simplified implementation.
     * For production, use the official Agora SDK:
     * composer require agora/rtc-token-builder
     */
    private function buildToken(
        string $appId,
        string $appCertificate,
        string $channelName,
        int $uid,
        string $role,
        int $privilegeExpireTime
    ): string {
        // TODO: Replace with official Agora RTC Token Builder
        // This is a placeholder implementation

        // For now, return a mock token for development
        if (app()->environment('local', 'development')) {
            return base64_encode(json_encode([
                'app_id' => $appId,
                'channel' => $channelName,
                'uid' => $uid,
                'role' => $role,
                'expire' => $privilegeExpireTime,
                'signature' => hash_hmac('sha256', $channelName . $uid, $appCertificate),
            ]));
        }

        // Production implementation using official SDK
        try {
            // Install: composer require agora/rtc-token-builder
            $roleValue = $role === 'publisher' ? 1 : 2;

            // Uncomment when official SDK is installed:
            // $tokenBuilder = new \Agora\RtcTokenBuilder\RtcTokenBuilder();
            // return $tokenBuilder->buildTokenWithUid(
            //     $appId,
            //     $appCertificate,
            //     $channelName,
            //     $uid,
            //     $roleValue,
            //     $privilegeExpireTime
            // );

            throw new Exception('Agora RTC Token Builder not installed. Run: composer require agora/rtc-token-builder');
        } catch (Exception $e) {
            throw new Exception('Failed to build RTC token: ' . $e->getMessage());
        }
    }

    /**
     * Build RTM token
     */
    private function buildRtmToken(
        string $appId,
        string $appCertificate,
        string $userId,
        int $privilegeExpireTime
    ): string {
        // For development environment
        if (app()->environment('local', 'development')) {
            return base64_encode(json_encode([
                'app_id' => $appId,
                'user_id' => $userId,
                'expire' => $privilegeExpireTime,
                'signature' => hash_hmac('sha256', $userId, $appCertificate),
            ]));
        }

        // Production implementation
        try {
            // Install: composer require agora/rtm-token-builder

            // Uncomment when official SDK is installed:
            // $tokenBuilder = new \Agora\RtmTokenBuilder\RtmTokenBuilder();
            // return $tokenBuilder->buildToken(
            //     $appId,
            //     $appCertificate,
            //     $userId,
            //     $privilegeExpireTime
            // );

            throw new Exception('Agora RTM Token Builder not installed. Run: composer require agora/rtm-token-builder');
        } catch (Exception $e) {
            throw new Exception('Failed to build RTM token: ' . $e->getMessage());
        }
    }

    /**
     * Validate channel name
     */
    public function validateChannelName(string $channelName): bool
    {
        // Channel name rules:
        // - Length: 1-64 characters
        // - Valid characters: a-z, A-Z, 0-9, !, #, $, %, &, (, ), +, -, :, ;, <, =, ., >, ?, @, [, ], ^, _, {, }, |, ~, ,
        return strlen($channelName) >= 1
            && strlen($channelName) <= 64
            && preg_match('/^[a-zA-Z0-9!#$%&()+\-:;<=>?@\[\]^_{|}~,.]*$/', $channelName);
    }

    /**
     * Generate unique channel name
     */
    public function generateChannelName(string $prefix = 'ch'): string
    {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(8));
    }
}
