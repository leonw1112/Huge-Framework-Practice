<?php

/**
 * Class CaptchaModel
 *
 * This model class handles all the captcha stuff.
 * Currently uses Google reCAPTCHA v2.
 */
class CaptchaModel
{
    /**
     * Verifies the Google reCAPTCHA v2 response.
     *
     * @param string $recaptchaResponse The 'g-recaptcha-response' from the form POST
     * @return bool true if valid, false otherwise
     */
    public static function verifyRecaptcha($recaptchaResponse)
    {
        // If reCAPTCHA is not configured (default test keys), skip validation
        $secretKey = Config::get('RECAPTCHA_SECRET_KEY');
        if (empty($secretKey) || $secretKey === '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe') {
            return true;
        }

        // If no response was submitted, fail immediately
        if (empty($recaptchaResponse)) {
            return false;
        }

        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $postData = http_build_query([
            'secret'   => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        // Use cURL if available, otherwise fall back to file_get_contents
        if (function_exists('curl_init')) {
            $ch = curl_init($verifyUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $postData,
                    'timeout' => 10,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($verifyUrl, false, $context);
        }

        if (empty($response)) {
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] === true;
    }
}
