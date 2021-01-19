![Keymanager](https://development.keymanager.tk/assets/img/logo_KeyManager.png)

**API KEYMANAGER**

Keymanager es una aplicación web para la gestión de códigos de seguridad para centrales receptoras de alarmas (https://development.keymanager.tk). 

En este proyecto estuve encargado del desarrollo de la **API REST con Laravel**.

La documentación está disponible en el siguiente enlace:
https://documenter.getpostman.com/view/10581396/TVsxCSKs


**TIPOS DE USUARIOS**


**Usuario de sistema:** es el operador de la central de alarmas. Puede realizar todo tipo de gestiones

**Usuario Cliente:** es el responsable de una empresa que contrata los servicios de la central de alarmas. Tiene permisos para ver información sobre sus localizaciones, empleados y sus propios datos como cliente. Por seguridad, no puede ver passwords de sus empleados ni tramitar bajas, pero si puede solicitar bajas que luego hará efectivas un usuario se sistema.

**Usuario empleado:** Puede ver información básica del cliente y la localización a la que pertenece y su gestión está limitada a algunos de sus datos como empleado.

**MODELOS**

**User:** su tabla users guarda la información personal de los usuarios, además de datos como su password de acceso a la aplicación, el tipo de usuario, si se ha solicitado su baja, si está de baja o si se ha bloqueado su cuenta.
Además su tabla está vinculada con otras que guardan información sobre su historial de accesos, reseteo de password y con qué clientes y empleados está relacionado.

**Customer:** su tabla guarda información básica del cliente. Está relacionada con la tabla pivot customer_user y con locations.

**Location:** guarda la información sobre las localizaciones de los clientes y está relacionada con la tabla employees.

**Employee:** es un usuario al que se le ha asignado una localización. Un usuario puede ser asignado a más de una localización, pero en cada caso se creará un empleado diferente, con diferentes códigos de seguridad. Está relacionado con modelos que guardan un historial de sus códigos de seguridad, historial de emails, expiración de sus códigos, su cargo y el usuario al que corresponde. 

**CIFRADO**

En el proceso de autorización usamos JSON Web Token. Se recibe desde front en el header, siempre se comprueba su validez y en casi todos los endpoints se extrae del token el tipo de usuario para verificar permisos. 

Los códigos de los empleados son cifrados y descifrados con un método que agrega un código secreto de la API para mayor seguridad. 

**RESPUESTAS**

Los endpoints responden con el siguiente formato:

**"status"**: true | false

**"message"**: "loginUserOk", | "loginUserError",

**"response"**: "error:" | "success" | un array o un objeto en caso de respuestas más complejas.

