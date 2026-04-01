<div style="border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,.1);">

    {{-- Toolbar --}}
    <div style="background: #1f2937; padding: 12px 20px; display: flex; align-items: center; gap: 8px;">
        <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
        <span style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
        <span style="width: 12px; height: 12px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
        <span style="margin-left: 12px; font-size: 12px; color: #9ca3af;">Vista previa del email</span>
    </div>

    {{-- Email header --}}
    <div style="background: #f9fafb; padding: 20px 24px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #4338ca, #6366f1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="color: #fff; font-weight: 700; font-size: 14px;">SF</span>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 600; color: #111827;">SecuriForm</div>
                <div style="font-size: 12px; color: #6b7280;">noreply@securiform.local</div>
            </div>
        </div>

        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
            <tr>
                <td style="padding: 4px 0; color: #9ca3af; width: 60px; vertical-align: top;">Para:</td>
                <td style="padding: 4px 0; color: #374151;">{{ $para }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: #9ca3af; vertical-align: top;">Asunto:</td>
                <td style="padding: 4px 0; color: #111827; font-weight: 600;">{{ $asunto }}</td>
            </tr>
        </table>
    </div>

    {{-- Email body --}}
    <div style="background: #f3f4f6; padding: 0; max-height: 460px; overflow-y: auto;">
        {!! $htmlPreview !!}
    </div>

</div>
