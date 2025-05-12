<?php
/**
 * WhatsApp Cloud API Connector
 * Meta/Facebook WhatsApp Business API ilə qarşılıqlı əlaqə üçün sinif
 */
class WhatsAppCloudAPI {
    private $apiUrl = 'https://graph.facebook.com/v18.0/';
    private $apiToken;
    private $phoneNumberId;
    
    /**
     * Constructor
     */
    public function __construct() {
        // API məlumatlarını mühit dəyişənlərindən əldə et
        $this->apiToken = getenv('WHATSAPP_API_TOKEN');
        $this->phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID');
        
        // API məlumatlarının var olduğunu yoxla
        if (empty($this->apiToken) || empty($this->phoneNumberId)) {
            error_log("WhatsApp API məlumatları: API açarı və ya telefon nömrəsi ID-si mövcud deyil");
        }
    }
    
    /**
     * Telefon nömrəsini düzgün formata çevir
     * @param string $phone Telefon nömrəsi
     * @return string Formatlanmış telefon nömrəsi
     */
    public function formatPhoneNumber($phone) {
        // Bütün rəqəm olmayan simvolları təmizlə
        $phone = preg_replace('/\D/', '', $phone);
        
        // Azərbaycan nömrəsini formatlama
        if (!preg_match('/^994/', $phone)) {
            $phone = "994" . ltrim($phone, '0');
        }
        
        return $phone;
    }
    
    /**
     * WhatsApp ilə text mesajı göndər
     * @param string $phone Telefon nömrəsi
     * @param string $message Göndəriləcək mesaj
     * @return array Nəticə
     */
    public function sendTextMessage($phone, $message) {
        // Telefon nömrəsini formatla
        $phone = $this->formatPhoneNumber($phone);
        
        // API URL
        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        
        // Mesaj məlumatları
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message
            ]
        ];
        
        // API sorğusunu göndər
        return $this->makeRequest($url, $data);
    }
    
    /**
     * WhatsApp ilə şəkil mesajı göndər
     * @param string $phone Telefon nömrəsi
     * @param string $imageUrl Şəkil URL-i
     * @param string $caption Şəkil üçün başlıq (istəyə bağlı)
     * @return array Nəticə
     */
    public function sendImageMessage($phone, $imageUrl, $caption = '') {
        // Telefon nömrəsini formatla
        $phone = $this->formatPhoneNumber($phone);
        
        // API URL
        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        
        // Mesaj məlumatları
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl,
                'caption' => $caption
            ]
        ];
        
        // API sorğusunu göndər
        return $this->makeRequest($url, $data);
    }
    
    /**
     * WhatsApp ilə şablon mesajı göndər
     * @param string $phone Telefon nömrəsi
     * @param string $templateName Şablon adı
     * @param array $params Şablon parametrləri
     * @param string $language Dil kodu (default: tr_TR)
     * @return array Nəticə
     */
    public function sendTemplateMessage($phone, $templateName, $params = [], $language = 'tr_TR') {
        // Telefon nömrəsini formatla
        $phone = $this->formatPhoneNumber($phone);
        
        // API URL
        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        
        // Parametrləri hazırla
        $components = [];
        if (!empty($params)) {
            $parameters = [];
            foreach ($params as $param) {
                $parameters[] = [
                    'type' => 'text',
                    'text' => $param
                ];
            }
            
            $components[] = [
                'type' => 'body',
                'parameters' => $parameters
            ];
        }
        
        // Mesaj məlumatları
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ]
            ]
        ];
        
        if (!empty($components)) {
            $data['template']['components'] = $components;
        }
        
        // API sorğusunu göndər
        return $this->makeRequest($url, $data);
    }
    
    /**
     * HTTP sorğusu göndər
     * @param string $url API endpoint URL
     * @param array $data Göndəriləcək məlumatlar
     * @return array Nəticə
     */
    private function makeRequest($url, $data) {
        // API açarı yoxdursa, xəta qaytarın
        if (empty($this->apiToken)) {
            return [
                'success' => false,
                'error' => 'API açarı mövcud deyil'
            ];
        }
        
        // API sorğusunu hazırlama
        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Debug məqsədləri üçün məlumatları qeyd et
        error_log("WhatsApp API Request URL: " . $url);
        error_log("WhatsApp API Request Data: " . json_encode($data));
        
        // Sorğunu göndər
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Debug məqsədləri üçün cavabı qeyd et
        error_log("WhatsApp API Response (" . $httpCode . "): " . $response);
        
        // Xəta baş verdiyi təqdirdə
        if ($error) {
            error_log("WhatsApp API Error: " . $error);
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode
            ];
        }
        
        // Uğurlu olub-olmadığını yoxla
        $responseData = json_decode($response, true);
        $success = ($httpCode >= 200 && $httpCode < 300) && isset($responseData['messages']) && !empty($responseData['messages']);
        
        return [
            'success' => $success,
            'data' => $responseData,
            'http_code' => $httpCode
        ];
    }
    
    /**
     * API məlumatlarını yoxla
     * @return bool Məlumatların olub-olmadığı 
     */
    public function hasValidCredentials() {
        return !empty($this->apiToken) && !empty($this->phoneNumberId);
    }
    
    /**
     * Test API mesajı göndər
     * @param string $phone Telefon nömrəsi
     * @return array Nəticə
     */
    public function sendTestMessage($phone) {
        return $this->sendTextMessage(
            $phone, 
            "Bu test mesajıdır. WhatsApp Cloud API vasitəsilə göndərilmişdir."
        );
    }
}
?>