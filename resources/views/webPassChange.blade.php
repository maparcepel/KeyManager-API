@component('mail::message')
Estimado/a {{$name}},

Recibe este correo desde el sistema de control de identificación de {{ config('app.name') }}.

Ha sido dado de alta en la web <a href={{ config('app.url') }}>keymanager.tk</a> con los siguientes datos.

@component('mail::table')
| **Nombre**      | **Usuario**         | **Contraseña**  |
| --------------- |:----------------------:| ---------------:|
| {{$name}}       | {{$email}}             |{{$web_password}}|
@endcomponent

**Antes de 48 horas** debe ir al siguiente enlace y personalizar su contraseña:

@component('mail::button', ['url' => $link])
Cambiar contraseña
@endcomponent

No responda a este mensaje, es un envío automático.

Gracias,

{{ config('app.name') }}

@endcomponent

