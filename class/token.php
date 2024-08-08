<?php
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Token
{
    private $secretKey;
    private $algorithm;

    public function __construct()
    {
        // Define your secret key and algorithm here
        $this->secretKey = 'zi8F9GMkZgaCfqpn2pFG'; // Change this to a secure key
        $this->algorithm = 'HS256'; // You can use other algorithms like HS384 or RS256
    }

    public function generateToken($payload)
    {
        $issuedAt = time();
        $expiration = $issuedAt + 3600; // Token valid for 1 hour

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expiration,
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    public function decodeToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public function validateToken($token)
    {
        try {
            JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
