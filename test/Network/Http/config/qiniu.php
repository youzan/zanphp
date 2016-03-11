<?php
/**
 * Created by PhpStorm.
 * User: heize
 * Date: 16/3/9
 * Time: 下午4:42
 */
return [
    'access_key' => 'rUB366LVcrOqc3fwm0Bf8mPHDQFNRMMAOezxeV3g',
    'secret_key' => 'gI8PL_oEBsBm0zjk9k2DyjCxO3b2pFHrxjR9-F9c',
    'url' => [
        'kdt-cloud' => 'http://dl.koudaitong.com/'
    ],
    'bucket' => [
        'kdt_img' => [
            'name' => 'kdt_img',
            'domain' => 'dn-kdt-img.qbox.me',
            'public' => TRUE,
            'save_key' => 'upload_files/$(year)/$(mon)/$(day)/$(etag)$(ext)',
            'fop' => [
                'thumbnails' => '!100x100.jpg',
                '100x100' => '?imageView2/2/w/100/h/100',
                '8k' => '!8k.mp3'
            ],
            'expires' => 60 * 60
        ],
        'kdt-private' => [
            'name' => 'kdt-private',
            'domain' => 'dn-kdt-private.qbox.me',
            'public' => FALSE,
            'save_key' => '$(bucket)-$(year)-$(mon)-$(day)-$(etag)$(ext)',
            'fop' => [
                'thumbnails' => '!100x100.jpg',
                '100x100' => '?imageView2/2/w/100/h/100',
                '8k' => '!8k.mp3'
            ],
            'expires' => 10 * 60
        ],
        'kdt-app-private' => [
            'name' => 'kdt-app-private',
            'domain' => 'dn-kdt-app-private.qbox.me',
            'public' => FALSE,
            'save_key' => '$(bucket)-$(year)-$(mon)-$(day)-$(etag)$(ext)',
            'fop' => [
                'thumbnails' => '!100x100.jpg',
                '100x100' => '?imageView2/2/w/100/h/100',
                '8k' => '!8k.mp3'
            ],
            'expires' => 10 * 60
        ],
    ],
    'callback_url' => 'http://koudaitong.com/v2/common/qiniu/upload.json',
    'no_pic_url' => 'https://img.yzcdn.cn/upload_files/no_pic.png'
];