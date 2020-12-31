@component('mail::message')
Estimado/a {{$name}},

Recibe este correo desde el sistema de control de identificación de {{ config('app.name') }}.

@if($type==1 || $type==3)
Su contraseña de acceso web ha excedido el tiempo máximo de vida de {{$days}} dias.

Habiamos confiado en usted, y nos ha fallado.

Informarle que tras no proceder al cambio de su contraseña web hemos procedido a su destrucción total. 

@elseif($type==2)
Quedan {{$days}} dias para que su contraseña de acceso web quede fuera de servicio.

Recordarle que debe proceder a su cambio desde la aplicacion web:

@component('mail::button', ['url' => '{{$link}}'])
Cambiar contraseña
@endcomponent

Si no procede al cambio de contraseña en el tiempo indicado, quedará anulada y no podrá acceder a la platadorma web.
@endif

Gracias,

{{ config('app.name') }}
@endcomponent