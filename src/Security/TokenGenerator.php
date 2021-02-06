<?php


namespace App\Security;


class TokenGenerator
{
    private const ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    public function getRandomSecureToken(int $lenght=30) : string {
        $token = '';
        $maxNum = strlen(self::ALPHABET);
        for ($i=0;$i<$lenght;$i++) {
            $token .= self::ALPHABET[random_int(0,$maxNum-1)];
        }
        return $token;
    }
}