# Propuesta: recepción de pacientes nuevos o sin cita

## Situación actual

Actualmente, el sistema trabaja principalmente con pacientes que tienen una cuenta de usuario y una cita. El registro público permite que el paciente cree su cuenta, y administración puede crear cuentas de pacientes desde la gestión de usuarios. La cita presencial también se agenda para un paciente que ya existe en el sistema.

Por eso, si una persona llega al centro sin haberse registrado en la página y sin una cita, el flujo no está cubierto de forma completa. Administración tendría que crear primero su cuenta y ficha, y luego registrar una cita para que enfermería pueda encontrarlo en la bandeja diaria. Enfermería no tiene una opción para crear pacientes ni iniciar atenciones sin cita.

## Recomendación

Implementar un flujo de **recepción presencial**, separado del registro de cuentas del portal:

1. El paciente llega al centro y el personal de recepción busca su ficha por documento de identidad u otro dato autorizado.
2. Si ya existe, se selecciona su ficha y se evita crear otra.
3. Si no existe, recepción registra los datos mínimos aprobados por el centro y crea su ficha clínica.
4. Recepción crea una atención presencial sin cita y selecciona al médico o servicio responsable según la organización del centro.
5. Enfermería ve la atención en una cola de pacientes del día, confirma la identidad y registra el motivo referido y las mediciones que tomó.
6. El médico asignado consulta los datos de enfermería, registra la consulta y completa la atención.
7. La atención queda cerrada o marcada como no completada según el resultado del flujo definido por el centro.

La ficha clínica debe poder existir aunque la persona no tenga correo electrónico ni use el portal. Si más adelante quiere usar las funciones en línea, se le puede habilitar una cuenta vinculada a su ficha. No se recomienda inventar correos ni entregar contraseñas compartidas o temporales de manera informal.

## Roles y responsabilidades sugeridos

### Recepción o administración

- Buscar pacientes existentes y confirmar coincidencias antes de crear una ficha nueva.
- Crear fichas de pacientes presenciales con los datos mínimos definidos por el centro.
- Iniciar una atención sin cita y registrar la información administrativa necesaria.
- Asignar la atención al servicio o profesional según las reglas de operación aprobadas.
- No consultar diagnósticos ni notas clínicas salvo que el centro autorice expresamente esos datos para su función.

### Enfermería

- Consultar las atenciones activas que le correspondan según el alcance acordado.
- Ver los datos mínimos para identificar al paciente.
- Registrar motivo referido, signos vitales y observaciones de recepción.
- No modificar datos demográficos ni introducir diagnósticos o tratamientos médicos.

### Médico

- Consultar las atenciones que tenga asignadas.
- Revisar la información inicial y registrar por separado su consulta médica.
- Cerrar la atención según el flujo que apruebe el centro.

## Recomendación de diseño de datos

En el estado actual, la ficha de paciente (`PatientProfile`) depende de una cuenta de usuario (`User`), y la recepción y la consulta médica dependen de una cita (`Appointment`). Esto acopla la identidad clínica al inicio de sesión y hace difícil atender correctamente a alguien sin cuenta o sin cita.

Como solución sostenible, conviene separar tres conceptos:

1. **Ficha de paciente:** identidad y datos demográficos. Puede existir sin credenciales del portal.
2. **Cuenta de acceso:** credenciales para ingresar al portal, opcionalmente vinculadas a una ficha de paciente.
3. **Atención:** episodio de atención presencial o programada. Puede tener una cita asociada o ser una atención sin cita.

La recepción de enfermería y la consulta médica deberían asociarse a la atención. Una atención programada puede referenciar una cita; una atención sin cita no debe necesitar crear una cita ficticia para poder registrar datos clínicos.

Este cambio afecta relaciones, permisos, búsquedas y claves foráneas existentes. Antes de migrar los datos actuales se debe preparar una transición que mantenga cada cuenta de paciente ligada a su ficha y preserve las citas y los registros clínicos existentes.

## Alcance mínimo recomendado para una primera entrega

- Añadir una acción **“Registrar paciente presencial”** al panel de recepción/administración.
- Permitir buscar antes de crear, usando el documento como dato de coincidencia cuando esté disponible.
- No asumir que el documento siempre existe: el centro debe definir una alternativa para personas sin documento o sin datos verificables.
- Crear una ficha clínica sin exigir correo ni contraseña.
- Permitir iniciar una atención sin cita y asociarla con el paciente y el profesional/servicio responsable.
- Mostrar la atención en la cola de enfermería del día.
- Permitir al médico consultar el registro de enfermería y añadir la consulta médica a esa misma atención.
- Registrar quién creó la atención, quién hizo la recepción, quién la atendió y cuándo ocurrieron esas acciones.
- Mantener los accesos protegidos por rol y evitar mostrar detalles clínicos al personal administrativo que no los necesite.
- Prevenir atenciones duplicadas accidentales, sin bloquear que un mismo paciente tenga varias atenciones legítimas.

## Criterios de aceptación propuestos

- Recepción puede encontrar una ficha existente sin crear duplicados.
- Si el paciente no existe, una persona autorizada puede crear una ficha sin crear obligatoriamente una cuenta de portal.
- La atención presencial puede iniciarse aunque no exista una cita.
- Enfermería solo ve los datos identificativos y clínicos mínimos necesarios para realizar la recepción.
- La atención se vincula a un profesional o servicio responsable antes de habilitar la consulta médica.
- El médico solo accede a las atenciones autorizadas para su trabajo asistencial.
- El sistema conserva autoría y fechas de creación, recepción y consulta.
- Los datos de enfermería, diagnósticos y tratamientos médicos permanecen separados por rol y responsabilidad.
- Las correcciones clínicas mantienen trazabilidad y no eliminan silenciosamente el dato original.
- La interfaz muestra con claridad si la atención fue programada o presencial sin cita.

## Decisiones que debe aprobar el centro

1. ¿Qué cargo realiza el registro inicial: recepción, administración u otro personal autorizado?
2. ¿Qué datos identificativos son obligatorios y cómo se procede cuando la persona no tiene documento?
3. ¿Cómo se comprueba que dos registros corresponden a la misma persona y quién puede resolver una posible duplicidad?
4. ¿Quién asigna médico o servicio a una atención sin cita y qué ocurre cuando todavía no hay asignación?
5. ¿Qué estados necesita el flujo: recibido, en enfermería, pendiente de médico, en consulta, atendido, derivado o no completado?
6. ¿En qué situaciones puede enfermería iniciar la atención antes de que exista asignación médica?
7. ¿Qué datos puede consultar recepción y cuáles quedan reservados para enfermería y el médico?
8. ¿Cómo se documentan las atenciones urgentes? El sistema no debe sustituir el protocolo clínico ni retrasar la atención de emergencia.
9. ¿Cuánto tiempo se conservan los datos, quién puede corregirlos y qué auditoría exige la institución?
10. ¿Cómo se habilitará más adelante una cuenta de portal sin crear una segunda ficha de paciente?

## Alternativas y trade-offs

### A. Atajo: crear cuenta y cita para cada paciente presencial

Es el cambio inicial más pequeño y aprovecha el flujo actual. Sin embargo, obliga a inventar o pedir correo, gestionar credenciales innecesarias, crear citas artificiales y mezclar el agendamiento con la recepción. No lo recomiendo como diseño definitivo.

### B. Ficha de paciente independiente de la cuenta y atención independiente de la cita

Es la opción recomendada a mediano plazo. Representa correctamente a pacientes presenciales, protege la separación entre identidad, acceso al portal y atención, y permite historial tanto de citas como de atenciones sin cita. Requiere una migración más amplia y pruebas cuidadosas para preservar los datos existentes.

### C. Identificador temporal para pacientes no identificados

Puede ser necesario si el protocolo institucional permite iniciar una atención antes de completar la identificación. Debe diseñarse con reglas expresas de seguridad, conciliación posterior y prevención de duplicados. No debe implementarse como ficha anónima permanente sin aprobación del centro.

## Plan de implementación sugerido

1. Validar con el centro los roles, datos mínimos, estados y reglas de asignación.
2. Diseñar la migración de `PatientProfile` para que no dependa de credenciales de acceso, conservando las cuentas actuales.
3. Introducir el concepto de atención y vincularlo opcionalmente a una cita.
4. Implementar búsqueda/alta presencial y protección contra duplicados.
5. Adaptar la cola de enfermería para mostrar citas del día y atenciones sin cita.
6. Adaptar el historial clínico y la consulta médica para trabajar sobre una atención.
7. Añadir pruebas de permisos por rol, registros duplicados, accesos cruzados y migración de los datos existentes.
8. Probar el comportamiento en la base de datos que se decida para producción antes de desplegar.

## Conclusión

La recomendación es permitir que **recepción o administración cree una ficha clínica sin crear automáticamente una cuenta de usuario**, y que pueda iniciar una **atención presencial sin cita**. Enfermería registra los datos iniciales y el médico responsable documenta la consulta. Para hacerlo bien, el proyecto debe separar progresivamente la ficha clínica, la cuenta de acceso y la cita/atención; no se debería simular el proceso creando cuentas o citas ficticias.

La selección exacta de los roles, datos mínimos, asignación y estados depende de las políticas del Centro de Salud San Juan y debe aprobarse antes de convertir esta propuesta en una historia de usuario e implementarla.
