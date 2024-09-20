<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Code Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        p {
            font-size: 20px;
            color: #424530;
            line-height: 30px
        }
        .container {
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #ffefcd;
            text-align: center;
        }

        .logo {
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 20px;
        }

        .code {
            font-size: 24px;
            font-weight: bold;
            display: inline-block;
            letter-spacing: 3px;
            background: #E09132;
            padding: 8px;
            border-radius: 8px;
        }
        span {
            color: crimson;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="{{$message->embed(public_path('imgs/logo.png'))}}" alt="App Logo" width="100">
        </div>
        <p class="message">Your OTP Code Is:</p>
       <p class="code">{{$code}}</p>
        <p>Your OTP Code Will Expire In <span>15 Minutes</span>. Please Use It To Complete Your Password Reset Process.</p>
    </div>
</body>

</html>