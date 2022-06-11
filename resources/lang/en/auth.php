<?php

return [
    "2fa" => [
        "already_enabled" => "2FA is already enabled to your account.",
        "create_secret_failed" => "Failed to create a 2FA secret key, try again..",
        "disabled" => "2FA is disabled for your account, please enabled it to login.",
        "enable_failed" => "Failed to enable 2FA, try again.",
        "invalid_otp" => "The 2FA OTP provided is invalid or expired, try again.",
        "qrcode_failed" => "Failed to create 2FA QRCode, try again."
    ],
    "failed" => "These credentials do not match our records.",
    "forbidden" => "You don't have access to this resource",
    "notifications" => [
        "changes" => [
            "message" => "We have noticed some changes on your account from IP :ipAddress .",
            "no_further_action" => "If you have made these changes, no further action is required. However, if you believe these changes to be suspicious please immediately contact support at support@betkudos.io",
            "subject" => "Recently changes in your account"
        ]
    ],
    "throttle" => "Too many login attempts. Please try again in :seconds seconds."
];
