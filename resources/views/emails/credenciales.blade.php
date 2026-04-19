<!DOCTYPE html>
<html>
<head>
    <title>Credenciales de Acceso</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #0056b3; text-align: center;">Bienvenido al Sistema CONGOPE</h2>
        
        <p>Hola <strong>{{ $user->name }}</strong>,</p>
        
        <p>Se ha creado (o actualizado) tu cuenta en el sistema. A continuación, te proporcionamos tus credenciales de acceso temporales:</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Correo electrónico:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Contraseña:</strong> <span style="font-family: monospace; font-size: 16px;">{{ $password }}</span></p>
        </div>
        
        <p><em>Nota importante:</em> Por motivos de seguridad, el sistema te solicitará cambiar esta contraseña generada automáticamente en tu primer inicio de sesión.</p>
        
        <br>
        <p>Saludos cordiales,<br>El equipo de CONGOPE</p>
    </div>
</body>
</html>
