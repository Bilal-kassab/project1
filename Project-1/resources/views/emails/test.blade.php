<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            background-color: #f1f1f1;
            padding: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Verify Your Email</h2>
        </div>
        <p>Dear {{$user->name}}</p>
        <p>Thank you for registering on our application. We hope you have a great experience! </p>
        <div class="code">
            {{ $code->code }}
        </div>
        <p>Greetings,<br>Support team</p>
        <div class="footer">
            &copy; {{ date('Y') }} جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
