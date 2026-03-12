<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - Plim Telecom</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            line-height: 1.6;
            color: #2c3e50;
            padding: 20px 0;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            border: 1px solid #e3e8f0;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            text-align: center;
            padding: 50px 30px;
            color: white;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><path d="M0,10 Q25,0 50,10 T100,10 L100,20 L0,20 Z" fill="rgba(255,255,255,0.1)"/></svg>') repeat-x;
            background-size: 100px 20px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .content {
            padding: 50px 40px;
            text-align: center;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        }

        .icon-container {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 20px;
            margin: 0 auto 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 42px;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            position: relative;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8, #3730a3);
            border-radius: 22px;
            z-index: -1;
            opacity: 0.7;
        }

        .content h2 {
            color: #1f2937;
            font-size: 28px;
            margin-bottom: 25px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .content p {
            color: #4b5563;
            font-size: 17px;
            margin-bottom: 35px;
            line-height: 1.7;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 18px 45px;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            margin: 25px 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(30, 60, 114, 0.3);
            border: 2px solid transparent;
            letter-spacing: 0.5px;
        }

        a.reset-button {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        a.reset-button:link,
        a.reset-button:visited,
        a.reset-button:active {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .reset-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.4);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            color: #ffffff !important;
            text-decoration: none !important;
        }

        a.reset-button:hover {
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .link-alternative {
            background: linear-gradient(145deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 25px;
            margin: 35px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        .link-alternative p {
            margin-bottom: 15px;
            font-size: 15px;
            color: #64748b;
            font-weight: 500;
        }

        .link-text {
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            font-size: 13px;
            color: #475569;
            word-break: break-all;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }

            .content {
                padding: 40px 25px;
            }

            .header {
                padding: 40px 25px;
            }

            .header h1 {
                font-size: 26px;
            }

            .icon-container {
                width: 80px;
                height: 80px;
                font-size: 36px;
            }

            .content h2 {
                font-size: 24px;
            }

            .reset-button {
                padding: 16px 35px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Recuperação de Senha</h1>
        </div>

        <!-- Content -->
        <div class="content">

            <h2>Olá, {{ $user ?? 'Usuário' }}!</h2>

            <p>
                Recebemos uma solicitação para redefinir a senha da sua conta.
                Se você fez esta solicitação, clique no botão abaixo para criar uma nova senha de forma segura.
            </p>

            <a href="{{ $resetUrl }}" class="reset-button">
                ✨ Redefinir Minha Senha
            </a>

            <div class="link-alternative">
                <p>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
                <div class="link-text">{{ $resetUrl }}</div>
            </div>

            <p style="margin-top: 35px; font-size: 15px; color: #6b7280; font-weight: 500;">
                Se você não solicitou a redefinição de senha, pode ignorar este email com segurança.
                Sua senha atual permanecerá inalterada.
            </p>

            <div style="background: linear-gradient(145deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 10px; padding: 18px; margin-top: 25px; color: #92400e; font-size: 14px; font-weight: 600; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
                <strong>IMPORTANTE:</strong> Este link expira em 60 minutos por motivos de segurança.
                Se precisar de um novo link, solicite uma nova redefinição de senha.
            </div>
        </div>
    </div>
</body>
</html>
