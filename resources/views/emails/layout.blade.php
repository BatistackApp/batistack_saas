<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Styles de base pour compatibilité email */
        body { margin: 0; padding: 0; font-family: 'Noto Sans', Arial, sans-serif; background-color: #ffffff; color: #333333; line-height: 1.5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 1px solid #eeeeee; padding-bottom: 20px; margin-bottom: 30px; }
        .logo-container { display: flex; align-items: center; }
        .title { color: #002157; font-size: 22px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; }
        .subtitle { color: #666666; font-size: 14px; margin-bottom: 30px; }
        .content { font-size: 16px; margin-bottom: 40px; }
        .button-container { background-color: #f6f6f6; border-radius: 8px; padding: 20px; display: flex; align-items: center; margin-bottom: 30px; }
        .button-icon { background-color: #002157; color: white; padding: 10px; border-radius: 6px; margin-right: 15px; }
        .footer-banner { background-color: #002157; color: #ffffff; padding: 25px; font-size: 12px; margin-top: 40px; }
        .footer-text { margin-bottom: 15px; line-height: 1.4; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { text-align: left; border-bottom: 2px solid #002157; padding: 10px; color: #002157; }
        .table td { padding: 10px; border-bottom: 1px solid #eeeeee; }
        a { color: #0056b3; text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <!-- Header Institutionnel -->
    <div class="header">
        <table width="100%">
            <tr>
                <td>
                    <img src="https://upload.wikimedia.org/wikipedia/fr/thumb/c/c4/Logo_France_Travail_2024.svg/2560px-Logo_France_Travail_2024.svg.png" alt="Batistack Logo" height="50">
                </td>
                <td align="right">
                    <div style="width: 20px; height: 20px; background-color: #e1000f; border-radius: 50%;"></div>
                </td>
            </tr>
        </table>
    </div>

    @yield('content')

    <!-- Footer Institutionnel -->
    <div class="footer-banner">
        <div class="footer-text">
            Cet e-mail vous est envoyé automatiquement, merci de ne pas utiliser la fonction "répondre à l'expéditeur".
        </div>
        <div class="footer-text">
            Vous disposez d'un droit d'accès et de rectification aux informations qui vous concernent auprès de Batistack conformément à la loi du 6 janvier 1978, modifiée, relative à l'informatique, aux fichiers et aux libertés.
        </div>
    </div>
</div>
</body>
</html>
