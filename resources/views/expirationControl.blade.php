@component('mail::message')
Estimado/a {{$name}},

Recibe este correo desde el sistema de control de identificación de {{ config('app.name') }}.

@if($type==1)
Su clave, contraclave y pin de seguridad para {{$location}} han excedido el tiempo máximo de vida de {{$days}} dias.

Rogamos proceda a realizar su cambio desde la aplicación web o bien poniéndose en contacto con su centro de control.

@component('mail::button', ['url' => $link])
Acceso web
@endcomponent

Si no procede al cambio de contraseña en el tiempo indicado, quedará anulada y no podrá volver a identificarse telefónicamente.
@elseif($type==2)
Ha unos días recibió el primer recordatorio conforme su clave, contraclave y pin de seguridad para {{$location}} habian excedido el tiempo máximo de vida de {{$days}} dias.

Recordarle que debe proceder a su cambio desde la aplicacion web o telefónicamente.

@component('mail::button', ['url' => $link])
Acceso web
@endcomponent

Si no procede al cambio de contraseña en el tiempo indicado, quedará anulada y no podrá volver a identificarse telefónicamente.
@elseif($type==3)
Habíamos depositado nuestra confianza en usted, pero nos ha defraudado.

Informarle que tras no proceder al cambio de sus claves de seguridad hemos procedido a su destrucción total. 

No nos llame, no podrá identificarse, no le haremos caso.

Habiamos confiado en usted, y nos ha fallado.
@endif

Gracias,

{{ config('app.name') }}
@endcomponent