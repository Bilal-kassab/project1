<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            direction: ltr;
            text-align: left;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-top: 4px solid #007bff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h3 {
            font-size: 24px;
            color: #023d7b;
            margin: 0;
            font-weight: bold;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin: 10px 0;
        }
        .content .code {
            font-size: 28px;
            font-weight: bold;
            background-color: #f1f1f1;
            color: #023d7b;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 2px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3 >Welcome to Unigo</h3>
        </div>
        <div class="content">
            <p style="color: black">Dear {{$user->name}},</p>
            <p>Thank you for registering on our application. We hope you have a great experience!</p>
            <div class="code">
                {{ $code->code }}
            </div>
            <p style="color: black">Greetings<br>Support team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} All rights reserved.
        </div>
    </div>
</body>
</html>
