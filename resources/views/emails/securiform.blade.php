<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $asunto }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color: #4338ca; border-radius: 12px 12px 0 0; padding: 24px 32px; text-align: center;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">
                                SecuriForm
                            </h1>
                            <p style="margin: 4px 0 0; font-size: 12px; color: rgba(255,255,255,0.7);">
                                Gestion de Seguridad de la Informacion
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background-color: #ffffff; padding: 32px;">
                            @if ($saludo)
                                <p style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: #111827;">
                                    {{ $saludo }}
                                </p>
                            @endif

                            <div style="font-size: 14px; line-height: 1.7; color: #374151;">
                                {!! $cuerpo !!}
                            </div>

                            @if ($actionUrl && $actionLabel)
                                <div style="text-align: center; margin: 28px 0 8px;">
                                    <a href="{{ $actionUrl }}"
                                       style="display: inline-block; background-color: #4338ca; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 28px; border-radius: 8px;">
                                        {{ $actionLabel }}
                                    </a>
                                </div>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 12px 12px; padding: 20px 32px; text-align: center;">
                            @if ($piePagina)
                                <p style="margin: 0 0 8px; font-size: 12px; color: #6b7280;">
                                    {{ $piePagina }}
                                </p>
                            @endif
                            <p style="margin: 0; font-size: 11px; color: #9ca3af;">
                                Este es un mensaje automatico de SecuriForm. No responda a este correo.
                            </p>
                            <p style="margin: 4px 0 0; font-size: 11px; color: #9ca3af;">
                                &copy; {{ date('Y') }} SecuriForm &mdash; Estudio Palacios Abogados S.A.C.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
