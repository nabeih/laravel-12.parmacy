@component('mail::message')
# Welcome to {{ $appName }}!

Hi {{ $name }},

Thanks for creating an account with **{{ $appName }}**. Please use the verification code below to confirm your email address.

@component('mail::panel')
<div style="text-align:center; font-size:34px; font-weight:bold; letter-spacing:10px;">
{{ $otp }}
</div>
@endcomponent

This code will expire in **{{ $expiresInMinutes }} minutes**. If you didn't request this code, you can safely ignore this email.

Thanks,<br>
{{ $appName }}
@endcomponent
