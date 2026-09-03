<x-mail::message>
# Halo, {{ $contactMessage->name }}!

Menanggapi pesan Anda sebelumnya dengan subjek **"{{ $contactMessage->subject }}"**:

> {{ $contactMessage->message }}

---

Berikut adalah balasan dari tim kami:

<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin-bottom: 20px;">
{!! nl2br(e($replyMessage)) !!}
</div>

Terima kasih telah menghubungi kami!

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
