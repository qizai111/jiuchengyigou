<?php
namespace app\api\controller\common;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

// Download：https://github.com/aliyun/openapi-sdk-php
// Usage：https://github.com/aliyun/openapi-sdk-php/blob/master/README.md

class Information
{
    private static function config(){
        require "../vendor/autoload.php";
        AlibabaCloud::accessKeyClient(config("alibaba.access.id"), config("alibaba.access.secret"))
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

    }
    public static function infor($code, $phone)
    {
        self::config();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => "$phone",
                        'SignName' => "酒城易购",
                        'TemplateCode' => "SMS_187241393",
                        'TemplateParam' => "{\"code\":\"$code\"}",
                    ],
                ])
                ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            return $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            return $e->getErrorMessage() . PHP_EOL;

        }
    }
    
}

