<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified Successfully</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            text-align: center;
        }

        .logo {
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 20px;
        }
        p {
            font-size: 20px;
            color: #424530;
            line-height: 30px
        }
        .success {
            font-size: 20px;
            font-weight: bold;
            color: #00cc66;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="{{$message->embed(public_path('imgs/logo.png'))}}" alt="App Logo" width="100">
        </div>
        <p class="message">Your Email Has Been Verified Successfully !</p>
        <p class="success">Congratulations !</p>
        <p>Enjoy, Hope You Have A Good Experience</p>
    </div>
</body>

</html>