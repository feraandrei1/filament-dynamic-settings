<?php

namespace Feraandrei1\FilamentDynamicSettings\Enums;

enum SettingName: string
{
    // Home Page settings
    case STATUS = 'status';
    case COMPANY_NAME = 'company_name';
    case COMPANY_ADDRESS = 'company_address';
    case DESCRIPTION = 'description';

    case INSTAGRAM_LINK = 'instagram_link';
    case FACEBOOK_LINK = 'facebook_link';
    case TIKTOK_LINK = 'tiktok_link';

    case EMAIL = 'email';
    case PHONE_NUMBER = 'phone_number';

    // General settings
    case LOGO = 'logo';
    case FAVICON = 'favicon';

    public function group(): SettingGroup
    {
        return match($this) {

            // Home Page
            self::STATUS,
            self::COMPANY_NAME,
            self::COMPANY_ADDRESS,
            self::DESCRIPTION,

            self::INSTAGRAM_LINK,
            self::FACEBOOK_LINK,
            self::TIKTOK_LINK,

            self::EMAIL,
            self::PHONE_NUMBER => SettingGroup::HOME_PAGE,

            // General settings
            self::LOGO,
            self::FAVICON => SettingGroup::GENERAL,
        };
    }
}
