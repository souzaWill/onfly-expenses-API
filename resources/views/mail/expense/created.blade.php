<x-mail::message>
# Olá {{ $userName}},

Uma nova despesa no valor de **R$ {{ $value }}** foi cadastrada para seu usuário!

<br>
Atenciosamente,  
{{ config('app.name') }}
</x-mail::message>
