
<html>
    <head>
        <title>God Admin Registration</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form method="post" action = "{!! url('/doRegister') !!}">
            <input type="hidden" name="_token" value="{!! csrf_token() !!}">
            First Name: <input type="text" name="firstName">
            Last Name: <input type="text" name="lastName">
            Email: <input type="text" name="email">
            Password: <input type="text" name="password">
            <input type="submit" value="Register">
        </form>
        
    </body>
</html>
